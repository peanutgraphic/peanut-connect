import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Button from './Button';
import { Check } from 'lucide-react';

describe('Button', () => {
  it('renders children text', () => {
    render(<Button>Click me</Button>);

    expect(screen.getByRole('button')).toHaveTextContent('Click me');
  });

  it('applies primary variant styles by default', () => {
    render(<Button>Primary</Button>);

    const button = screen.getByRole('button');
    expect(button).toHaveClass('bg-primary-600');
  });

  it('applies secondary variant styles', () => {
    render(<Button variant="secondary">Secondary</Button>);

    const button = screen.getByRole('button');
    expect(button).toHaveClass('bg-slate-100');
  });

  it('applies danger variant styles', () => {
    render(<Button variant="danger">Delete</Button>);

    const button = screen.getByRole('button');
    expect(button).toHaveClass('bg-red-600');
  });

  it('applies success variant styles', () => {
    render(<Button variant="success">Save</Button>);

    const button = screen.getByRole('button');
    expect(button).toHaveClass('bg-green-600');
  });

  it('applies outline variant styles', () => {
    render(<Button variant="outline">Outline</Button>);

    const button = screen.getByRole('button');
    expect(button).toHaveClass('border');
    expect(button).toHaveClass('bg-white');
  });

  it('applies ghost variant styles', () => {
    render(<Button variant="ghost">Ghost</Button>);

    const button = screen.getByRole('button');
    expect(button).toHaveClass('text-slate-600');
  });

  it('applies small size styles', () => {
    render(<Button size="sm">Small</Button>);

    const button = screen.getByRole('button');
    expect(button).toHaveClass('px-3');
    expect(button).toHaveClass('py-1.5');
  });

  it('applies medium size styles by default', () => {
    render(<Button>Medium</Button>);

    const button = screen.getByRole('button');
    expect(button).toHaveClass('px-4');
    expect(button).toHaveClass('py-2');
  });

  it('applies large size styles', () => {
    render(<Button size="lg">Large</Button>);

    const button = screen.getByRole('button');
    expect(button).toHaveClass('px-5');
    expect(button).toHaveClass('py-2.5');
  });

  it('calls onClick handler when clicked', () => {
    const handleClick = vi.fn();
    render(<Button onClick={handleClick}>Click me</Button>);

    fireEvent.click(screen.getByRole('button'));

    expect(handleClick).toHaveBeenCalledTimes(1);
  });

  it('is disabled when disabled prop is true', () => {
    render(<Button disabled>Disabled</Button>);

    expect(screen.getByRole('button')).toBeDisabled();
  });

  it('is disabled when loading is true', () => {
    render(<Button loading>Loading</Button>);

    expect(screen.getByRole('button')).toBeDisabled();
  });

  it('shows loading spinner when loading', () => {
    render(<Button loading>Loading</Button>);

    const button = screen.getByRole('button');
    expect(button.querySelector('.animate-spin')).toBeInTheDocument();
  });

  it('does not call onClick when disabled', () => {
    const handleClick = vi.fn();
    render(<Button onClick={handleClick} disabled>Disabled</Button>);

    fireEvent.click(screen.getByRole('button'));

    expect(handleClick).not.toHaveBeenCalled();
  });

  it('does not call onClick when loading', () => {
    const handleClick = vi.fn();
    render(<Button onClick={handleClick} loading>Loading</Button>);

    fireEvent.click(screen.getByRole('button'));

    expect(handleClick).not.toHaveBeenCalled();
  });

  it('renders icon on the left by default', () => {
    render(<Button icon={<Check data-testid="icon" />}>With Icon</Button>);

    const button = screen.getByRole('button');
    const icon = screen.getByTestId('icon');

    // Icon should come before the text in DOM
    expect(icon.parentElement).toEqual(button.firstChild);
  });

  it('renders icon on the right when iconPosition is right', () => {
    render(
      <Button icon={<Check data-testid="icon" />} iconPosition="right">
        With Icon
      </Button>
    );

    const button = screen.getByRole('button');
    const icon = screen.getByTestId('icon');

    // Icon should come after the text in DOM
    expect(icon.parentElement).toEqual(button.lastChild);
  });

  it('hides icon when loading', () => {
    render(
      <Button icon={<Check data-testid="icon" />} loading>
        With Icon
      </Button>
    );

    expect(screen.queryByTestId('icon')).not.toBeInTheDocument();
  });

  it('applies custom className', () => {
    render(<Button className="custom-class">Custom</Button>);

    expect(screen.getByRole('button')).toHaveClass('custom-class');
  });

  it('forwards ref to button element', () => {
    const ref = vi.fn();
    render(<Button ref={ref}>With Ref</Button>);

    expect(ref).toHaveBeenCalled();
  });

  it('passes through native button props', () => {
    render(
      <Button type="submit" name="submit-button">
        Submit
      </Button>
    );

    const button = screen.getByRole('button');
    expect(button).toHaveAttribute('type', 'submit');
    expect(button).toHaveAttribute('name', 'submit-button');
  });
});
