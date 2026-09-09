import { useEffect, useState } from 'react'
import { ApiError } from '../api/client'
import { getInvoice } from '../api/invoices'
import type { Invoice } from '../types/invoice'

type FetchStatus = 'idle' | 'loading' | 'success' | 'error'

export function useInvoice(id: string) {
    const [data, setData] = useState<Invoice | null>(null)
    const [fetchStatus, setFetchStatus] = useState<FetchStatus>('idle')
    const [error, setError] = useState<ApiError | null>(null)
    const [refreshIndex, setRefreshIndex] = useState(0)

    useEffect(() => {
        let cancelled = false

        setFetchStatus('loading')
        setError(null)

        getInvoice(id)
            .then((result) => {
                if (cancelled) return
                setData(result)
                setFetchStatus('success')
            })
            .catch((err: unknown) => {
                if (cancelled) return
                setError(err instanceof ApiError ? err : new ApiError(0, 'Unknown error'))
                setFetchStatus('error')
            })

        return () => {
            cancelled = true
        }
    }, [id, refreshIndex])

    const refresh = () => setRefreshIndex((n) => n + 1)

    return { data, status: fetchStatus, error, refresh }
}