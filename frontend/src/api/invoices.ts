import { apiFetch } from './client'
import type {
  CreateInvoicePayload,
  Invoice,
  InvoiceStatus,
  PaginatedInvoices,
  UpdateInvoicePayload,
} from '../types/invoice'

interface ListInvoicesParams {
  status?: InvoiceStatus
  page?: number
}

export function listInvoices(params: ListInvoicesParams = {}): Promise<PaginatedInvoices> {
  const query = new URLSearchParams()
  if (params.status) query.set('status', params.status)
  if (params.page) query.set('page', String(params.page))

  const queryString = query.toString()
  return apiFetch<PaginatedInvoices>(`/invoices${queryString ? `?${queryString}` : ''}`)
}

export function getInvoice(id: string): Promise<Invoice> {
  return apiFetch<Invoice>(`/invoices/${id}`)
}

export function createInvoice(payload: CreateInvoicePayload): Promise<Invoice> {
  return apiFetch<Invoice>('/invoices', { method: 'POST', body: payload })
}

export function updateInvoice(id: string, payload: UpdateInvoicePayload): Promise<Invoice> {
  return apiFetch<Invoice>(`/invoices/${id}`, { method: 'PUT', body: payload })
}
