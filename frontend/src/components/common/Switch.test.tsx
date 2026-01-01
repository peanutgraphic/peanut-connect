import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Switch from './Switch';

describe('Switch', () => {
  const defaultProps = {
    checked: false,
    onChange: vi.fn(),
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders a switch element', () => {
    render(<Switch {...defaultProps} />);

    expect(screen.getByRole('switch')).toBeInTheDocument();
  });

  it('has correct aria-checked when unchecked', () => {
    render(<Switch {...defaultProps} checked={false} />);

    expect(screen.getByRole('switch')).toHaveAttribute('aria-checked', 'false');
  });

  it('has correct aria-checked when checked', () => {
    render(<Switch {...defaultProps} checked={true} />);

    expect(screen.getByRole('switch')).toHaveAttribute('aria-checked', 'true');
  });

  it('calls onChange with true when clicked while unchecked', () => {
    const onChange = vi.fn();
    render(<Switch checked={false} onChange={onChange} />);

    fireEvent.click(screen.getByRole('switch'));

    expect(onChange).toHaveBeenCalledWith(true);
  });

  it('calls onChange with false when clicked while checked', () => {
    const onChange = vi.fn();
    render(<Switch checked={true} onChange={onChange} />);

    fireEvent.click(screen.getByRole('switch'));

    expect(onChange).toHaveBeenCalledWith(false);
  });

  // Visual state tests
  it('applies checked styles when checked', () => {
    render(<Switch {...defaultProps} checked={true} />);

    expect(screen.getByRole('switch')).toHaveClass('bg-primary-600');
  });

  it('applies unchecked styles when unchecked', () => {
    render(<Switch {...defaultProps} checked={false} />);

    expect(screen.getByRole('switch')).toHaveClass('bg-slate-200');
  });

  it('moves toggle thumb when checked', () => {
    render(<Switch {...defaultProps} checked={true} />);

    const thumb = screen.getByRole('switch').querySelector('span');
    expect(thumb).toHaveClass('translate-x-5');
  });

  it('keeps toggle thumb at start when unchecked', () => {
    render(<Switch {...defaultProps} checked={false} />);

    const thumb = screen.getByRole('switch').querySelector('span');
    expect(thumb).toHaveClass('translate-x-0');
  });

  // Label tests
  it('renders label when provided', () => {
    render(<Switch {...defaultProps} label="Enable notifications" />);

    expect(screen.getByText('Enable notifications')).toBeInTheDocument();
  });

  it('does not render label when not provided', () => {
    render(<Switch {...defaultProps} />);

    expect(screen.queryByText(/./)).toBeNull(); // No text content
  });

  it('renders description when provided', () => {
    render(<Switch {...defaultProps} description="Get updates about new features" />);

    expect(screen.getByText('Get updates about new features')).toBeInTheDocument();
  });

  it('renders both label and description', () => {
    render(
      <Switch
        {...defaultProps}
        label="Email notifications"
        description="Receive email updates"
      />
    );

    expect(screen.getByText('Email notifications')).toBeInTheDocument();
    expect(screen.getByText('Receive email updates')).toBeInTheDocument();
  });

  // Disabled state tests
  it('is disabled when disabled prop is true', () => {
    render(<Switch {...defaultProps} disabled />);

    expect(screen.getByRole('switch')).toBeDisabled();
  });

  it('does not call onChange when disabled', () => {
    const onChange = vi.fn();
    render(<Switch checked={false} onChange={onChange} disabled />);

    fireEvent.click(screen.getByRole('switch'));

    expect(onChange).not.toHaveBeenCalled();
  });

  it('applies disabled styles when disabled', () => {
    const { container } = render(<Switch {...defaultProps} disabled />);

    expect(container.querySelector('label')).toHaveClass('opacity-50');
    expect(container.querySelector('label')).toHaveClass('cursor-not-allowed');
  });

  it('applies cursor-not-allowed to switch when disabled', () => {
    render(<Switch {...defaultProps} disabled />);

    expect(screen.getByRole('switch')).toHaveClass('cursor-not-allowed');
  });

  // Custom className
  it('applies custom className', () => {
    const { container } = render(<Switch {...defaultProps} className="custom-switch-class" />);

    expect(container.querySelector('label')).toHaveClass('custom-switch-class');
  });

  // Accessibility
  it('has focus ring styles', () => {
    render(<Switch {...defaultProps} />);

    expect(screen.getByRole('switch')).toHaveClass('focus:ring-2');
    expect(screen.getByRole('switch')).toHaveClass('focus:ring-primary-500');
  });

  it('is a button element', () => {
    render(<Switch {...defaultProps} />);

    expect(screen.getByRole('switch').tagName).toBe('BUTTON');
  });

  it('has type="button" to prevent form submission', () => {
    render(<Switch {...defaultProps} />);

    expect(screen.getByRole('switch')).toHaveAttribute('type', 'button');
  });
});
