import { type ReactNode, type HTMLAttributes } from 'react';
import { clsx } from 'clsx';

interface CardProps extends HTMLAttributes<HTMLDivElement> {
  children: ReactNode;
  className?: string;
  padding?: 'none' | 'sm' | 'md' | 'lg';
}

export default function Card({ children, className, padding = 'md', ...props }: CardProps) {
  const paddingStyles = {
    none: '',
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8',
  };

  return (
    <div
      className={clsx(
        'bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden',
        paddingStyles[padding],
        className
      )}
      {...props}
    >
      {children}
    </div>
  );
}

interface CardHeaderProps {
  title: ReactNode;
  description?: string;
  action?: ReactNode;
  className?: string;
}

export function CardHeader({ title, description, action, className }: CardHeaderProps) {
  return (
    <div className={clsx('flex items-start justify-between mb-4', className)}>
      <div>
        <h3 className="text-lg font-semibold text-slate-900">{title}</h3>
        {description && (
          <p className="text-sm text-slate-500 mt-0.5">{description}</p>
        )}
      </div>
      {action && <div>{action}</div>}
    </div>
  );
}

interface StatCardProps {
  title: ReactNode;
  value: string | number;
  change?: {
    value: number;
    type: 'increase' | 'decrease' | 'neutral';
  };
  icon?: ReactNode;
  className?: string;
}

export function StatCard({ title, value, change, icon, className }: StatCardProps) {
  return (
    <Card className={className}>
      <div className="flex items-start justify-between">
        <div>
          <p className="text-sm font-medium text-slate-500">{title}</p>
          <p className="text-2xl font-bold text-slate-900 mt-1">{value}</p>
          {change && (
            <p
              className={clsx(
                'text-sm mt-1',
                change.type === 'increase' && 'text-green-600',
                change.type === 'decrease' && 'text-red-600',
                change.type === 'neutral' && 'text-slate-500'
              )}
            >
              {change.type === 'increase' && '+'}
              {change.type === 'decrease' && '-'}
              {Math.abs(change.value)}%
            </p>
          )}
        </div>
        {icon && (
          <div className="p-2 bg-primary-50 rounded-lg text-primary-600">
            {icon}
          </div>
        )}
      </div>
    </Card>
  );
}
