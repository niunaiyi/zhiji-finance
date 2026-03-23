import axios from 'axios';

const apiClient = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request interceptor - add token and company ID
apiClient.interceptors.request.use((config) => {
    const authData = localStorage.getItem('auth');
    if (authData) {
        const { token, company } = JSON.parse(authData);
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        if (company) {
            config.headers['X-Company-Id'] = company.id.toString();
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
        localStorage.removeItem('auth');
        window.location.href = '/login';
    }
    return Promise.reject(error);
});

export default apiClient;
