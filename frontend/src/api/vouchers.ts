import apiClient from './client';

export interface VoucherLine {
  account_id: number;
  summary?: string;
  debit: string;
  credit: string;
  aux_items?: Array<{
    aux_category_id: number;
    aux_item_id: number;
  }>;
}

export interface CreateVoucherRequest {
  period_id: number;
  voucher_type: 'receipt' | 'payment' | 'transfer';
  voucher_date: string;
  summary?: string;
  lines: VoucherLine[];
}

export interface Voucher {
  id: number;
  company_id: number;
  period_id: number;
  voucher_type: string;
  voucher_no: string;
  voucher_date: string;
  status: string;
  summary?: string;
  total_debit: string;
  total_credit: string;
  created_at: string;
  lines?: VoucherLine[];
}

export const vouchersApi = {
  list: (params?: any) =>
    apiClient.get('/v1/vouchers', { params }),

  get: (id: number) =>
    apiClient.get(`/v1/vouchers/${id}`),

  create: (data: CreateVoucherRequest) =>
    apiClient.post('/v1/vouchers', data),

  review: (id: number) =>
    apiClient.post(`/v1/vouchers/${id}/review`),

  post: (id: number) =>
    apiClient.post(`/v1/vouchers/${id}/post`),

  reverse: (id: number) =>
    apiClient.post(`/v1/vouchers/${id}/reverse`),

  void: (id: number) =>
    apiClient.post(`/v1/vouchers/${id}/void`),
};
