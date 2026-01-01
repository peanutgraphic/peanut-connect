import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { Alert, SecurityAlert, Recommendation } from './Alert';
import { Info } from 'lucide-react';

describe('Alert', () => {
  it('renders children content', () => {
    render(<Alert>This is an alert message</Alert>);

    expect(screen.getByText('This is an alert message')).toBeInTheDocument();
  });

  it('has role="alert"', () => {
    render(<Alert>Alert content</Alert>);

    expect(screen.getByRole('alert')).toBeInTheDocument();
  });

  it('renders title when provided', () => {
    render(<Alert title="Alert Title">Alert content</Alert>);

    expect(screen.getByText('Alert Title')).toBeInTheDocument();
  });

  // Variant tests
  it('applies info variant styles by default', () => {
    render(<Alert>Info alert</Alert>);

    const alert = screen.getByRole('alert');
    expect(alert).toHaveClass('bg-blue-50');
    expect(alert).toHaveClass('border-blue-300');
  });

  it('applies warning variant styles', () => {
    render(<Alert variant="warning">Warning alert</Alert>);

    const alert = screen.getByRole('alert');
    expect(alert).toHaveClass('bg-amber-50');
    expect(alert).toHaveClass('border-amber-300');
  });

  it('applies error variant styles', () => {
    render(<Alert variant="error">Error alert</Alert>);

    const alert = screen.getByRole('alert');
    expect(alert).toHaveClass('bg-red-50');
    expect(alert).toHaveClass('border-red-300');
  });

  it('applies success variant styles', () => {
    render(<Alert variant="success">Success alert</Alert>);

    const alert = screen.getByRole('alert');
    expect(alert).toHaveClass('bg-green-50');
    expect(alert).toHaveClass('border-green-300');
  });

  it('applies danger variant styles', () => {
    render(<Alert variant="danger">Danger alert</Alert>);

    const alert = screen.getByRole('alert');
    expect(alert).toHaveClass('bg-red-50');
    expect(alert).toHaveClass('border-red-400');
  });

  // Dismissible tests
  it('shows dismiss button when dismissible', () => {
    render(<Alert dismissible>Dismissible alert</Alert>);

    expect(screen.getByLabelText('Dismiss')).toBeInTheDocument();
  });

  it('does not show dismiss button by default', () => {
    render(<Alert>Non-dismissible alert</Alert>);

    expect(screen.queryByLabelText('Dismiss')).not.toBeInTheDocument();
  });

  it('hides alert when dismiss button is clicked', () => {
    render(<Alert dismissible>Dismissible alert</Alert>);

    fireEvent.click(screen.getByLabelText('Dismiss'));

    expect(screen.queryByRole('alert')).not.toBeInTheDocument();
  });

  it('calls onDismiss callback when dismissed', () => {
    const onDismiss = vi.fn();
    render(
      <Alert dismissible onDismiss={onDismiss}>
        Dismissible alert
      </Alert>
    );

    fireEvent.click(screen.getByLabelText('Dismiss'));

    expect(onDismiss).toHaveBeenCalledTimes(1);
  });

  // Custom icon tests
  it('renders custom icon when provided', () => {
    render(
      <Alert icon={<Info data-testid="custom-icon" />}>
        Alert with custom icon
      </Alert>
    );

    expect(screen.getByTestId('custom-icon')).toBeInTheDocument();
  });

  // Action tests
  it('renders action when provided', () => {
    render(
      <Alert action={<button>Take Action</button>}>
        Alert with action
      </Alert>
    );

    expect(screen.getByText('Take Action')).toBeInTheDocument();
  });

  // Custom className
  it('applies custom className', () => {
    render(<Alert className="custom-alert-class">Alert</Alert>);

    expect(screen.getByRole('alert')).toHaveClass('custom-alert-class');
  });
});

describe('SecurityAlert', () => {
  it('renders children content', () => {
    render(<SecurityAlert>Security warning message</SecurityAlert>);

    expect(screen.getByText('Security warning message')).toBeInTheDocument();
  });

  it('renders default title', () => {
    render(<SecurityAlert>Content</SecurityAlert>);

    expect(screen.getByText('Security Notice')).toBeInTheDocument();
  });

  it('renders custom title', () => {
    render(<SecurityAlert title="Custom Security Title">Content</SecurityAlert>);

    expect(screen.getByText('Custom Security Title')).toBeInTheDocument();
  });

  // Severity levels
  it('applies low severity styles', () => {
    const { container } = render(
      <SecurityAlert severity="low">Low severity</SecurityAlert>
    );

    expect(container.firstChild).toHaveClass('bg-slate-50');
    expect(container.firstChild).toHaveClass('border-slate-300');
    expect(screen.getByText('Low Risk')).toBeInTheDocument();
  });

  it('applies medium severity styles by default', () => {
    const { container } = render(
      <SecurityAlert>Medium severity</SecurityAlert>
    );

    expect(container.firstChild).toHaveClass('bg-amber-50');
    expect(container.firstChild).toHaveClass('border-amber-300');
    expect(screen.getByText('Medium Risk')).toBeInTheDocument();
  });

  it('applies high severity styles', () => {
    const { container } = render(
      <SecurityAlert severity="high">High severity</SecurityAlert>
    );

    expect(container.firstChild).toHaveClass('bg-orange-50');
    expect(container.firstChild).toHaveClass('border-orange-400');
    expect(screen.getByText('High Risk')).toBeInTheDocument();
  });

  it('applies critical severity styles', () => {
    const { container } = render(
      <SecurityAlert severity="critical">Critical severity</SecurityAlert>
    );

    expect(container.firstChild).toHaveClass('bg-red-50');
    expect(container.firstChild).toHaveClass('border-red-500');
    expect(screen.getByText('Critical')).toBeInTheDocument();
  });

  it('applies custom className', () => {
    const { container } = render(
      <SecurityAlert className="custom-security-class">Content</SecurityAlert>
    );

    expect(container.firstChild).toHaveClass('custom-security-class');
  });
});

describe('Recommendation', () => {
  it('renders title', () => {
    render(
      <Recommendation title="Recommendation Title">
        Recommendation content
      </Recommendation>
    );

    expect(screen.getByText('Recommendation Title')).toBeInTheDocument();
  });

  it('renders children content', () => {
    render(
      <Recommendation title="Title">
        Recommendation content
      </Recommendation>
    );

    expect(screen.getByText('Recommendation content')).toBeInTheDocument();
  });

  it('renders action button when provided', () => {
    const handleClick = vi.fn();
    render(
      <Recommendation
        title="Title"
        action={{ label: 'Learn More', onClick: handleClick }}
      >
        Content
      </Recommendation>
    );

    expect(screen.getByText(/Learn More/)).toBeInTheDocument();
  });

  it('calls action onClick when action button is clicked', () => {
    const handleClick = vi.fn();
    render(
      <Recommendation
        title="Title"
        action={{ label: 'Learn More', onClick: handleClick }}
      >
        Content
      </Recommendation>
    );

    fireEvent.click(screen.getByText(/Learn More/));

    expect(handleClick).toHaveBeenCalledTimes(1);
  });

  it('does not render action button when not provided', () => {
    render(
      <Recommendation title="Title">
        Content
      </Recommendation>
    );

    expect(screen.queryByText(/→/)).not.toBeInTheDocument();
  });
});
