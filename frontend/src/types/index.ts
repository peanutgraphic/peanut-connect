// API Response types
export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
}

// Connection status
export interface ConnectionStatus {
  connected: boolean;
  manager_url: string | null;
  last_sync: string | null;
  site_key: string | null;
}

// Permissions
export interface Permissions {
  health_check: boolean;
  list_updates: boolean;
  perform_updates: boolean;
  access_analytics: boolean;
}

// Settings
export interface Settings {
  connection: ConnectionStatus;
  permissions: Permissions;
  peanut_suite: PeanutSuiteInfo | null;
}

// Peanut Suite info
export interface PeanutSuiteInfo {
  installed: boolean;
  version: string;
  modules: string[];
}

// Health check data (matches PHP Peanut_Connect_Health::get_health_data)
export interface HealthData {
  wp_version: {
    version: string;
    latest_version: string;
    needs_update: boolean;
  };
  php_version: {
    version: string;
    recommended: boolean;
    minimum_met: boolean;
  };
  ssl: {
    enabled: boolean;
    valid: boolean;
    days_until_expiry: number | null;
    issuer: string | null;
  };
  plugins: {
    total: number;
    active: number;
    inactive: number;
    updates_available: number;
    needing_update: Array<{
      slug: string;
      file: string;
      name: string;
      version: string;
      new_version: string;
    }>;
  };
  themes: {
    total: number;
    active: string;
    active_version: string;
    updates_available: number;
    needing_update: Array<{
      slug: string;
      name: string;
      version: string;
      new_version: string;
    }>;
  };
  disk_space: {
    available: boolean;
    total?: number;
    total_formatted?: string;
    used?: number;
    used_formatted?: string;
    free?: number;
    free_formatted?: string;
    used_percent?: number;
  };
  database: {
    size: number;
    size_formatted: string;
    tables_count: number;
    prefix: string;
  };
  debug_mode: boolean;
  backup: {
    plugin_detected: string | null;
    last_backup: string | null;
    days_since_last: number | null;
  };
  file_permissions: {
    secure: boolean;
    checks: {
      wp_config?: {
        permissions: string;
        secure: boolean;
      };
      htaccess?: {
        permissions: string;
        secure: boolean;
      };
      wp_content?: {
        writable: boolean;
      };
    };
  };
  server: {
    software: string;
    php_sapi: string;
    max_upload_size: number;
    max_upload_size_formatted: string;
    memory_limit: string;
    max_execution_time: string;
    php_extensions: {
      curl: boolean;
      imagick: boolean;
      gd: boolean;
      zip: boolean;
      openssl: boolean;
    };
  };
  peanut_suite: PeanutSuiteInfo | null;
}

// Plugin update info
export interface PluginUpdate {
  slug: string;
  file: string;
  name: string;
  version: string;
  new_version: string;
  url?: string;
  package?: string;
}

// Theme update info
export interface ThemeUpdate {
  slug: string;
  name: string;
  version: string;
  new_version: string;
  url?: string;
  package?: string;
}

// Core update info
export interface CoreUpdate {
  current_version: string;
  new_version: string;
  locale?: string;
  package?: string;
}

// Available updates
export interface AvailableUpdates {
  plugins: PluginUpdate[];
  themes: ThemeUpdate[];
  core: CoreUpdate | null;
}

// Update result
export interface UpdateResult {
  success: boolean;
  message: string;
  type: 'plugin' | 'theme' | 'core';
  slug: string;
}

// Verify endpoint response
export interface VerifyResponse {
  site_name: string;
  site_url: string;
  wordpress_version: string;
  permissions: Permissions;
  peanut_suite: PeanutSuiteInfo | null;
  health_summary: {
    plugins_need_update: number;
    themes_need_update: number;
    core_needs_update: boolean;
  };
}

// Analytics data (from Peanut Suite)
export interface AnalyticsData {
  period: string;
  contacts: {
    total: number;
    new: number;
  };
  utm_clicks: number;
  link_clicks: number;
  form_submissions: number;
}

// Navigation item
export interface NavItem {
  name: string;
  href: string;
  icon: React.ComponentType<{ className?: string }>;
}

// Activity log types
export type ActivityType =
  | 'health_check'
  | 'update_installed'
  | 'update_failed'
  | 'connection_established'
  | 'connection_lost'
  | 'key_regenerated'
  | 'permission_changed'
  | 'settings_changed';

export type ActivityStatus = 'success' | 'warning' | 'error' | 'info';

export interface ActivityLogEntry {
  id: string;
  type: ActivityType;
  status: ActivityStatus;
  title: string;
  description: string;
  timestamp: string;
  metadata?: Record<string, unknown>;
}

// Error Log types
export type ErrorLevel = 'critical' | 'error' | 'warning' | 'notice';

export interface ErrorLogEntry {
  type: string;
  level: ErrorLevel;
  message: string;
  file: string;
  line: number;
  timestamp: string;
  url: string;
  user_id: number;
  php_version?: string;
  memory_usage?: number;
}

export interface ErrorCounts {
  critical: number;
  error: number;
  warning: number;
  notice: number;
  total: number;
}

export interface ErrorLogData {
  entries: ErrorLogEntry[];
  counts: ErrorCounts;
  logging_enabled: boolean;
}

export interface ErrorCountsData {
  all_time: ErrorCounts;
  last_24h: ErrorCounts;
  logging_enabled: boolean;
}
