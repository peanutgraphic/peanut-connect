import { clsx } from 'clsx';

interface SkeletonProps {
  className?: string;
  variant?: 'text' | 'circular' | 'rectangular' | 'rounded';
  width?: string | number;
  height?: string | number;
  animation?: 'pulse' | 'wave' | 'none';
}

export function Skeleton({
  className,
  variant = 'text',
  width,
  height,
  animation = 'pulse',
}: SkeletonProps) {
  const baseClasses = 'bg-slate-200 dark:bg-slate-700';

  const animationClasses = {
    pulse: 'animate-pulse',
    wave: 'animate-shimmer bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 dark:from-slate-700 dark:via-slate-600 dark:to-slate-700 bg-[length:200%_100%]',
    none: '',
  };

  const variantClasses = {
    text: 'rounded h-4',
    circular: 'rounded-full',
    rectangular: '',
    rounded: 'rounded-lg',
  };

  const style: React.CSSProperties = {
    width: width ?? (variant === 'text' ? '100%' : undefined),
    height: height ?? (variant === 'circular' ? width : undefined),
  };

  return (
    <div
      className={clsx(
        baseClasses,
        animationClasses[animation],
        variantClasses[variant],
        className
      )}
      style={style}
    />
  );
}

// Pre-built skeleton patterns
export function SkeletonText({ lines = 3, className }: { lines?: number; className?: string }) {
  return (
    <div className={clsx('space-y-2', className)}>
      {Array.from({ length: lines }).map((_, i) => (
        <Skeleton
          key={i}
          variant="text"
          width={i === lines - 1 ? '60%' : '100%'}
        />
      ))}
    </div>
  );
}

export function SkeletonCard({ className }: { className?: string }) {
  return (
    <div className={clsx('bg-white rounded-xl border border-slate-200 p-6', className)}>
      <div className="flex items-start justify-between mb-4">
        <div className="space-y-2 flex-1">
          <Skeleton variant="text" width="40%" height={20} />
          <Skeleton variant="text" width="60%" height={14} />
        </div>
        <Skeleton variant="rounded" width={40} height={40} />
      </div>
      <SkeletonText lines={2} />
    </div>
  );
}

export function SkeletonStatCard({ className }: { className?: string }) {
  return (
    <div className={clsx('bg-white rounded-xl border border-slate-200 p-6', className)}>
      <div className="flex items-start justify-between">
        <div className="space-y-2">
          <Skeleton variant="text" width={100} height={14} />
          <Skeleton variant="text" width={60} height={28} />
        </div>
        <Skeleton variant="rounded" width={40} height={40} />
      </div>
    </div>
  );
}

export function SkeletonTable({ rows = 5, cols = 4, className }: { rows?: number; cols?: number; className?: string }) {
  return (
    <div className={clsx('bg-white rounded-xl border border-slate-200 overflow-hidden', className)}>
      {/* Header */}
      <div className="flex gap-4 p-4 border-b border-slate-200 bg-slate-50">
        {Array.from({ length: cols }).map((_, i) => (
          <Skeleton key={i} variant="text" width={`${100 / cols}%`} height={16} />
        ))}
      </div>
      {/* Rows */}
      {Array.from({ length: rows }).map((_, rowIndex) => (
        <div key={rowIndex} className="flex gap-4 p-4 border-b border-slate-100 last:border-0">
          {Array.from({ length: cols }).map((_, colIndex) => (
            <Skeleton key={colIndex} variant="text" width={`${100 / cols}%`} height={14} />
          ))}
        </div>
      ))}
    </div>
  );
}

export function SkeletonList({ items = 5, className }: { items?: number; className?: string }) {
  return (
    <div className={clsx('space-y-3', className)}>
      {Array.from({ length: items }).map((_, i) => (
        <div key={i} className="flex items-center gap-3 p-3 bg-white rounded-lg border border-slate-200">
          <Skeleton variant="circular" width={40} height={40} />
          <div className="flex-1 space-y-2">
            <Skeleton variant="text" width="70%" height={16} />
            <Skeleton variant="text" width="40%" height={12} />
          </div>
          <Skeleton variant="rounded" width={80} height={32} />
        </div>
      ))}
    </div>
  );
}

// Dashboard skeleton
export function DashboardSkeleton() {
  return (
    <div className="space-y-6">
      {/* Stats row */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <SkeletonStatCard />
        <SkeletonStatCard />
        <SkeletonStatCard />
        <SkeletonStatCard />
      </div>
      {/* Main content */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <SkeletonCard />
        <SkeletonCard />
      </div>
    </div>
  );
}

// Health page skeleton
export function HealthSkeleton() {
  return (
    <div className="space-y-6">
      {/* Health score */}
      <div className="bg-white rounded-xl border border-slate-200 p-6">
        <div className="flex items-center gap-6">
          <Skeleton variant="circular" width={120} height={120} />
          <div className="flex-1 space-y-3">
            <Skeleton variant="text" width="30%" height={24} />
            <Skeleton variant="text" width="50%" height={16} />
            <div className="flex gap-2 mt-4">
              <Skeleton variant="rounded" width={80} height={24} />
              <Skeleton variant="rounded" width={80} height={24} />
              <Skeleton variant="rounded" width={80} height={24} />
            </div>
          </div>
        </div>
      </div>
      {/* Health cards */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <SkeletonCard />
        <SkeletonCard />
        <SkeletonCard />
        <SkeletonCard />
      </div>
    </div>
  );
}

// Updates page skeleton
export function UpdatesSkeleton() {
  return (
    <div className="space-y-6">
      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <SkeletonStatCard />
        <SkeletonStatCard />
        <SkeletonStatCard />
      </div>
      {/* Update lists */}
      <SkeletonCard />
      <SkeletonList items={4} />
    </div>
  );
}

// Settings page skeleton
export function SettingsSkeleton() {
  return (
    <div className="space-y-6">
      <SkeletonCard />
      <SkeletonCard />
      <div className="bg-white rounded-xl border border-slate-200 p-6">
        <Skeleton variant="text" width="30%" height={20} className="mb-4" />
        <div className="space-y-4">
          {Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div className="flex items-center gap-3">
                <Skeleton variant="rounded" width={32} height={32} />
                <div className="space-y-1">
                  <Skeleton variant="text" width={120} height={16} />
                  <Skeleton variant="text" width={200} height={12} />
                </div>
              </div>
              <Skeleton variant="rounded" width={44} height={24} />
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
