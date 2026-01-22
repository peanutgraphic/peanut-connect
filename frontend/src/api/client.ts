import axios, { AxiosInstance, AxiosError } from 'axios';
import type { ApiResponse } from '@/types';

// WordPress passes these via wp_localize_script
declare global {
  interface Window {
    peanutConnect?: {
      apiUrl: string;
      nonce: string;
      version: string;
    };
  }
}

// Get config from WordPress or use defaults for development
const getConfig = () => {
  if (window.peanutConnect) {
    return {
      baseURL: window.peanutConnect.apiUrl,
      nonce: window.peanutConnect.nonce,
    };
  }

  // Development fallback
  return {
    baseURL: '/wp-json/peanut-connect/v1',
    nonce: '',
  };
};

const config = getConfig();

// Create axios instance
const api: AxiosInstance = axios.create({
  baseURL: config.baseURL,
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': config.nonce,
  },
});

// Response interceptor
api.interceptors.response.use(
  (response) => {
    // Handle API response format
    const data = response.data;
    if (data && typeof data === 'object' && 'success' in data) {
      if (!data.success) {
        return Promise.reject(new Error(data.message || 'Request failed'));
      }
      // Preserve message alongside inner data for mutation handlers
      return {
        ...response,
        data: {
          ...data.data,
          message: data.message,
          success: data.success,
        },
      };
    }
    return response;
  },
  (error: AxiosError<ApiResponse<unknown>>) => {
    const message = error.response?.data?.message || error.message || 'An error occurred';
    return Promise.reject(new Error(message));
  }
);

export default api;

// Helper to check if we're in WordPress admin
export const isWordPressAdmin = (): boolean => {
  return typeof window.peanutConnect !== 'undefined';
};

// Helper to get plugin version
export const getVersion = (): string => {
  return window.peanutConnect?.version || '1.0.0';
};
