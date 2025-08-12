import type { NextAuthOptions } from "next-auth"
import CredentialsProvider from "next-auth/providers/credentials"
import { jwtDecode } from "jwt-decode";

export const authOptions: NextAuthOptions = {
  providers: [
    CredentialsProvider({
      name: "credentials",
      credentials: {
        email: { label: "Email", type: "email" },
        password: { label: "Password", type: "password" },
      },
      async authorize(credentials) {
        if (!credentials?.email || !credentials?.password) {
          return null
        }
  

        try {
          // Call Laravel API for authentication
          const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/login`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              //"Accept": "application/json"
            },
            body: JSON.stringify({
              email: credentials.email,
              password: credentials.password,
            }),
          })
          console.log("Response status:", response)

          const data = await response.json()
          
          if (response.ok && data.status) {
          type MyJwtPayload = {
            data: any;
            id: string;
            email: string;
            name: string;
            // add other fields if needed
          };

          const user = jwtDecode<MyJwtPayload>(data.data.access_token);

          return {
            id: user.data?.id,
            email: user?.data.email,
            name: user?.data.name,
            accessToken: data.data.access_token,
          }
          }
          return null
        } catch (error) {
          console.error("Authentication error:", error)
          return null
        }
      },
    }),
  ],
  callbacks: {
    async jwt({ token, user }) {
      if (user) {
        token.accessToken = user.accessToken
      }
      return token
    },
    async session({ session, token }) {
      session.accessToken = token.accessToken
      return session
    },
  },
  pages: {
    signIn: "/auth/signin",
  },
  session: {
    strategy: "jwt",
  },
}


