import apiClient from './client';

export interface FixedAsset {
    id: number;
    asset_no: string;
    name: string;
    category: string;
    purchase_date: string;
    original_value: string;
    accumulated_depreciation: string;
    net_value: string;
    useful_life_months: number;
    residual_rate: string;
    depreciation_method: string;
    status: string;
}

export const fixedAssetsApi = {
    listAssets: (params?: any) =>
        apiClient.get('/v1/fixed-assets', { params }),

    createAsset: (data: Partial<FixedAsset>) =>
        apiClient.post('/v1/fixed-assets', data),

    updateAsset: (id: number, data: Partial<FixedAsset>) =>
        apiClient.patch(`/v1/fixed-assets/${id}`, data),

    deleteAsset: (id: number) =>
        apiClient.delete(`/v1/fixed-assets/${id}`),

    calculateDepreciation: () =>
        apiClient.post('/v1/fixed-assets/calculate-depreciation'),

    generateVoucher: (data: { date: string }) =>
        apiClient.post('/v1/fixed-assets/generate-voucher', data),
};
