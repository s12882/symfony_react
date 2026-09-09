export class ApiError extends Error {
  status: number
  errors: Record<string, string[]> | null

  constructor(status: number, message: string, errors: Record<string, string[]> | null = null) {
    super(message)
    // Object.setPrototypeOf(this, ApiError.prototype)  // <-- the workaround for ES5
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }
}

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api'

interface RequestOptions {
  method?: string
  body?: unknown
}

export async function apiFetch<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    method: options.method ?? 'GET',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
  })

  const isJson = response.headers.get('content-type')?.includes('application/json') ?? false
  const data = isJson ? await response.json() : null

  if (!response.ok) {
    throw new ApiError(response.status, data?.message ?? response.statusText, data?.errors ?? null)
  }

  return data as T
}
