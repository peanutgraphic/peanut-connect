import api from './client';
import type {
  Settings,
  Permissions,
  HealthData,
  AvailableUpdates,
  UpdateResult,
  VerifyResponse,
  AnalyticsData,
  ErrorLogData,
  ErrorCountsData,
  ErrorLevel,
} from '@/types';

// Settings API
export const settingsApi = {
  // Get current settings (connection, permissions, etc.)
  get: async (): Promise<Settings> => {
    const response = await api.get('/settings');
    return response.data;
  },

  // Update permissions
  updatePermissions: async (permissions: Partial<Permissions>): Promise<Permissions> => {
    const response = await api.post('/settings/permissions', permissions);
    return response.data;
  },

  // Generate new site key
  generateKey: async (): Promise<{ site_key: string }> => {
    const response = await api.post('/settings/generate-key');
    return response.data;
  },

  // Regenerate site key
  regenerateKey: async (): Promise<{ site_key: string }> => {
    const response = await api.post('/settings/regenerate-key');
    return response.data;
  },

  // Disconnect from manager
  disconnect: async (): Promise<{ success: boolean }> => {
    const response = await api.post('/settings/disconnect');
    return response.data;
  },

  // Hub settings - save
  saveHubSettings: async (hubUrl: string, apiKey: string): Promise<{
    success: boolean;
    message: string;
    data?: {
      site: Record<string, unknown>;
      client: Record<string, unknown>;
      agency: Record<string, unknown>;
    };
  }> => {
    const response = await api.post('/settings/hub', { hub_url: hubUrl, api_key: apiKey });
    return response.data;
  },

  // Hub settings - test connection
  testHubConnection: async (): Promise<{
    success: boolean;
    message: string;
  }> => {
    const response = await api.post('/settings/hub/test');
    return response.data;
  },

  // Hub settings - disconnect
  disconnectHub: async (): Promise<{ success: boolean; message: string }> => {
    const response = await api.post('/settings/hub/disconnect');
    return response.data;
  },

  // Hub settings - trigger sync
  triggerHubSync: async (): Promise<{ success: boolean; message: string }> => {
    const response = await api.post('/settings/hub/sync');
    return response.data;
  },
};

// Health API
export const healthApi = {
  // Get full health data (admin endpoint)
  get: async (): Promise<HealthData> => {
    const response = await api.get('/admin/health');
    return response.data;
  },
};

// Updates API
export const updatesApi = {
  // Get available updates (admin endpoint)
  get: async (): Promise<AvailableUpdates> => {
    const response = await api.get('/admin/updates');
    return response.data;
  },

  // Perform an update (admin endpoint)
  update: async (type: 'plugin' | 'theme' | 'core', slug: string): Promise<UpdateResult> => {
    const response = await api.post('/admin/update', { type, slug });
    return response.data;
  },
};

// Verify API (used by manager site)
export const verifyApi = {
  // Verify connection
  verify: async (): Promise<VerifyResponse> => {
    const response = await api.get('/verify');
    return response.data;
  },
};

// Analytics API (if Peanut Suite is installed)
export const analyticsApi = {
  // Get analytics data
  get: async (days: number = 30): Promise<AnalyticsData> => {
    const response = await api.get('/analytics', { params: { days } });
    return response.data;
  },
};

// Dashboard API (combined data for dashboard)
export const dashboardApi = {
  // Get dashboard data
  get: async (): Promise<{
    connection: {
      connected: boolean;
      manager_url: string | null;
      last_sync: string | null;
    };
    health_summary: {
      status: 'healthy' | 'warning' | 'critical';
      issues: string[];
    };
    updates: {
      plugins: number;
      themes: number;
      core: boolean;
    };
    peanut_suite: {
      installed: boolean;
      version: string | null;
    } | null;
  }> => {
    const response = await api.get('/dashboard');
    return response.data;
  },
};

// Error Log API
export const errorLogApi = {
  // Get error log entries
  get: async (limit: number = 50, offset: number = 0, level?: ErrorLevel): Promise<ErrorLogData> => {
    const params: Record<string, string | number> = { limit, offset };
    if (level) params.level = level;
    const response = await api.get('/errors', { params });
    return response.data;
  },

  // Get error counts
  getCounts: async (): Promise<ErrorCountsData> => {
    const response = await api.get('/errors/counts');
    return response.data;
  },

  // Clear error log
  clear: async (): Promise<{ success: boolean; message: string }> => {
    const response = await api.post('/errors/clear');
    return response.data;
  },

  // Export error log as CSV
  export: async (): Promise<{ csv: string; filename: string }> => {
    const response = await api.get('/errors/export');
    return response.data;
  },

  // Update error logging settings
  updateSettings: async (enabled: boolean): Promise<{ logging_enabled: boolean }> => {
    const response = await api.put('/errors/settings', { enabled });
    return response.data;
  },
};
