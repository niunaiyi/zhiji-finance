import apiClient from './client';
import { Account, CreateAccountRequest, UpdateAccountRequest, ListAccountsParams } from '../types/account';

export const accountsApi = {
  list: async (params?: ListAccountsParams): Promise<{ data: Account[]; meta: any }> => {
    const response = await apiClient.get('/v1/accounts', { params });
    return response.data;
  },

  get: async (id: number): Promise<Account> => {
    const response = await apiClient.get(`/v1/accounts/${id}`);
    return response.data.data;
  },

  create: async (data: CreateAccountRequest): Promise<Account> => {
    const response = await apiClient.post('/v1/accounts', data);
    return response.data.data;
  },

  update: async (id: number, data: UpdateAccountRequest): Promise<Account> => {
    const response = await apiClient.put(`/v1/accounts/${id}`, data);
    return response.data.data;
  },

  deactivate: async (id: number): Promise<void> => {
    await apiClient.delete(`/v1/accounts/${id}`);
  },
};
