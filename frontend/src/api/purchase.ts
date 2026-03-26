import apiClient from './client';

export interface PurchaseBill {
    id: number;
    bill_no: string;
    bill_date: string;
    vendor_name: string;
    total_amount: string;
    status: 'draft' | 'posted';
    remark?: string;
}

export const purchaseApi = {
    listBills: (params?: any) =>
        apiClient.get('/v1/purchase/bills', { params }),

    createBill: (data: any) =>
        apiClient.post('/v1/purchase/bills', data),

    getBill: (id: number) =>
        apiClient.get(`/v1/purchase/bills/${id}`),

    postBill: (id: number) =>
        apiClient.post(`/v1/purchase/bills/${id}/post`),

    listOrders: (params?: any) =>
        apiClient.get('/v1/purchase/orders', { params }),

    createOrder: (data: any) =>
        apiClient.post('/v1/purchase/orders', data),
};
