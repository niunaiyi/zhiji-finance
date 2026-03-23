export interface AuxCategory {
  id: number;
  company_id: number;
  code: string;
  name: string;
  is_system: boolean;
  created_at: string;
  updated_at: string;
}

export interface AuxItem {
  id: number;
  company_id: number;
  aux_category_id: number;
  code: string;
  name: string;
  parent_id: number | null;
  is_active: boolean;
  extra: Record<string, any> | null;
  created_at: string;
  updated_at: string;
}

export interface CreateAuxCategoryRequest {
  code: string;
  name: string;
}

export interface CreateAuxItemRequest {
  aux_category_id: number;
  code: string;
  name: string;
  parent_id?: number;
  extra?: Record<string, any>;
}
