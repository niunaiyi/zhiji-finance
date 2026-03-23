// frontend/src/types/auth.ts
export interface User {
  id: number;
  name: string;
  email: string;
}

export interface Company {
  id: number;
  code: string;
  name: string;
  fiscal_year_start: number;
  status: 'active' | 'suspended';
}

export interface UserCompanyRole {
  id: number;
  user_id: number;
  company_id: number;
  role: 'admin' | 'accountant' | 'auditor' | 'viewer';
  is_active: boolean;
}

export interface LoginResponse {
  user: User;
  companies: Company[];
}

export interface AuthState {
  user: User | null;
  token: string | null;
  company: Company | null;
  role: string | null;
  companies: Company[];
}
