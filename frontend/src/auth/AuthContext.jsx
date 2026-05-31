/* eslint-disable react-refresh/only-export-components */
import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import { api, registerTokenListener, registerUnauthorizedListener, setAccessToken, unwrap } from '../api/client'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [token, setToken] = useState(null)
  const [user, setUser] = useState(null)
  const [booting, setBooting] = useState(true)

  useEffect(() => {
    registerTokenListener(setToken)
    registerUnauthorizedListener(() => setUser(null))

    api
      .post('/refresh-token', {}, { skipAuth: true })
      .then((response) => {
        const data = unwrap(response)
        setAccessToken(data.access_token)
        setUser(data.user)
      })
      .catch(() => {
        setAccessToken(null)
        setUser(null)
      })
      .finally(() => setBooting(false))
  }, [])

  const value = useMemo(
    () => ({
      booting,
      token,
      user,
      isAuthenticated: Boolean(token && user),
      async login(credentials) {
        const response = await api.post('/login', credentials, { skipAuth: true })
        const data = unwrap(response)
        setAccessToken(data.access_token)
        setUser(data.user)
      },
      async register(payload) {
        const response = await api.post('/register', payload, { skipAuth: true })
        const data = unwrap(response)
        setAccessToken(data.access_token)
        setUser(data.user)
      },
      async logout() {
        try {
          await api.post('/logout')
        } finally {
          setAccessToken(null)
          setUser(null)
        }
      },
    }),
    [booting, token, user],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const context = useContext(AuthContext)

  if (!context) {
    throw new Error('useAuth must be used within AuthProvider.')
  }

  return context
}
