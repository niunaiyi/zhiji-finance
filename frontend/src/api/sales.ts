import apiClient from './client';

export interface SalesInvoice {
    id: number;
    invoice_no: string;
    invoice_date: string;
    customer_name: string;
    total_amount: string;
    status: 'draft' | 'posted';
    remark?: string;
}

export const salesApi = {
    listInvoices: (params?: any) =>
        apiClient.get('/v1/sales/invoices', { params }),

    createInvoice: (data: any) =>
        apiClient.post('/v1/sales/invoices', data),

    getInvoice: (id: number) =>
        apiClient.get(`/v1/sales/invoices/${id}`),

    postInvoice: (id: number) =>
        apiClient.post(`/v1/sales/invoices/${id}/post`),

    listOrders: (params?: any) =>
        apiClient.get('/v1/sales/orders', { params }),

    createOrder: (data: any) =>
        apiClient.post('/v1/sales/orders', data),
};
