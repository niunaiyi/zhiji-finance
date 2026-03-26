import apiClient from './client';
import type { AuxCategory, AuxItem, CreateAuxCategoryRequest, CreateAuxItemRequest } from '../types/auxiliary';

export const auxCategoriesApi = {
  list: async (params?: { is_system?: boolean }): Promise<{ data: AuxCategory[] }> => {
    const response = await apiClient.get('/v1/aux-categories', { params });
    return response.data;
  },

  get: async (id: number): Promise<AuxCategory> => {
    const response = await apiClient.get(`/v1/aux-categories/${id}`);
    return response.data.data;
  },

  create: async (data: CreateAuxCategoryRequest): Promise<AuxCategory> => {
    const response = await apiClient.post('/v1/aux-categories', data);
    return response.data.data;
  },

  update: async (id: number, data: Partial<CreateAuxCategoryRequest>): Promise<AuxCategory> => {
    const response = await apiClient.put(`/v1/aux-categories/${id}`, data);
    return response.data.data;
  },
};

export const auxItemsApi = {
  list: async (params?: { aux_category_id?: number; is_active?: boolean }): Promise<{ data: AuxItem[] }> => {
    const response = await apiClient.get('/v1/aux-items', { params });
    return response.data;
  },

  get: async (id: number): Promise<AuxItem> => {
    const response = await apiClient.get(`/v1/aux-items/${id}`);
    return response.data.data;
  },

  create: async (data: CreateAuxItemRequest): Promise<AuxItem> => {
    const response = await apiClient.post('/v1/aux-items', data);
    return response.data.data;
  },

  update: async (id: number, data: Partial<CreateAuxItemRequest>): Promise<AuxItem> => {
    const response = await apiClient.put(`/v1/aux-items/${id}`, data);
    return response.data.data;
  },
};
