export interface Account {
  id: number;
  company_id: number;
  code: string;
  name: string;
  parent_id: number | null;
  level: number;
  element_type: 'asset' | 'liability' | 'equity' | 'income' | 'expense' | 'cost';
  balance_direction: 'debit' | 'credit';
  is_detail: boolean;
  is_active: boolean;
  has_aux: boolean;
  created_at: string;
  updated_at: string;
}

export interface CreateAccountRequest {
  code: string;
  name: string;
  parent_id?: number;
  element_type?: 'asset' | 'liability' | 'equity' | 'income' | 'expense' | 'cost';
  balance_direction?: 'debit' | 'credit';
  has_aux?: boolean;
}

export interface UpdateAccountRequest {
  name: string;
  is_active?: boolean;
}

export interface ListAccountsParams {
  parent_id?: number;
  is_active?: boolean;
  is_detail?: boolean;
  page?: number;
  per_page?: number;
}

export interface PaginationMeta {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}
