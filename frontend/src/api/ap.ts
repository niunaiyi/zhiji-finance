import apiClient from './client';

export interface ApBill {
    id: number;
    bill_no: string;
    bill_date: string;
    supplier_id: number;
    supplier?: { id: number; name: string; code: string };
    period_id: number;
    amount: string;
    settled_amount: string;
    balance: string;
    status: 'open' | 'partial' | 'settled' | 'voided';
    is_estimate: boolean;
    source_type?: string;
    source_id?: number;
}

export interface ApPayment {
    id: number;
    payment_no: string;
    payment_date: string;
    supplier_id: number;
    supplier?: { id: number; name: string; code: string };
    period_id: number;
    amount: string;
    settled_amount: string;
    balance: string;
    status: 'open' | 'partial' | 'settled';
}

export interface CreateApBillRequest {
    period_id: number;
    supplier_id: number;
    bill_no: string;
    bill_date: string;
    amount: number;
    is_estimate?: boolean;
}

export interface CreateApPaymentRequest {
    period_id: number;
    supplier_id: number;
    payment_no: string;
    payment_date: string;
    amount: number;
}

export const apApi = {
    listBills: (params?: any) =>
        apiClient.get('/v1/ap/bills', { params }),

    createBill: (data: CreateApBillRequest) =>
        apiClient.post('/v1/ap/bills', data),

    getBill: (id: number) =>
        apiClient.get(`/v1/ap/bills/${id}`),

    listPayments: (params?: any) =>
        apiClient.get('/v1/ap/payments', { params }),

    createPayment: (data: CreateApPaymentRequest) =>
        apiClient.post('/v1/ap/payments', data),

    settle: (apBillId: number, apPaymentId: number, amount: number) =>
        apiClient.post('/v1/ap/settle', {
            ap_bill_id: apBillId,
            ap_payment_id: apPaymentId,
            amount,
        }),
};
