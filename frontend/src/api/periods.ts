import apiClient from './client';
import type { Period, CreatePeriodRequest, ListPeriodsParams, InitializeFiscalYearRequest } from '../types/period';
import type { PaginationMeta } from '../types/account';

export const periodsApi = {
  list: async (params?: ListPeriodsParams): Promise<{ data: Period[]; meta: PaginationMeta }> => {
    const response = await apiClient.get('/v1/periods', { params });
    return response.data;
  },

  get: async (id: number): Promise<Period> => {
    const response = await apiClient.get(`/v1/periods/${id}`);
    return response.data.data;
  },

  create: async (data: CreatePeriodRequest): Promise<Period> => {
    const response = await apiClient.post('/v1/periods', data);
    return response.data.data;
  },

  close: async (id: number): Promise<Period> => {
    const response = await apiClient.post(`/v1/periods/${id}/close`);
    return response.data.data;
  },

  initializeFiscalYear: async (data: InitializeFiscalYearRequest): Promise<{ data: Period[] }> => {
    const response = await apiClient.post('/v1/fiscal-years/initialize', data);
    return response.data;
  },
};
