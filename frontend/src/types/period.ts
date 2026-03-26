export interface Period {
  id: number;
  company_id: number;
  fiscal_year: number;
  period_number: number;
  start_date: string;
  end_date: string;
  status: 'open' | 'closed' | 'locked';
  closed_at: string | null;
  closed_by: number | null;
  created_at: string;
  updated_at: string;
}

export interface CreatePeriodRequest {
  fiscal_year: number;
  period_number: number;
  start_date: string;
  end_date: string;
}

export interface ListPeriodsParams {
  fiscal_year?: number;
  status?: 'open' | 'closed' | 'locked';
  page?: number;
  per_page?: number;
}

export interface InitializeFiscalYearRequest {
  fiscal_year: number;
  start_month?: number;
}
