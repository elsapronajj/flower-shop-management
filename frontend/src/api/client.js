import axios from 'axios'

let accessToken = null
let onTokenChanged = () => {}
let onUnauthorized = () => {}
let refreshRequest = null

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

export function setAccessToken(token) {
  accessToken = token
  onTokenChanged(token)
}

export function getAccessToken() {
  return accessToken
}

export function registerTokenListener(listener) {
  onTokenChanged = listener
}

export function registerUnauthorizedListener(listener) {
  onUnauthorized = listener
}

api.interceptors.request.use((config) => {
  if (accessToken && !config.skipAuth) {
    config.headers.Authorization = `Bearer ${accessToken}`
  }

  return config
})

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config
    const status = error.response?.status
    const isRefreshCall = originalRequest?.url?.includes('/refresh-token')

    if (status !== 401 || originalRequest?._retry || isRefreshCall) {
      return Promise.reject(error)
    }

    originalRequest._retry = true

    try {
      refreshRequest =
        refreshRequest ||
        api.post('/refresh-token', {}, { skipAuth: true }).finally(() => {
          refreshRequest = null
        })

      const response = await refreshRequest
      const token = response.data?.data?.access_token

      if (!token) {
        throw new Error('Refresh endpoint did not return an access token.')
      }

      setAccessToken(token)
      originalRequest.headers.Authorization = `Bearer ${token}`

      return api(originalRequest)
    } catch (refreshError) {
      setAccessToken(null)
      onUnauthorized()
      return Promise.reject(refreshError)
    }
  },
)

export function unwrap(response) {
  return response.data?.data
}

export function getList(payload) {
  if (Array.isArray(payload)) {
    return payload
  }

  return payload?.data ?? []
}
