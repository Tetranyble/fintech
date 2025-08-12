"use client"

import { useState, useCallback, useEffect } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { Badge } from "@/components/ui/badge"
import { Progress } from "@/components/ui/progress"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Textarea } from "@/components/ui/textarea"
import { useSession } from "next-auth/react"
import { usePaymentWebSocket } from "@/hooks/use-payment-websocket"
import type { PaymentStatusEvent } from "@/lib/websocket"

type PaymentStatus = "idle" | "validating" | "processing" | "success" | "failed"

interface PaymentData {
  id?: string
  amount: number
  description: string
  type: string
  status: PaymentStatus
  timestamp?: Date
  transactionId?: string
}

interface FormErrors {
  amount?: string
  description?: string
  type?: string
}

export function PaymentFlowCard() {
  const { data: session } = useSession()
  const { isConnected, subscribeToPaymentStatus } = usePaymentWebSocket()
  const [formData, setFormData] = useState({
    amount: "",
    description: "",
    type: "payment",
  })
  const [status, setStatus] = useState<PaymentStatus>("idle")
  const [message, setMessage] = useState("")
  const [errors, setErrors] = useState<FormErrors>({})
  const [progress, setProgress] = useState(0)
  const [paymentData, setPaymentData] = useState<PaymentData | null>(null)
  const [currentPaymentId, setCurrentPaymentId] = useState<string | null>(null)

  useEffect(() => {
    const unsubscribe = subscribeToPaymentStatus((event: PaymentStatusEvent) => {
      console.log("Received payment status update:", event)

      // Only update if this is for the current payment
      if (currentPaymentId && event.payment_id === currentPaymentId) {
        switch (event.status) {
          case "processing":
            setStatus("processing")
            setMessage("Payment is being processed...")
            setProgress(70)
            break
          case "completed":
            setStatus("success")
            setMessage(`Payment of ${formatCurrency(event.amount)} completed successfully!`)
            setProgress(100)
            setPaymentData((prev) => ({
              ...prev!,
              status: "success",
              timestamp: new Date(event.timestamp),
              transactionId: event.transaction_id,
            }))
            // Clear current payment ID and reset after delay
            setTimeout(() => {
              setCurrentPaymentId(null)
              resetForm()
            }, 8000)
            break
          case "failed":
            setStatus("failed")
            setMessage("Payment failed. Please try again.")
            setProgress(0)
            setTimeout(() => {
              setCurrentPaymentId(null)
              resetForm()
            }, 5000)
            break
          default:
            break
        }
      }
    })

    return unsubscribe
  }, [subscribeToPaymentStatus, currentPaymentId])

  const resetForm = () => {
    setStatus("idle")
    setMessage("")
    setProgress(0)
    setPaymentData(null)
    setFormData({
      amount: "",
      description: "",
      type: "payment",
    })
  }

  const validateForm = (): boolean => {
    const newErrors: FormErrors = {}

    if (!formData.amount || Number.parseFloat(formData.amount) <= 0) {
      newErrors.amount = "Please enter a valid amount greater than 0"
    }

    if (Number.parseFloat(formData.amount) > 10000) {
      newErrors.amount = "Amount cannot exceed $10,000"
    }

    if (!formData.type) {
      newErrors.type = "Please select a payment type"
    }

    if (formData.description.length > 255) {
      newErrors.description = "Description cannot exceed 255 characters"
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const processPayment = useCallback(async () => {
    if (!validateForm()) {
      return
    }

    setStatus("validating")
    setMessage("")
    setProgress(10)

    try {
      // Step 1: Validate payment data
      await new Promise((resolve) => setTimeout(resolve, 500))
      setProgress(30)

      setStatus("processing")
      setMessage("Processing your payment...")

      // Step 2: Process payment via Laravel API
      const paymentPayload = {
        amount: Number.parseFloat(formData.amount),
        description: formData.description || `${formData.type} payment`,
        type: formData.type,
        currency: "USD",
      }

      setProgress(60)

      let response
      if (session?.accessToken) {
        // Real API call to Laravel backend
        response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/payments`, {
          method: "POST",
          headers: {
            Authorization: `Bearer ${session.accessToken}`,
            "Content-Type": "application/json",
          },
          body: JSON.stringify(paymentPayload),
        })

        if (!response.ok) {
          throw new Error(`Payment failed: ${response.statusText}`)
        }

        const result = await response.json()

        setCurrentPaymentId(result.payment.id)

        setProgress(90)

        setPaymentData({
          id: result.payment.id,
          amount: result.payment.amount,
          description: result.payment.description,
          type: result.payment.type,
          status: "processing", // Will be updated via WebSocket
          timestamp: new Date(result.payment.created_at),
          transactionId: result.payment.transaction_id,
        })

        // Don't set success immediately - wait for WebSocket update
        setMessage("Payment submitted. Waiting for confirmation...")
      } else {
        // Mock API call for development
        await new Promise((resolve) => setTimeout(resolve, 1500))

        const mockPaymentId = `pay_${Date.now()}`
        setCurrentPaymentId(mockPaymentId)
        setProgress(90)

        // Randomize success/failure for testing (70% success rate)
        const isSuccess = Math.random() > 0.3

        if (!isSuccess) {
          throw new Error("Payment processing failed")
        }

        setPaymentData({
          id: mockPaymentId,
          amount: Number.parseFloat(formData.amount),
          description: formData.description || `${formData.type} payment`,
          type: formData.type,
          status: "success",
          timestamp: new Date(),
          transactionId: `txn_${Date.now()}`,
        })

        setProgress(100)
        setStatus("success")
        setMessage(`Payment of ${formatCurrency(Number.parseFloat(formData.amount))} processed successfully!`)

        // Clear form on success
        setTimeout(() => {
          setCurrentPaymentId(null)
          resetForm()
        }, 8000)
      }
    } catch (error) {
      setStatus("failed")
      setMessage(error instanceof Error ? error.message : "An error occurred while processing the payment")
      setProgress(0)
      setCurrentPaymentId(null)

      // Reset status after 5 seconds
      setTimeout(() => {
        resetForm()
      }, 5000)
    }
  }, [formData, session?.accessToken])

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
    }).format(amount)
  }

  const getStatusBadge = () => {
    switch (status) {
      case "validating":
        return <Badge variant="secondary">Validating...</Badge>
      case "processing":
        return (
          <div className="flex items-center gap-2">
            <Badge variant="secondary">Processing...</Badge>
            {isConnected && (
              <Badge variant="outline" className="text-xs">
                Live
              </Badge>
            )}
          </div>
        )
      case "success":
        return <Badge variant="default">Success</Badge>
      case "failed":
        return <Badge variant="destructive">Failed</Badge>
      default:
        return null
    }
  }

  const isProcessing = status === "validating" || status === "processing"

  return (
    <Card className="h-fit">
      <CardHeader>
        <div className="flex items-center justify-between">
          <CardTitle>Payment Flow</CardTitle>
          {getStatusBadge()}
        </div>
        <CardDescription>
          Initiate a new payment and receive real-time status updates
          {isConnected && " • Live updates enabled"}
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {/* Progress bar for processing */}
          {isProcessing && (
            <div className="space-y-2">
              <div className="flex justify-between text-sm">
                <span>Processing Payment</span>
                <span>{progress}%</span>
              </div>
              <Progress value={progress} className="w-full" />
            </div>
          )}

          {/* Payment Type Selection */}
          <div className="space-y-2">
            <Label htmlFor="type">Payment Type</Label>
            <Select
              value={formData.type}
              onValueChange={(value) => setFormData((prev) => ({ ...prev, type: value }))}
              disabled={isProcessing}
            >
              <SelectTrigger>
                <SelectValue placeholder="Select payment type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="payment">Standard Payment</SelectItem>
                <SelectItem value="refund">Refund</SelectItem>
                <SelectItem value="transfer">Transfer</SelectItem>
                <SelectItem value="subscription">Subscription</SelectItem>
              </SelectContent>
            </Select>
            {errors.type && <p className="text-sm text-red-600">{errors.type}</p>}
          </div>

          {/* Amount Input */}
          <div className="space-y-2">
            <Label htmlFor="amount">Amount ($)</Label>
            <Input
              id="amount"
              type="number"
              step="0.01"
              min="0.01"
              max="10000"
              placeholder="0.00"
              value={formData.amount}
              onChange={(e) => setFormData((prev) => ({ ...prev, amount: e.target.value }))}
              disabled={isProcessing}
              className={errors.amount ? "border-red-500" : ""}
            />
            {errors.amount && <p className="text-sm text-red-600">{errors.amount}</p>}
          </div>

          {/* Description Input */}
          <div className="space-y-2">
            <Label htmlFor="description">Description</Label>
            <Textarea
              id="description"
              placeholder="Payment description (optional)"
              value={formData.description}
              onChange={(e) => setFormData((prev) => ({ ...prev, description: e.target.value }))}
              disabled={isProcessing}
              maxLength={255}
              className={errors.description ? "border-red-500" : ""}
            />
            <div className="flex justify-between text-xs text-gray-500">
              <span>{errors.description || ""}</span>
              <span>{formData.description.length}/255</span>
            </div>
          </div>

          {/* Status Message */}
          {message && (
            <Alert variant={status === "failed" ? "destructive" : "default"}>
              <AlertDescription>{message}</AlertDescription>
            </Alert>
          )}

          {/* Payment Receipt */}
          {paymentData && status === "success" && (
            <div className="bg-green-50 border border-green-200 rounded-lg p-4 space-y-2">
              <h4 className="font-medium text-green-800">Payment Successful</h4>
              <div className="text-sm text-green-700 space-y-1">
                <p>
                  <strong>Amount:</strong> {formatCurrency(paymentData.amount)}
                </p>
                <p>
                  <strong>Type:</strong> {paymentData.type}
                </p>
                <p>
                  <strong>Transaction ID:</strong> {paymentData.transactionId}
                </p>
                <p>
                  <strong>Time:</strong> {paymentData.timestamp?.toLocaleString()}
                </p>
                {paymentData.description && (
                  <p>
                    <strong>Description:</strong> {paymentData.description}
                  </p>
                )}
              </div>
            </div>
          )}

          {/* Submit Button */}
          <Button onClick={processPayment} className="w-full" disabled={isProcessing || !formData.amount}>
            {status === "validating"
              ? "Validating..."
              : status === "processing"
                ? "Processing Payment..."
                : "Process Payment"}
          </Button>

          {/* Help Text */}
          <p className="text-xs text-gray-500 text-center">
            Payments are processed securely. You will receive real-time updates on the status.
            {!isConnected && " (Offline mode - limited real-time updates)"}
          </p>
        </div>
      </CardContent>
    </Card>
  )
}
