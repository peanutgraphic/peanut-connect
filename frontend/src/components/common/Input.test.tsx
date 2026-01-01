import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Input from './Input';
import { Search, Eye } from 'lucide-react';

describe('Input', () => {
  it('renders an input element', () => {
    render(<Input />);

    expect(screen.getByRole('textbox')).toBeInTheDocument();
  });

  it('applies placeholder text', () => {
    render(<Input placeholder="Enter text..." />);

    expect(screen.getByPlaceholderText('Enter text...')).toBeInTheDocument();
  });

  it('displays value', () => {
    render(<Input value="test value" onChange={() => {}} />);

    expect(screen.getByDisplayValue('test value')).toBeInTheDocument();
  });

  it('calls onChange when typing', () => {
    const handleChange = vi.fn();

    render(<Input onChange={handleChange} />);

    fireEvent.change(screen.getByRole('textbox'), { target: { value: 'hello' } });

    expect(handleChange).toHaveBeenCalled();
  });

  // Label tests
  it('renders label when provided', () => {
    render(<Input label="Email" />);

    expect(screen.getByText('Email')).toBeInTheDocument();
  });

  it('associates label with input', () => {
    render(<Input label="Username" name="username" />);

    const input = screen.getByRole('textbox');
    expect(input).toHaveAttribute('id', 'username');
    expect(screen.getByText('Username')).toHaveAttribute('for', 'username');
  });

  it('shows required indicator when required', () => {
    render(<Input label="Email" required />);

    expect(screen.getByText('*')).toBeInTheDocument();
    expect(screen.getByText('*')).toHaveClass('text-red-500');
  });

  it('does not show required indicator when not required', () => {
    render(<Input label="Email" />);

    expect(screen.queryByText('*')).not.toBeInTheDocument();
  });

  // Error tests
  it('displays error message', () => {
    render(<Input error="This field is required" />);

    expect(screen.getByText('This field is required')).toBeInTheDocument();
    expect(screen.getByText('This field is required')).toHaveClass('text-red-600');
  });

  it('applies error styles to input', () => {
    render(<Input error="Error" />);

    expect(screen.getByRole('textbox')).toHaveClass('border-red-300');
  });

  it('does not show hint when error is present', () => {
    render(<Input error="Error" hint="Helpful hint" />);

    expect(screen.getByText('Error')).toBeInTheDocument();
    expect(screen.queryByText('Helpful hint')).not.toBeInTheDocument();
  });

  // Hint tests
  it('displays hint text', () => {
    render(<Input hint="Enter your full name" />);

    expect(screen.getByText('Enter your full name')).toBeInTheDocument();
    expect(screen.getByText('Enter your full name')).toHaveClass('text-slate-500');
  });

  // Icon tests
  it('renders left icon', () => {
    render(<Input leftIcon={<Search data-testid="left-icon" />} />);

    expect(screen.getByTestId('left-icon')).toBeInTheDocument();
  });

  it('renders right icon', () => {
    render(<Input rightIcon={<Eye data-testid="right-icon" />} />);

    expect(screen.getByTestId('right-icon')).toBeInTheDocument();
  });

  it('renders both icons', () => {
    render(
      <Input
        leftIcon={<Search data-testid="left-icon" />}
        rightIcon={<Eye data-testid="right-icon" />}
      />
    );

    expect(screen.getByTestId('left-icon')).toBeInTheDocument();
    expect(screen.getByTestId('right-icon')).toBeInTheDocument();
  });

  // Disabled state
  it('is disabled when disabled prop is true', () => {
    render(<Input disabled />);

    expect(screen.getByRole('textbox')).toBeDisabled();
  });

  it('applies disabled styles', () => {
    render(<Input disabled />);

    expect(screen.getByRole('textbox')).toHaveClass('disabled:bg-slate-50');
    expect(screen.getByRole('textbox')).toHaveClass('disabled:cursor-not-allowed');
  });

  // Width tests
  it('applies full width by default', () => {
    const { container } = render(<Input />);

    expect(container.firstChild).toHaveClass('w-full');
    expect(screen.getByRole('textbox')).toHaveClass('w-full');
  });

  it('does not apply full width when fullWidth is false', () => {
    const { container } = render(<Input fullWidth={false} />);

    expect(container.firstChild).not.toHaveClass('w-full');
    expect(screen.getByRole('textbox')).not.toHaveClass('w-full');
  });

  // Custom className
  it('applies custom className to input', () => {
    render(<Input className="custom-input-class" />);

    expect(screen.getByRole('textbox')).toHaveClass('custom-input-class');
  });

  // Native props
  it('passes through native input props', () => {
    render(
      <Input
        type="email"
        name="email"
        autoComplete="email"
        maxLength={50}
      />
    );

    const input = screen.getByRole('textbox');
    expect(input).toHaveAttribute('type', 'email');
    expect(input).toHaveAttribute('name', 'email');
    expect(input).toHaveAttribute('autocomplete', 'email');
    expect(input).toHaveAttribute('maxlength', '50');
  });

  // Ref forwarding
  it('forwards ref to input element', () => {
    const ref = vi.fn();
    render(<Input ref={ref} />);

    expect(ref).toHaveBeenCalled();
    expect(ref.mock.calls[0][0]).toBeInstanceOf(HTMLInputElement);
  });

  // Focus behavior
  it('can be focused', () => {
    render(<Input />);

    const input = screen.getByRole('textbox');
    input.focus();

    expect(input).toHaveFocus();
  });

  it('has focus ring styles', () => {
    render(<Input />);

    expect(screen.getByRole('textbox')).toHaveClass('focus:ring-2');
    expect(screen.getByRole('textbox')).toHaveClass('focus:ring-primary-500');
  });
});
