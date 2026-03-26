import axios from 'axios';
import type { AuthState } from '../types/auth';

const AUTH_STORAGE_KEY = 'auth';
const LOGIN_PATH = '/login';

const apiClient = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'App-Identifier': 'web',
    },
});

// Request interceptor - add token and company ID
apiClient.interceptors.request.use((config) => {
    const authData = localStorage.getItem(AUTH_STORAGE_KEY);
    if (authData) {
        try {
            const { token, company }: AuthState = JSON.parse(authData);
            if (token && !config.headers.Authorization) {
                config.headers.Authorization = `Bearer ${token}`;
            }
            if (company?.id) {
                config.headers['X-Company-Id'] = company.id.toString();
            }
        } catch (error) {
            console.error('Failed to parse auth data:', error);
            localStorage.removeItem(AUTH_STORAGE_KEY);
        }
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

// Response interceptor - handle 401
apiClient.interceptors.response.use((response) => {
    return response;
}, (error) => {
    if (error.response && error.response.status === 401) {
        // Clear auth and redirect to login
        localStorage.removeItem(AUTH_STORAGE_KEY);
        window.location.href = LOGIN_PATH;
    }
    return Promise.reject(error);
});

export default apiClient;
