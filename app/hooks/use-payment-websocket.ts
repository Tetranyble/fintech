"use client"

import { useCallback } from "react"
import { useWebSocket } from "@/components/providers/websocket-provider"
import type { PaymentStatusEvent, BalanceUpdateEvent } from "@/lib/websocket"

export function usePaymentWebSocket() {
  const { isConnected, onPaymentStatusChanged, onBalanceUpdated } = useWebSocket()

  const subscribeToPaymentStatus = useCallback(
    (callback: (event: PaymentStatusEvent) => void) => {
      return onPaymentStatusChanged(callback)
    },
    [onPaymentStatusChanged],
  )

  const subscribeToBalanceUpdates = useCallback(
    (callback: (event: BalanceUpdateEvent) => void) => {
      return onBalanceUpdated(callback)
    },
    [onBalanceUpdated],
  )

  return {
    isConnected,
    subscribeToPaymentStatus,
    subscribeToBalanceUpdates,
  }
}
