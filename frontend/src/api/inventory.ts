import apiClient from './client';

export interface InventoryItem {
    id: number;
    sku: string;
    name: string;
    unit: string;
    category?: string;
    current_quantity: number;
    current_average_cost: number;
}

export const inventoryApi = {
    listItems: (params?: any) =>
        apiClient.get('/v1/inventory-items', { params }),

    createItem: (data: Partial<InventoryItem>) =>
        apiClient.post('/v1/inventory-items', data),

    stockIn: (data: { item_id: number; quantity: number; unit_price: number; record_date: string; book_code: string }) =>
        apiClient.post('/v1/inventory/stock-in', data),

    stockOut: (data: { item_id: number; quantity: number; record_date: string; book_code: string }) =>
        apiClient.post('/v1/inventory/stock-out', data),

    getBalances: () =>
        apiClient.get('/v1/inventory/balances'),
};
