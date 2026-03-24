import apiClient from './client';

export interface Balance {
  account: any;
  opening_debit: string;
  opening_credit: string;
  period_debit: string;
  period_credit: string;
  closing_debit: string;
  closing_credit: string;
}

export interface DetailLedgerEntry {
  voucher_no: string;
  voucher_date: string;
  summary: string;
  debit: string;
  credit: string;
}

export const generalLedgerApi = {
  balanceSheet: (params: { period_id: number; account_id?: number }) =>
    apiClient.get('/api/v1/general-ledger/balance-sheet', { params }),

  detailLedger: (params: { period_id: number; account_id: number }) =>
    apiClient.get('/api/v1/general-ledger/detail-ledger', { params }),

  chronological: (params: { period_id: number; start_date?: string; end_date?: string }) =>
    apiClient.get('/api/v1/general-ledger/chronological', { params }),

  auxiliaryLedger: (params: { period_id: number; aux_category_id: number; aux_item_id?: number }) =>
    apiClient.get('/api/v1/general-ledger/auxiliary-ledger', { params }),
};
