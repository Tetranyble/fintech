"use client"

import type React from "react"
import { createContext, useContext, useEffect, useState, useCallback } from "react"
import { useSession } from "next-auth/react"
import { WebSocketClient, type PaymentStatusEvent, type BalanceUpdateEvent } from "@/lib/websocket"

interface WebSocketContextType {
  client: WebSocketClient | null
  isConnected: boolean
  connectionState: string
  connect: () => Promise<void>
  disconnect: () => void
  onPaymentStatusChanged: (callback: (event: PaymentStatusEvent) => void) => () => void
  onBalanceUpdated: (callback: (event: BalanceUpdateEvent) => void) => () => void
}

const WebSocketContext = createContext<WebSocketContextType | null>(null)

export function WebSocketProvider({ children }: { children: React.ReactNode }) {
  const { data: session } = useSession()
  const [client, setClient] = useState<WebSocketClient | null>(null)
  const [isConnected, setIsConnected] = useState(false)
  const [connectionState, setConnectionState] = useState("disconnected")

  const connect = useCallback(async () => {
    if (!session?.accessToken || !session?.user?.email) {
      console.log("No session available for WebSocket connection")
      return
    }

    try {
      // Create WebSocket client
      const wsUrl = process.env.NEXT_PUBLIC_WS_URL || "ws://localhost:8080"
      const customerId = session.user.email // Using email as customer ID for now

      const newClient = new WebSocketClient(wsUrl, session.accessToken, customerId)

      await newClient.connect()
      setClient(newClient)
      setIsConnected(true)
      setConnectionState("connected")

      console.log("WebSocket connected successfully")
    } catch (error) {
      console.error("Failed to connect WebSocket:", error)
      setIsConnected(false)
      setConnectionState("disconnected")
    }
  }, [session])

  const disconnect = useCallback(() => {
    if (client) {
      client.disconnect()
      setClient(null)
      setIsConnected(false)
      setConnectionState("disconnected")
    }
  }, [client])

  const onPaymentStatusChanged = useCallback(
    (callback: (event: PaymentStatusEvent) => void) => {
      if (!client) return () => {}
      return client.on("paymentStatusChanged", callback)
    },
    [client],
  )

  const onBalanceUpdated = useCallback(
    (callback: (event: BalanceUpdateEvent) => void) => {
      if (!client) return () => {}
      return client.on("balanceUpdated", callback)
    },
    [client],
  )

  // Auto-connect when session is available
  useEffect(() => {
    if (session?.accessToken && !client) {
      connect()
    }
  }, [session, client, connect])

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      disconnect()
    }
  }, [disconnect])

  // Monitor connection state
  useEffect(() => {
    if (client) {
      const interval = setInterval(() => {
        const newState = client.connectionState
        const newIsConnected = client.isConnected

        if (newState !== connectionState) {
          setConnectionState(newState)
        }

        if (newIsConnected !== isConnected) {
          setIsConnected(newIsConnected)
        }
      }, 1000)

      return () => clearInterval(interval)
    }
  }, [client, connectionState, isConnected])

  const value: WebSocketContextType = {
    client,
    isConnected,
    connectionState,
    connect,
    disconnect,
    onPaymentStatusChanged,
    onBalanceUpdated,
  }

  return <WebSocketContext.Provider value={value}>{children}</WebSocketContext.Provider>
}

export function useWebSocket() {
  const context = useContext(WebSocketContext)
  if (!context) {
    throw new Error("useWebSocket must be used within a WebSocketProvider")
  }
  return context
}
