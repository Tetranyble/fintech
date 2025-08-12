"use client"

import { useSession } from "next-auth/react"
import { useRouter } from "next/navigation"
import { useEffect } from "react"
import { DashboardHeader } from "@/components/dashboard/dashboard-header"
import { PaymentBalanceCard } from "@/components/dashboard/payment-balance-card"
import { PaymentFlowCard } from "@/components/dashboard/payment-flow-card"

export default function Dashboard() {
  const { data: session, status } = useSession()
  const router = useRouter()

  useEffect(() => {
    if (status === "loading") return // Still loading
    if (!session) router.push("/auth/signin")
  }, [session, status, router])

  if (status === "loading") {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-lg">Loading...</div>
      </div>
    )
  }

  if (!session) {
    return null
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <DashboardHeader />
      <main className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Payment Dashboard</h1>
          <p className="text-gray-600 mt-2">Monitor your payment balance and initiate new payments</p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <PaymentBalanceCard />
          <PaymentFlowCard />
        </div>
      </main>
    </div>
  )
}
