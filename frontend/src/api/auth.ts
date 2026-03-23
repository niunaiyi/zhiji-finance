// frontend/src/api/auth.ts
import apiClient from './client';
import { LoginResponse, Company, SelectCompanyResponse } from '../types/auth';

export const authApi = {
  login: async (email: string, password: string): Promise<LoginResponse> => {
    const response = await apiClient.post('/v1/auth/login', { email, password });
    return response.data;
  },

  selectCompany: async (companyId: number): Promise<SelectCompanyResponse> => {
    const response = await apiClient.post('/v1/auth/select-company', { company_id: companyId });
    return response.data;
  },

  logout: async (): Promise<void> => {
    await apiClient.post('/v1/auth/logout');
  },
};
