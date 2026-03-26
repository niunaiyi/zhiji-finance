import apiClient from './client';

export interface PayrollRecord {
    id: number;
    employee_id: number;
    period_id: number;
    basic_salary: string;
    allowances: string;
    deductions: string;
    net_salary: string;
    status: 'draft' | 'posted';
    payroll_no?: string;
    payroll_date?: string;
    period?: { year: number; month: number };
    lines: any[];
}

export const payrollApi = {
    listPayrolls: (params?: any) =>
        apiClient.get('/v1/payroll', { params }),

    calculate: (data: { period_id: number }) =>
        apiClient.post('/v1/payroll/calculate', data),

    listItems: (params?: any) =>
        apiClient.get('/v1/payroll/items', { params }),

    saveItem: (data: any) =>
        apiClient.post('/v1/payroll/items', data),
};
