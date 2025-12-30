import { clsx } from 'clsx';
import type { ReactNode } from 'react';

interface BadgeProps {
  children: ReactNode;
  variant?: 'default' | 'primary' | 'success' | 'warning' | 'danger' | 'info';
  size?: 'sm' | 'md';
  className?: string;
}

export default function Badge({
  children,
  variant = 'default',
  size = 'md',
  className,
}: BadgeProps) {
  const variants = {
    default: 'bg-slate-100 text-slate-700',
    primary: 'bg-primary-100 text-primary-700',
    success: 'bg-green-100 text-green-700',
    warning: 'bg-amber-100 text-amber-700',
    danger: 'bg-red-100 text-red-700',
    info: 'bg-blue-100 text-blue-700',
  };

  const sizes = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-xs',
  };

  return (
    <span
      className={clsx(
        'inline-flex items-center font-medium rounded-full',
        variants[variant],
        sizes[size],
        className
      )}
    >
      {children}
    </span>
  );
}

// Status badge for common status values
interface StatusBadgeProps {
  status: string;
  className?: string;
}

export function StatusBadge({ status, className }: StatusBadgeProps) {
  const statusConfig: Record<string, { variant: BadgeProps['variant']; label: string }> = {
    // Connection statuses
    connected: { variant: 'success', label: 'Connected' },
    disconnected: { variant: 'danger', label: 'Disconnected' },
    pending: { variant: 'warning', label: 'Pending' },

    // Health statuses
    healthy: { variant: 'success', label: 'Healthy' },
    warning: { variant: 'warning', label: 'Warning' },
    critical: { variant: 'danger', label: 'Critical' },

    // Update statuses
    uptodate: { variant: 'success', label: 'Up to Date' },
    outdated: { variant: 'warning', label: 'Update Available' },

    // Permission statuses
    allowed: { variant: 'success', label: 'Allowed' },
    denied: { variant: 'danger', label: 'Denied' },
  };

  const config = statusConfig[status.toLowerCase()] || {
    variant: 'default' as const,
    label: status,
  };

  return (
    <Badge variant={config.variant} className={className}>
      {config.label}
    </Badge>
  );
}
