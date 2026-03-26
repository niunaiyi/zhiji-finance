import apiClient from './client';

export interface ArBill {
    id: number;
    bill_no: string;
    bill_date: string;
    customer_id: number;
    customer?: { id: number; name: string; code: string };
    period_id: number;
    amount: string;
    settled_amount: string;
    balance: string;
    status: 'open' | 'partial' | 'settled' | 'voided';
    source_type?: string;
    source_id?: number;
}

export interface ArReceipt {
    id: number;
    receipt_no: string;
    receipt_date: string;
    customer_id: number;
    customer?: { id: number; name: string; code: string };
    period_id: number;
    amount: string;
    settled_amount: string;
    balance: string;
    status: 'open' | 'partial' | 'settled';
}

export interface CreateArBillRequest {
    period_id: number;
    customer_id: number;
    bill_no: string;
    bill_date: string;
    amount: number;
    source_type?: string;
    source_id?: number;
}

export interface CreateArReceiptRequest {
    period_id: number;
    customer_id: number;
    receipt_no: string;
    receipt_date: string;
    amount: number;
}

export const arApi = {
    listBills: (params?: any) =>
        apiClient.get('/v1/ar/bills', { params }),

    createBill: (data: CreateArBillRequest) =>
        apiClient.post('/v1/ar/bills', data),

    getBill: (id: number) =>
        apiClient.get(`/v1/ar/bills/${id}`),

    listReceipts: (params?: any) =>
        apiClient.get('/v1/ar/receipts', { params }),

    createReceipt: (data: CreateArReceiptRequest) =>
        apiClient.post('/v1/ar/receipts', data),

    settle: (arBillId: number, arReceiptId: number, amount: number) =>
        apiClient.post('/v1/ar/settle', {
            ar_bill_id: arBillId,
            ar_receipt_id: arReceiptId,
            amount,
        }),
};
