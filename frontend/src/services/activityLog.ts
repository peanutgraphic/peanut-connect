import type { ActivityLogEntry, ActivityType, ActivityStatus } from '@/types';

const STORAGE_KEY = 'peanut_connect_activity_log';
const MAX_ENTRIES = 100;

// Get all activity entries
export function getActivityLog(): ActivityLogEntry[] {
  try {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (!stored) return [];
    return JSON.parse(stored);
  } catch {
    return [];
  }
}

// Add a new activity entry
export function addActivityEntry(
  type: ActivityType,
  status: ActivityStatus,
  title: string,
  description: string,
  metadata?: Record<string, unknown>
): ActivityLogEntry {
  const entry: ActivityLogEntry = {
    id: `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
    type,
    status,
    title,
    description,
    timestamp: new Date().toISOString(),
    metadata,
  };

  const entries = getActivityLog();
  entries.unshift(entry);

  // Keep only the last MAX_ENTRIES
  const trimmed = entries.slice(0, MAX_ENTRIES);

  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(trimmed));
  } catch {
    // Storage full or unavailable, continue silently
  }

  return entry;
}

// Clear all activity entries
export function clearActivityLog(): void {
  localStorage.removeItem(STORAGE_KEY);
}

// Activity log helper functions for common events
export const activityLogger = {
  healthCheck: (status: 'healthy' | 'warning' | 'critical', issues: string[] = []) => {
    const statusMap: Record<string, ActivityStatus> = {
      healthy: 'success',
      warning: 'warning',
      critical: 'error',
    };
    addActivityEntry(
      'health_check',
      statusMap[status],
      'Health Check Completed',
      status === 'healthy'
        ? 'All systems are running normally.'
        : `Found ${issues.length} issue${issues.length !== 1 ? 's' : ''}: ${issues.slice(0, 3).join(', ')}${issues.length > 3 ? '...' : ''}`,
      { status, issues }
    );
  },

  updateInstalled: (type: 'plugin' | 'theme' | 'core', name: string, version: string) => {
    addActivityEntry(
      'update_installed',
      'success',
      `${type.charAt(0).toUpperCase() + type.slice(1)} Updated`,
      `${name} was updated to version ${version}.`,
      { type, name, version }
    );
  },

  updateFailed: (type: 'plugin' | 'theme' | 'core', name: string, error: string) => {
    addActivityEntry(
      'update_failed',
      'error',
      `${type.charAt(0).toUpperCase() + type.slice(1)} Update Failed`,
      `Failed to update ${name}: ${error}`,
      { type, name, error }
    );
  },

  connectionEstablished: (managerUrl: string) => {
    addActivityEntry(
      'connection_established',
      'success',
      'Connected to Manager',
      `Successfully connected to ${managerUrl}`,
      { managerUrl }
    );
  },

  connectionLost: (reason?: string) => {
    addActivityEntry(
      'connection_lost',
      'warning',
      'Connection Lost',
      reason || 'Lost connection to the manager site.',
      { reason }
    );
  },

  keyRegenerated: () => {
    addActivityEntry(
      'key_regenerated',
      'info',
      'Site Key Regenerated',
      'A new site key was generated. Update your manager with the new key.',
      {}
    );
  },

  permissionChanged: (permission: string, enabled: boolean) => {
    addActivityEntry(
      'permission_changed',
      'info',
      'Permission Updated',
      `${permission} was ${enabled ? 'enabled' : 'disabled'}.`,
      { permission, enabled }
    );
  },
};

export default activityLogger;
