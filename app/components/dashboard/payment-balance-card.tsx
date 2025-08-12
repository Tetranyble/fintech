"use client"

import { useState, useEffect, useCallback } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Skeleton } from "@/components/ui/skeleton"
import { useSession } from "next-auth/react"
import { usePaymentWebSocket } from "@/hooks/use-payment-websocket"
import type { BalanceUpdateEvent } from "@/lib/websocket"

interface Transaction {
  id: string
  type: "credit" | "debit"
  amount: number
  description: string
  timestamp: Date
  status: "completed" | "pending" | "failed"
}

interface BalanceData {
  current: number
  previous: number
  lastUpdated: Date
}

export function PaymentBalanceCard() {
  const { data: session } = useSession()
  const { isConnected, subscribeToBalanceUpdates } = usePaymentWebSocket()
  const [balance, setBalance] = useState<BalanceData>({
    current: 0,
    previous: 0,
    lastUpdated: new Date(),
  })
  const [transactions, setTransactions] = useState<Transaction[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [balanceChange, setBalanceChange] = useState<number | null>(null)

  // Added WebSocket listener for real-time balance updates
  useEffect(() => {
    const unsubscribe = subscribeToBalanceUpdates((event: BalanceUpdateEvent) => {
      console.log("Received balance update:", event)

      setBalance((prev) => {
        const change = event.balance - prev.current
        if (change !== 0) {
          setBalanceChange(change)
          // Clear the change indicator after animation
          setTimeout(() => setBalanceChange(null), 3000)
        }

        return {
          current: event.balance,
          previous: event.previous_balance,
          lastUpdated: new Date(event.timestamp),
        }
      })

      // Add a new transaction entry for the balance change
      if (event.change !== 0) {
        const newTransaction: Transaction = {
          id: `ws_${Date.now()}`,
          type: event.change > 0 ? "credit" : "debit",
          amount: Math.abs(event.change),
          description: event.change > 0 ? "Payment received" : "Payment processed",
          timestamp: new Date(event.timestamp),
          status: "completed",
        }

        setTransactions((prev) => [newTransaction, ...prev.slice(0, 4)])
      }
    })

    return unsubscribe
  }, [subscribeToBalanceUpdates])

  const fetchBalance = useCallback(async () => {
    if (!session?.accessToken) return

    try {
      setError(null)
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/balance`, {
        headers: {
          Authorization: `Bearer ${session.accessToken}`,
          "Content-Type": "application/json",
        },
      })

      if (!response.ok) {
        throw new Error("Failed to fetch balance")
      }

      const data = await response.json()
      
      setBalance((prev) => {
        const change = data.balance - prev.current
        if (change !== 0) {
          setBalanceChange(change)
          // Clear the change indicator after animation
          setTimeout(() => setBalanceChange(null), 3000)
        }

        return {
          current: data.balance,
          previous: prev.current,
          lastUpdated: new Date(),
        }
      })

      setTransactions(data.recent_transactions || [])
    } catch (err) {
      setError(err instanceof Error ? err.message : "Failed to load balance")
    } finally {
      setIsLoading(false)
    }
  }, [session?.accessToken])

  const fetchTransactions = useCallback(async () => {
    if (!session?.accessToken) return

    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/transactions`, {
        headers: {
          Authorization: `Bearer ${session.accessToken}`,
          "Content-Type": "application/json",
        },
      })

      if (response.ok) {
        const data = await response.json()
        setTransactions(data.transactions || [])
      }
    } catch (err) {
      console.error("Failed to fetch transactions:", err)
    }
  }, [session?.accessToken])

  useEffect(() => {
    if (session?.accessToken) {
      fetchBalance()
      fetchTransactions()
    }
  }, [session?.accessToken, fetchBalance, fetchTransactions])

  useEffect(() => {
    if (!session?.accessToken) {
      // Mock data for development
      setBalance({
        current: 1250.75,
        previous: 1125.75,
        lastUpdated: new Date(),
      })
      setTransactions([
        {
          id: "1",
          type: "credit",
          amount: 125.0,
          description: "Payment received from client",
          timestamp: new Date(Date.now() - 300000), // 5 minutes ago
          status: "completed",
        },
        {
          id: "2",
          type: "debit",
          amount: 50.0,
          description: "Payment processed",
          timestamp: new Date(Date.now() - 900000), // 15 minutes ago
          status: "completed",
        },
        {
          id: "3",
          type: "credit",
          amount: 75.25,
          description: "Refund processed",
          timestamp: new Date(Date.now() - 1800000), // 30 minutes ago
          status: "completed",
        },
      ])
      setIsLoading(false)
    }
  }, [session?.accessToken])

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
    }).format(amount)
  }

  const formatTime = (date: Date) => {
    return date.toLocaleTimeString("en-US", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
    })
  }

  const formatRelativeTime = (date: Date) => {
    const now = new Date()
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))

    if (diffInMinutes < 1) return "Just now"
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`
    return `${Math.floor(diffInMinutes / 1440)}d ago`
  }

  const getTransactionIcon = (type: "credit" | "debit") => {
    return type === "credit" ? "↗" : "↙"
  }

  const getTransactionColor = (type: "credit" | "debit") => {
    return type === "credit" ? "text-green-600" : "text-red-600"
  }

  if (isLoading) {
    return (
      <Card className="h-fit">
        <CardHeader>
          <div className="flex items-center justify-between">
            <Skeleton className="h-6 w-32" />
            <Skeleton className="h-5 w-20" />
          </div>
          <Skeleton className="h-4 w-48" />
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <Skeleton className="h-10 w-32" />
            <Skeleton className="h-4 w-40" />
            <div className="pt-4 border-t space-y-2">
              <Skeleton className="h-4 w-24" />
              <Skeleton className="h-8 w-full" />
              <Skeleton className="h-8 w-full" />
            </div>
          </div>
        </CardContent>
      </Card>
    )
  }

  return (
    <Card className="h-fit">
      <CardHeader>
        <div className="flex items-center justify-between">
          <CardTitle>Payment Balance</CardTitle>
          <div className="flex items-center gap-2">
            {balanceChange && (
              <Badge variant={balanceChange > 0 ? "default" : "destructive"} className="animate-pulse">
                {balanceChange > 0 ? "+" : ""}
                {formatCurrency(balanceChange)}
              </Badge>
            )}
            {/* Updated to use actual WebSocket connection status */}
            <Badge variant={isConnected ? "default" : "destructive"}>{isConnected ? "Live" : "Offline"}</Badge>
          </div>
        </div>
        <CardDescription>
          Your current account balance with real-time updates
          {isConnected && " • Live updates enabled"}
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {error ? (
            <div className="text-center py-4">
              <p className="text-red-600 mb-2">{error}</p>
              <Button variant="outline" size="sm" onClick={fetchBalance}>
                Retry
              </Button>
            </div>
          ) : (
            <>
              <div>
                <div
                  className={`text-3xl font-bold transition-all duration-500 ${
                    balanceChange ? "scale-105" : ""
                  } ${balance.current >= 0 ? "text-green-600" : "text-red-600"}`}
                >
                  {formatCurrency(balance.current)}
                </div>
                <div className="flex items-center justify-between mt-1">
                  <p className="text-sm text-gray-500">Last updated: {formatTime(balance.lastUpdated)}</p>
                  <Button variant="ghost" size="sm" onClick={fetchBalance}>
                    Refresh
                  </Button>
                </div>
              </div>

              <div className="pt-4 border-t">
                <div className="flex items-center justify-between mb-3">
                  <h4 className="text-sm font-medium text-gray-700">Recent Activity</h4>
                  <Button variant="ghost" size="sm" onClick={fetchTransactions}>
                    Refresh
                  </Button>
                </div>

                {transactions.length === 0 ? (
                  <p className="text-sm text-gray-500 text-center py-4">No recent transactions</p>
                ) : (
                  <div className="space-y-3 max-h-48 overflow-y-auto">
                    {transactions.slice(0, 5).map((transaction) => (
                      <div
                        key={transaction.id}
                        className="flex justify-between items-center text-sm border-b border-gray-100 pb-2"
                      >
                        <div className="flex items-center gap-2">
                          <span className="text-lg">{getTransactionIcon(transaction.type)}</span>
                          <div>
                            <p className="font-medium text-gray-700">{transaction.description}</p>
                            <p className="text-xs text-gray-500">{formatRelativeTime(transaction.timestamp)}</p>
                          </div>
                        </div>
                        <div className="text-right">
                          <p className={`font-medium ${getTransactionColor(transaction.type)}`}>
                            {transaction.type === "credit" ? "+" : "-"}
                            {formatCurrency(transaction.amount)}
                          </p>
                          <Badge
                            variant={transaction.status === "completed" ? "default" : "secondary"}
                            className="text-xs"
                          >
                            {transaction.status}
                          </Badge>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </>
          )}
        </div>
      </CardContent>
    </Card>
  )
}
