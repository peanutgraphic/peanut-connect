import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import {
  Skeleton,
  SkeletonText,
  SkeletonCard,
  SkeletonStatCard,
  SkeletonTable,
  SkeletonList,
  DashboardSkeleton,
  HealthSkeleton,
  UpdatesSkeleton,
  SettingsSkeleton,
} from './Skeleton';

describe('Skeleton', () => {
  it('renders a div element', () => {
    const { container } = render(<Skeleton />);

    expect(container.firstChild?.nodeName).toBe('DIV');
  });

  // Variant tests
  it('applies text variant styles by default', () => {
    const { container } = render(<Skeleton />);

    expect(container.firstChild).toHaveClass('rounded');
    expect(container.firstChild).toHaveClass('h-4');
  });

  it('applies circular variant styles', () => {
    const { container } = render(<Skeleton variant="circular" />);

    expect(container.firstChild).toHaveClass('rounded-full');
  });

  it('applies rounded variant styles', () => {
    const { container } = render(<Skeleton variant="rounded" />);

    expect(container.firstChild).toHaveClass('rounded-lg');
  });

  // Animation tests
  it('applies pulse animation by default', () => {
    const { container } = render(<Skeleton />);

    expect(container.firstChild).toHaveClass('animate-pulse');
  });

  it('applies wave animation', () => {
    const { container } = render(<Skeleton animation="wave" />);

    expect(container.firstChild).toHaveClass('animate-shimmer');
  });

  it('applies no animation when none', () => {
    const { container } = render(<Skeleton animation="none" />);

    expect(container.firstChild).not.toHaveClass('animate-pulse');
    expect(container.firstChild).not.toHaveClass('animate-shimmer');
  });

  // Size tests
  it('applies width style', () => {
    const { container } = render(<Skeleton width={100} />);

    expect(container.firstChild).toHaveStyle({ width: '100px' });
  });

  it('applies height style', () => {
    const { container } = render(<Skeleton height={50} />);

    expect(container.firstChild).toHaveStyle({ height: '50px' });
  });

  it('applies string width', () => {
    const { container } = render(<Skeleton width="80%" />);

    expect(container.firstChild).toHaveStyle({ width: '80%' });
  });

  it('applies 100% width for text variant by default', () => {
    const { container } = render(<Skeleton variant="text" />);

    expect(container.firstChild).toHaveStyle({ width: '100%' });
  });

  // Custom className
  it('applies custom className', () => {
    const { container } = render(<Skeleton className="custom-skeleton" />);

    expect(container.firstChild).toHaveClass('custom-skeleton');
  });

  // Base styles
  it('has base background color', () => {
    const { container } = render(<Skeleton />);

    expect(container.firstChild).toHaveClass('bg-slate-200');
  });
});

describe('SkeletonText', () => {
  it('renders 3 lines by default', () => {
    const { container } = render(<SkeletonText />);

    expect(container.querySelectorAll('.bg-slate-200').length).toBe(3);
  });

  it('renders specified number of lines', () => {
    const { container } = render(<SkeletonText lines={5} />);

    expect(container.querySelectorAll('.bg-slate-200').length).toBe(5);
  });

  it('renders last line shorter', () => {
    const { container } = render(<SkeletonText lines={3} />);

    const skeletons = container.querySelectorAll('.bg-slate-200');
    expect(skeletons[2]).toHaveStyle({ width: '60%' });
  });

  it('applies custom className', () => {
    const { container } = render(<SkeletonText className="custom-text-skeleton" />);

    expect(container.firstChild).toHaveClass('custom-text-skeleton');
  });
});

describe('SkeletonCard', () => {
  it('renders card structure', () => {
    const { container } = render(<SkeletonCard />);

    expect(container.querySelector('.bg-white')).toBeInTheDocument();
    expect(container.querySelector('.rounded-xl')).toBeInTheDocument();
    expect(container.querySelector('.border')).toBeInTheDocument();
  });

  it('contains skeleton elements', () => {
    const { container } = render(<SkeletonCard />);

    expect(container.querySelectorAll('.bg-slate-200').length).toBeGreaterThan(0);
  });

  it('applies custom className', () => {
    const { container } = render(<SkeletonCard className="custom-card-skeleton" />);

    expect(container.querySelector('.custom-card-skeleton')).toBeInTheDocument();
  });
});

describe('SkeletonStatCard', () => {
  it('renders stat card structure', () => {
    const { container } = render(<SkeletonStatCard />);

    expect(container.querySelector('.bg-white')).toBeInTheDocument();
    expect(container.querySelector('.rounded-xl')).toBeInTheDocument();
  });

  it('contains skeleton elements', () => {
    const { container } = render(<SkeletonStatCard />);

    expect(container.querySelectorAll('.bg-slate-200').length).toBeGreaterThan(0);
  });

  it('applies custom className', () => {
    const { container } = render(<SkeletonStatCard className="custom-stat-skeleton" />);

    expect(container.querySelector('.custom-stat-skeleton')).toBeInTheDocument();
  });
});

describe('SkeletonTable', () => {
  it('renders 5 rows by default', () => {
    const { container } = render(<SkeletonTable />);

    // Header + 5 rows
    const rows = container.querySelectorAll('.flex.gap-4.p-4');
    expect(rows.length).toBe(6); // header + 5 body rows
  });

  it('renders specified number of rows', () => {
    const { container } = render(<SkeletonTable rows={3} />);

    const rows = container.querySelectorAll('.flex.gap-4.p-4');
    expect(rows.length).toBe(4); // header + 3 body rows
  });

  it('renders 4 columns by default', () => {
    const { container } = render(<SkeletonTable />);

    const headerCells = container.querySelector('.bg-slate-50')?.querySelectorAll('.bg-slate-200');
    expect(headerCells?.length).toBe(4);
  });

  it('renders specified number of columns', () => {
    const { container } = render(<SkeletonTable cols={6} />);

    const headerCells = container.querySelector('.bg-slate-50')?.querySelectorAll('.bg-slate-200');
    expect(headerCells?.length).toBe(6);
  });

  it('applies custom className', () => {
    const { container } = render(<SkeletonTable className="custom-table-skeleton" />);

    expect(container.querySelector('.custom-table-skeleton')).toBeInTheDocument();
  });
});

describe('SkeletonList', () => {
  it('renders 5 items by default', () => {
    const { container } = render(<SkeletonList />);

    const items = container.querySelectorAll('.flex.items-center.gap-3');
    expect(items.length).toBe(5);
  });

  it('renders specified number of items', () => {
    const { container } = render(<SkeletonList items={3} />);

    const items = container.querySelectorAll('.flex.items-center.gap-3');
    expect(items.length).toBe(3);
  });

  it('each item has circular avatar skeleton', () => {
    const { container } = render(<SkeletonList items={1} />);

    expect(container.querySelector('.rounded-full')).toBeInTheDocument();
  });

  it('applies custom className', () => {
    const { container } = render(<SkeletonList className="custom-list-skeleton" />);

    expect(container.firstChild).toHaveClass('custom-list-skeleton');
  });
});

describe('DashboardSkeleton', () => {
  it('renders stats grid', () => {
    const { container } = render(<DashboardSkeleton />);

    expect(container.querySelector('.grid')).toBeInTheDocument();
  });

  it('renders 4 stat cards', () => {
    const { container } = render(<DashboardSkeleton />);

    // Look for the first grid with 4 columns
    const statsGrid = container.querySelector('.lg\\:grid-cols-4');
    expect(statsGrid).toBeInTheDocument();
  });

  it('renders main content cards', () => {
    const { container } = render(<DashboardSkeleton />);

    // Should have multiple card structures
    expect(container.querySelectorAll('.bg-white').length).toBeGreaterThan(2);
  });
});

describe('HealthSkeleton', () => {
  it('renders health score section', () => {
    const { container } = render(<HealthSkeleton />);

    // Should have circular skeleton for health score
    expect(container.querySelector('.rounded-full')).toBeInTheDocument();
  });

  it('renders health cards grid', () => {
    const { container } = render(<HealthSkeleton />);

    expect(container.querySelector('.lg\\:grid-cols-2')).toBeInTheDocument();
  });
});

describe('UpdatesSkeleton', () => {
  it('renders stats section', () => {
    const { container } = render(<UpdatesSkeleton />);

    expect(container.querySelector('.md\\:grid-cols-3')).toBeInTheDocument();
  });

  it('renders update list section', () => {
    const { container } = render(<UpdatesSkeleton />);

    // Should have list-like structures
    expect(container.querySelectorAll('.bg-white').length).toBeGreaterThan(0);
  });
});

describe('SettingsSkeleton', () => {
  it('renders multiple card sections', () => {
    const { container } = render(<SettingsSkeleton />);

    expect(container.querySelectorAll('.bg-white').length).toBeGreaterThan(0);
  });

  it('renders permission toggles skeleton', () => {
    const { container } = render(<SettingsSkeleton />);

    // Should have rounded toggles
    expect(container.querySelectorAll('.rounded-lg').length).toBeGreaterThan(0);
  });
});
