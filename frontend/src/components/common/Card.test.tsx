import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import Card, { CardHeader, StatCard } from './Card';
import { Activity } from 'lucide-react';

describe('Card', () => {
  it('renders children', () => {
    render(<Card>Card content</Card>);

    expect(screen.getByText('Card content')).toBeInTheDocument();
  });

  it('applies default medium padding', () => {
    render(<Card data-testid="card">Content</Card>);

    const card = screen.getByTestId('card');
    expect(card).toHaveClass('p-6');
  });

  it('applies small padding when specified', () => {
    render(<Card data-testid="card" padding="sm">Content</Card>);

    const card = screen.getByTestId('card');
    expect(card).toHaveClass('p-4');
  });

  it('applies large padding when specified', () => {
    render(<Card data-testid="card" padding="lg">Content</Card>);

    const card = screen.getByTestId('card');
    expect(card).toHaveClass('p-8');
  });

  it('removes padding when none specified', () => {
    render(<Card data-testid="card" padding="none">Content</Card>);

    const card = screen.getByTestId('card');
    expect(card).not.toHaveClass('p-4');
    expect(card).not.toHaveClass('p-6');
    expect(card).not.toHaveClass('p-8');
  });

  it('applies custom className', () => {
    render(<Card data-testid="card" className="custom-class">Content</Card>);

    const card = screen.getByTestId('card');
    expect(card).toHaveClass('custom-class');
  });

  it('has rounded corners', () => {
    render(<Card data-testid="card">Content</Card>);

    const card = screen.getByTestId('card');
    expect(card).toHaveClass('rounded-xl');
  });

  it('has border styling', () => {
    render(<Card data-testid="card">Content</Card>);

    const card = screen.getByTestId('card');
    expect(card).toHaveClass('border');
    expect(card).toHaveClass('border-slate-200');
  });

  it('passes through native div props', () => {
    render(<Card data-testid="test-card">Content</Card>);

    expect(screen.getByTestId('test-card')).toBeInTheDocument();
  });
});

describe('CardHeader', () => {
  it('renders title', () => {
    render(<CardHeader title="Card Title" />);

    expect(screen.getByRole('heading', { level: 3 })).toHaveTextContent('Card Title');
  });

  it('renders description when provided', () => {
    render(<CardHeader title="Title" description="Card description" />);

    expect(screen.getByText('Card description')).toBeInTheDocument();
  });

  it('does not render description when not provided', () => {
    const { container } = render(<CardHeader title="Title" />);

    expect(container.querySelector('p')).not.toBeInTheDocument();
  });

  it('renders action when provided', () => {
    render(
      <CardHeader
        title="Title"
        action={<button>Action</button>}
      />
    );

    expect(screen.getByRole('button', { name: 'Action' })).toBeInTheDocument();
  });

  it('applies custom className', () => {
    const { container } = render(
      <CardHeader title="Title" className="custom-header" />
    );

    expect(container.firstChild).toHaveClass('custom-header');
  });

  it('renders ReactNode as title', () => {
    render(
      <CardHeader title={<span data-testid="custom-title">Custom Title</span>} />
    );

    expect(screen.getByTestId('custom-title')).toBeInTheDocument();
  });
});

describe('StatCard', () => {
  it('renders title and value', () => {
    render(<StatCard title="Total Users" value={1234} />);

    expect(screen.getByText('Total Users')).toBeInTheDocument();
    expect(screen.getByText('1234')).toBeInTheDocument();
  });

  it('renders string value correctly', () => {
    render(<StatCard title="Status" value="Active" />);

    expect(screen.getByText('Active')).toBeInTheDocument();
  });

  it('renders increase change with plus sign and green color', () => {
    render(
      <StatCard
        title="Revenue"
        value="$1,000"
        change={{ value: 15, type: 'increase' }}
      />
    );

    const changeElement = screen.getByText('+15%');
    expect(changeElement).toBeInTheDocument();
    expect(changeElement).toHaveClass('text-green-600');
  });

  it('renders decrease change with minus sign and red color', () => {
    render(
      <StatCard
        title="Bounce Rate"
        value="25%"
        change={{ value: 5, type: 'decrease' }}
      />
    );

    const changeElement = screen.getByText('-5%');
    expect(changeElement).toBeInTheDocument();
    expect(changeElement).toHaveClass('text-red-600');
  });

  it('renders neutral change without sign and slate color', () => {
    render(
      <StatCard
        title="Visitors"
        value="500"
        change={{ value: 0, type: 'neutral' }}
      />
    );

    const changeElement = screen.getByText('0%');
    expect(changeElement).toBeInTheDocument();
    expect(changeElement).toHaveClass('text-slate-500');
  });

  it('renders icon when provided', () => {
    render(
      <StatCard
        title="Activity"
        value="100"
        icon={<Activity data-testid="stat-icon" />}
      />
    );

    expect(screen.getByTestId('stat-icon')).toBeInTheDocument();
  });

  it('applies custom className', () => {
    const { container } = render(
      <StatCard
        title="Test"
        value="123"
        className="custom-stat-card"
      />
    );

    expect(container.firstChild).toHaveClass('custom-stat-card');
  });

  it('does not render change when not provided', () => {
    render(<StatCard title="Simple" value="42" />);

    expect(screen.queryByText('%')).not.toBeInTheDocument();
  });

  it('renders ReactNode as title', () => {
    render(
      <StatCard
        title={<span data-testid="custom-stat-title">Custom</span>}
        value="100"
      />
    );

    expect(screen.getByTestId('custom-stat-title')).toBeInTheDocument();
  });
});
