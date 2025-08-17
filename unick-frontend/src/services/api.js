// src/services/api.js
import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle auth errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_data');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export const authAPI = {
  login: (credentials) => api.post('/login', credentials),
  register: (userData) => api.post('/register', userData),
  logout: () => api.post('/logout'),
  profile: () => api.get('/profile'),
};

export const productsAPI = {
  getAll: (params) => api.get('/products', { params }),
  getOne: (id) => api.get(`/products/${id}`),
  create: (data) => api.post('/products', data),
  update: (id, data) => api.put(`/products/${id}`, data),
  delete: (id) => api.delete(`/products/${id}`),
  getLowStock: () => api.get('/products/low-stock'),
};

export const ordersAPI = {
  getAll: (params) => api.get('/orders', { params }),
  getOne: (id) => api.get(`/orders/${id}`),
  create: (data) => api.post('/orders', data),
  update: (id, data) => api.put(`/orders/${id}`, data),
  approve: (id) => api.post(`/orders/${id}/approve`),
};

export const inventoryAPI = {
  getRawMaterials: (params) => api.get('/inventory/raw-materials', { params }),
  getProducts: (params) => api.get('/inventory/products', { params }),
  getLowStock: () => api.get('/inventory/low-stock'),
  adjustStock: (data) => api.post('/inventory/adjust', data),
  getTransactions: (params) => api.get('/inventory/transactions', { params }),
};

export const productionsAPI = {
  getBatches: (params) => api.get('/production-batches', { params }),
  getBatch: (id) => api.get(`/production-batches/${id}`),
  createBatch: (data) => api.post('/production-batches', data),
  updateBatch: (id, data) => api.put(`/production-batches/${id}`, data),
  addLog: (id, data) => api.post(`/production/${id}/log`, data),
};

export const reportsAPI = {
  getInventory: () => api.get('/reports/inventory'),
  getSales: (params) => api.get('/reports/sales', { params }),
  getProduction: (params) => api.get('/reports/production', { params }),
  downloadInventory: () => api.get('/reports/inventory/download', { responseType: 'blob' }),
  downloadSales: (params) => api.get('/reports/sales/download', { params, responseType: 'blob' }),
  downloadProduction: (params) => api.get('/reports/production/download', { params, responseType: 'blob' }),
};
