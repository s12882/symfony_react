export type InvoiceStatus = 'pending' | 'approved' | 'rejected'

export interface Invoice {
  id: string
  number: string
  supplier_name: string
  supplier_tax_id: string
  net_amount: string
  vat_amount: string
  gross_amount: string
  currency: string
  status: InvoiceStatus
  issue_date: string
  due_date: string
  created_at: string
  updated_at: string
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  total: number
}

export interface PaginatedInvoices {
  data: Invoice[]
  meta: PaginationMeta
}

export interface CreateInvoicePayload {
  number: string
  supplier_name: string
  supplier_tax_id: string
  net_amount: string
  vat_amount: string
  gross_amount: string
  currency: string
  issue_date: string
  due_date: string
}

export interface UpdateInvoicePayload {
  net_amount: string
  vat_amount: string
  due_date: string
}

export interface ApiValidationError {
  message: string
  errors: Record<string, string[]>
}
