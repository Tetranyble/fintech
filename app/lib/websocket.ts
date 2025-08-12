export interface WebSocketMessage {
  event: string
  data: any
  channel?: string
}

export interface PaymentStatusEvent {
  payment_id: string
  status: "pending" | "processing" | "completed" | "failed" | "refunded"
  amount: number
  timestamp: string
  customer_id: string
  transaction_id?: string
}

export interface BalanceUpdateEvent {
  balance: number
  previous_balance: number
  change: number
  timestamp: string
  customer_id: string
}

export class WebSocketClient {
  private ws: WebSocket | null = null
  private reconnectAttempts = 0
  private maxReconnectAttempts = 5
  private reconnectDelay = 1000
  private heartbeatInterval: NodeJS.Timeout | null = null
  private listeners: Map<string, Set<(data: any) => void>> = new Map()

  constructor(
    private token: string,
    private customerId: string,
  ) {}

  connect(): Promise<void> {
    return new Promise((resolve, reject) => {
      try {
        // Close existing connection if any
        this.disconnect()

        const reverbHost = process.env.NEXT_PUBLIC_REVERB_HOST
        const reverbPort = process.env.NEXT_PUBLIC_REVERB_PORT
        const reverbScheme = process.env.NEXT_PUBLIC_REVERB_SCHEME
        const reverbAppKey = process.env.NEXT_PUBLIC_REVERB_APP_KEY

        const wsScheme = reverbScheme === "https" ? "wss" : "ws"
        const wsUrl = `${wsScheme}://${reverbHost}:${reverbPort}/app/${reverbAppKey}?protocol=7&client=js&version=8.4.0-rc2&flash=false`

        this.ws = new WebSocket(wsUrl)

        this.ws.onopen = () => {
          console.log("WebSocket connected to Laravel Reverb")
          this.reconnectAttempts = 0
          this.startHeartbeat()

          this.authenticate()

          // Subscribe to customer-specific channels
          this.subscribe(`customer.${this.customerId}`)
          this.subscribe(`payments.${this.customerId}`)

          resolve()
        }

        this.ws.onmessage = (event) => {
          try {
            const message: WebSocketMessage = JSON.parse(event.data)
            this.handleMessage(message)
          } catch (error) {
            console.error("Failed to parse WebSocket message:", error)
          }
        }

        this.ws.onclose = (event) => {
          console.log("WebSocket disconnected:", event.code, event.reason)
          this.stopHeartbeat()

          if (!event.wasClean && this.reconnectAttempts < this.maxReconnectAttempts) {
            this.scheduleReconnect()
          }
        }

        this.ws.onerror = (error) => {
          console.error("WebSocket error:", error)
          reject(error)
        }
      } catch (error) {
        reject(error)
      }
    })
  }

  disconnect(): void {
    this.stopHeartbeat()
    if (this.ws) {
      this.ws.close(1000, "Client disconnect")
      this.ws = null
    }
  }

  private authenticate(): void {
    this.send("pusher:connection_established", {
      auth: {
        headers: {
          Authorization: `Bearer ${this.token}`,
        },
      },
    })
  }

  private scheduleReconnect(): void {
    this.reconnectAttempts++
    const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1)

    console.log(
      `Attempting to reconnect in ${delay}ms (attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts})`,
    )

    setTimeout(() => {
      this.connect().catch((error) => {
        console.error("Reconnection failed:", error)
      })
    }, delay)
  }

  private startHeartbeat(): void {
    this.heartbeatInterval = setInterval(() => {
      if (this.ws?.readyState === WebSocket.OPEN) {
        this.send("pusher:ping", {})
      }
    }, 30000) // Send ping every 30 seconds
  }

  private stopHeartbeat(): void {
    if (this.heartbeatInterval) {
      clearInterval(this.heartbeatInterval)
      this.heartbeatInterval = null
    }
  }

  private subscribe(channel: string): void {
    this.send("pusher:subscribe", {
      channel,
      auth: `Bearer ${this.token}`,
      channel_data: JSON.stringify({ user_id: this.customerId }),
    })
  }

  private send(event: string, data: any): void {
    if (this.ws?.readyState === WebSocket.OPEN) {
      this.ws.send(JSON.stringify({ event, data }))
    }
  }

  private handleMessage(message: WebSocketMessage): void {
    const { event, data } = message

    switch (event) {
      case "pusher:connection_established":
        console.log("Pusher connection established")
        break
      case "pusher:pong":
        // Heartbeat response
        break
      case "pusher_internal:subscription_succeeded":
        console.log("Subscribed to channel:", data?.channel)
        break
      case "payment.status.changed":
        this.emit("paymentStatusChanged", data as PaymentStatusEvent)
        break
      case "balance.updated":
        this.emit("balanceUpdated", data as BalanceUpdateEvent)
        break
      default:
        this.emit(event, data)
    }
  }

  on(event: string, callback: (data: any) => void): () => void {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, new Set())
    }

    this.listeners.get(event)!.add(callback)

    // Return unsubscribe function
    return () => {
      const eventListeners = this.listeners.get(event)
      if (eventListeners) {
        eventListeners.delete(callback)
        if (eventListeners.size === 0) {
          this.listeners.delete(event)
        }
      }
    }
  }

  private emit(event: string, data: any): void {
    const eventListeners = this.listeners.get(event)
    if (eventListeners) {
      eventListeners.forEach((callback) => callback(data))
    }
  }

  get isConnected(): boolean {
    return this.ws?.readyState === WebSocket.OPEN
  }

  get connectionState(): string {
    if (!this.ws) return "disconnected"

    switch (this.ws.readyState) {
      case WebSocket.CONNECTING:
        return "connecting"
      case WebSocket.OPEN:
        return "connected"
      case WebSocket.CLOSING:
        return "closing"
      case WebSocket.CLOSED:
        return "disconnected"
      default:
        return "unknown"
    }
  }
}
