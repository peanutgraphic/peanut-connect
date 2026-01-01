import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import Badge, { StatusBadge } from './Badge';

describe('Badge', () => {
  it('renders children content', () => {
    render(<Badge>Badge Text</Badge>);

    expect(screen.getByText('Badge Text')).toBeInTheDocument();
  });

  it('renders as a span element', () => {
    render(<Badge>Content</Badge>);

    expect(screen.getByText('Content').tagName).toBe('SPAN');
  });

  // Variant tests
  it('applies default variant styles', () => {
    render(<Badge>Default</Badge>);

    const badge = screen.getByText('Default');
    expect(badge).toHaveClass('bg-slate-100');
    expect(badge).toHaveClass('text-slate-700');
  });

  it('applies primary variant styles', () => {
    render(<Badge variant="primary">Primary</Badge>);

    const badge = screen.getByText('Primary');
    expect(badge).toHaveClass('bg-primary-100');
    expect(badge).toHaveClass('text-primary-700');
  });

  it('applies success variant styles', () => {
    render(<Badge variant="success">Success</Badge>);

    const badge = screen.getByText('Success');
    expect(badge).toHaveClass('bg-green-100');
    expect(badge).toHaveClass('text-green-700');
  });

  it('applies warning variant styles', () => {
    render(<Badge variant="warning">Warning</Badge>);

    const badge = screen.getByText('Warning');
    expect(badge).toHaveClass('bg-amber-100');
    expect(badge).toHaveClass('text-amber-700');
  });

  it('applies danger variant styles', () => {
    render(<Badge variant="danger">Danger</Badge>);

    const badge = screen.getByText('Danger');
    expect(badge).toHaveClass('bg-red-100');
    expect(badge).toHaveClass('text-red-700');
  });

  it('applies info variant styles', () => {
    render(<Badge variant="info">Info</Badge>);

    const badge = screen.getByText('Info');
    expect(badge).toHaveClass('bg-blue-100');
    expect(badge).toHaveClass('text-blue-700');
  });

  // Size tests
  it('applies medium size styles by default', () => {
    render(<Badge>Medium</Badge>);

    const badge = screen.getByText('Medium');
    expect(badge).toHaveClass('px-2.5');
    expect(badge).toHaveClass('py-1');
  });

  it('applies small size styles', () => {
    render(<Badge size="sm">Small</Badge>);

    const badge = screen.getByText('Small');
    expect(badge).toHaveClass('px-2');
    expect(badge).toHaveClass('py-0.5');
  });

  // Common styles
  it('has rounded-full class', () => {
    render(<Badge>Rounded</Badge>);

    expect(screen.getByText('Rounded')).toHaveClass('rounded-full');
  });

  it('has font-medium class', () => {
    render(<Badge>Font Medium</Badge>);

    expect(screen.getByText('Font Medium')).toHaveClass('font-medium');
  });

  // Custom className
  it('applies custom className', () => {
    render(<Badge className="custom-badge-class">Custom</Badge>);

    expect(screen.getByText('Custom')).toHaveClass('custom-badge-class');
  });
});

describe('StatusBadge', () => {
  // Connection statuses
  it('renders connected status', () => {
    render(<StatusBadge status="connected" />);

    expect(screen.getByText('Connected')).toBeInTheDocument();
    expect(screen.getByText('Connected')).toHaveClass('bg-green-100');
  });

  it('renders disconnected status', () => {
    render(<StatusBadge status="disconnected" />);

    expect(screen.getByText('Disconnected')).toBeInTheDocument();
    expect(screen.getByText('Disconnected')).toHaveClass('bg-red-100');
  });

  it('renders pending status', () => {
    render(<StatusBadge status="pending" />);

    expect(screen.getByText('Pending')).toBeInTheDocument();
    expect(screen.getByText('Pending')).toHaveClass('bg-amber-100');
  });

  // Health statuses
  it('renders healthy status', () => {
    render(<StatusBadge status="healthy" />);

    expect(screen.getByText('Healthy')).toBeInTheDocument();
    expect(screen.getByText('Healthy')).toHaveClass('bg-green-100');
  });

  it('renders warning status', () => {
    render(<StatusBadge status="warning" />);

    expect(screen.getByText('Warning')).toBeInTheDocument();
    expect(screen.getByText('Warning')).toHaveClass('bg-amber-100');
  });

  it('renders critical status', () => {
    render(<StatusBadge status="critical" />);

    expect(screen.getByText('Critical')).toBeInTheDocument();
    expect(screen.getByText('Critical')).toHaveClass('bg-red-100');
  });

  // Update statuses
  it('renders uptodate status', () => {
    render(<StatusBadge status="uptodate" />);

    expect(screen.getByText('Up to Date')).toBeInTheDocument();
    expect(screen.getByText('Up to Date')).toHaveClass('bg-green-100');
  });

  it('renders outdated status', () => {
    render(<StatusBadge status="outdated" />);

    expect(screen.getByText('Update Available')).toBeInTheDocument();
    expect(screen.getByText('Update Available')).toHaveClass('bg-amber-100');
  });

  // Permission statuses
  it('renders allowed status', () => {
    render(<StatusBadge status="allowed" />);

    expect(screen.getByText('Allowed')).toBeInTheDocument();
    expect(screen.getByText('Allowed')).toHaveClass('bg-green-100');
  });

  it('renders denied status', () => {
    render(<StatusBadge status="denied" />);

    expect(screen.getByText('Denied')).toBeInTheDocument();
    expect(screen.getByText('Denied')).toHaveClass('bg-red-100');
  });

  // Case insensitivity
  it('handles case insensitive status', () => {
    render(<StatusBadge status="CONNECTED" />);

    expect(screen.getByText('Connected')).toBeInTheDocument();
  });

  // Unknown status
  it('renders unknown status as-is with default variant', () => {
    render(<StatusBadge status="unknown-status" />);

    expect(screen.getByText('unknown-status')).toBeInTheDocument();
    expect(screen.getByText('unknown-status')).toHaveClass('bg-slate-100');
  });

  // Custom className
  it('applies custom className', () => {
    render(<StatusBadge status="connected" className="custom-status-class" />);

    expect(screen.getByText('Connected')).toHaveClass('custom-status-class');
  });
});
