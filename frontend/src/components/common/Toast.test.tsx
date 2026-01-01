import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import { ToastProvider, useToast } from './Toast';

// Test component that uses the toast hook
function ToastTester() {
  const toast = useToast();

  return (
    <div>
      <button onClick={() => toast.success('Success message')}>
        Show Success
      </button>
      <button onClick={() => toast.error('Error message')}>
        Show Error
      </button>
      <button onClick={() => toast.warning('Warning message')}>
        Show Warning
      </button>
      <button onClick={() => toast.info('Info message')}>
        Show Info
      </button>
      <button onClick={() => toast.success('Long toast', 10000)}>
        Show Long Toast
      </button>
    </div>
  );
}

describe('ToastProvider', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('renders children', () => {
    render(
      <ToastProvider>
        <div>Child content</div>
      </ToastProvider>
    );

    expect(screen.getByText('Child content')).toBeInTheDocument();
  });

  it('shows success toast', async () => {
    render(
      <ToastProvider>
        <ToastTester />
      </ToastProvider>
    );

    fireEvent.click(screen.getByText('Show Success'));

    expect(screen.getByRole('alert')).toHaveTextContent('Success message');
    expect(screen.getByRole('alert')).toHaveClass('bg-green-50');
  });

  it('shows error toast', () => {
    render(
      <ToastProvider>
        <ToastTester />
      </ToastProvider>
    );

    fireEvent.click(screen.getByText('Show Error'));

    expect(screen.getByRole('alert')).toHaveTextContent('Error message');
    expect(screen.getByRole('alert')).toHaveClass('bg-red-50');
  });

  it('shows warning toast', () => {
    render(
      <ToastProvider>
        <ToastTester />
      </ToastProvider>
    );

    fireEvent.click(screen.getByText('Show Warning'));

    expect(screen.getByRole('alert')).toHaveTextContent('Warning message');
    expect(screen.getByRole('alert')).toHaveClass('bg-amber-50');
  });

  it('shows info toast', () => {
    render(
      <ToastProvider>
        <ToastTester />
      </ToastProvider>
    );

    fireEvent.click(screen.getByText('Show Info'));

    expect(screen.getByRole('alert')).toHaveTextContent('Info message');
    expect(screen.getByRole('alert')).toHaveClass('bg-blue-50');
  });

  it('auto-dismisses toast after default duration', async () => {
    render(
      <ToastProvider>
        <ToastTester />
      </ToastProvider>
    );

    fireEvent.click(screen.getByText('Show Success'));
    expect(screen.getByRole('alert')).toBeInTheDocument();

    // Fast-forward past the default 4000ms duration
    await act(async () => {
      vi.advanceTimersByTime(4100);
    });

    expect(screen.queryByRole('alert')).not.toBeInTheDocument();
  });

  it('removes toast when close button clicked', async () => {
    render(
      <ToastProvider>
        <ToastTester />
      </ToastProvider>
    );

    fireEvent.click(screen.getByText('Show Success'));
    expect(screen.getByRole('alert')).toBeInTheDocument();

    // Find and click the close button
    const closeButton = screen.getByRole('alert').querySelector('button');
    expect(closeButton).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(closeButton!);
    });

    expect(screen.queryByRole('alert')).not.toBeInTheDocument();
  });

  it('can show multiple toasts', () => {
    render(
      <ToastProvider>
        <ToastTester />
      </ToastProvider>
    );

    fireEvent.click(screen.getByText('Show Success'));
    fireEvent.click(screen.getByText('Show Error'));
    fireEvent.click(screen.getByText('Show Warning'));

    expect(screen.getAllByRole('alert')).toHaveLength(3);
  });

  it('respects custom duration', async () => {
    render(
      <ToastProvider>
        <ToastTester />
      </ToastProvider>
    );

    fireEvent.click(screen.getByText('Show Long Toast'));
    expect(screen.getByRole('alert')).toBeInTheDocument();

    // Should still be visible after default duration
    await act(async () => {
      vi.advanceTimersByTime(5000);
    });

    expect(screen.getByRole('alert')).toBeInTheDocument();

    // Should be gone after custom duration
    await act(async () => {
      vi.advanceTimersByTime(6000);
    });

    expect(screen.queryByRole('alert')).not.toBeInTheDocument();
  });
});

describe('useToast', () => {
  it('throws error when used outside ToastProvider', () => {
    // Suppress console.error for this test
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

    expect(() => {
      render(<ToastTester />);
    }).toThrow('useToast must be used within a ToastProvider');

    consoleSpy.mockRestore();
  });
});
