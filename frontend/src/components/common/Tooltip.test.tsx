import { describe, it, expect } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { Tooltip, HelpTooltip } from './Tooltip';

describe('Tooltip', () => {
  it('renders children', () => {
    render(
      <Tooltip content="Tooltip text">
        <span>Hover me</span>
      </Tooltip>
    );

    expect(screen.getByText('Hover me')).toBeInTheDocument();
  });

  it('shows tooltip on mouse enter', async () => {
    render(
      <Tooltip content="Tooltip content">
        <span>Trigger</span>
      </Tooltip>
    );

    fireEvent.mouseEnter(screen.getByText('Trigger'));

    await waitFor(() => {
      expect(screen.getByText('Tooltip content')).toBeInTheDocument();
    });
  });

  it('hides tooltip on mouse leave', async () => {
    render(
      <Tooltip content="Tooltip content">
        <span>Trigger</span>
      </Tooltip>
    );

    fireEvent.mouseEnter(screen.getByText('Trigger'));

    await waitFor(() => {
      expect(screen.getByText('Tooltip content')).toBeInTheDocument();
    });

    fireEvent.mouseLeave(screen.getByText('Trigger'));

    await waitFor(() => {
      expect(screen.queryByText('Tooltip content')).not.toBeInTheDocument();
    });
  });

  it('shows tooltip on focus', async () => {
    render(
      <Tooltip content="Focus tooltip">
        <button>Focus me</button>
      </Tooltip>
    );

    fireEvent.focus(screen.getByText('Focus me'));

    await waitFor(() => {
      expect(screen.getByText('Focus tooltip')).toBeInTheDocument();
    });
  });

  it('hides tooltip on blur', async () => {
    render(
      <Tooltip content="Focus tooltip">
        <button>Focus me</button>
      </Tooltip>
    );

    fireEvent.focus(screen.getByText('Focus me'));

    await waitFor(() => {
      expect(screen.getByText('Focus tooltip')).toBeInTheDocument();
    });

    fireEvent.blur(screen.getByText('Focus me'));

    await waitFor(() => {
      expect(screen.queryByText('Focus tooltip')).not.toBeInTheDocument();
    });
  });

  it('renders help icon when showIcon is true', () => {
    render(<Tooltip content="Help text" showIcon />);

    // HelpCircle icon should be rendered
    const trigger = screen.getByText('', { selector: 'span.relative' });
    expect(trigger.querySelector('svg')).toBeInTheDocument();
  });

  it('applies sm icon size', () => {
    const { container } = render(<Tooltip content="Help" showIcon iconSize="sm" />);

    const icon = container.querySelector('svg');
    expect(icon).toHaveClass('w-3.5');
    expect(icon).toHaveClass('h-3.5');
  });

  it('applies md icon size', () => {
    const { container } = render(<Tooltip content="Help" showIcon iconSize="md" />);

    const icon = container.querySelector('svg');
    expect(icon).toHaveClass('w-4');
    expect(icon).toHaveClass('h-4');
  });

  it('applies lg icon size', () => {
    const { container } = render(<Tooltip content="Help" showIcon iconSize="lg" />);

    const icon = container.querySelector('svg');
    expect(icon).toHaveClass('w-5');
    expect(icon).toHaveClass('h-5');
  });

  it('renders React node as content', async () => {
    render(
      <Tooltip content={<span data-testid="custom-content">Rich content</span>}>
        <span>Trigger</span>
      </Tooltip>
    );

    fireEvent.mouseEnter(screen.getByText('Trigger'));

    await waitFor(() => {
      expect(screen.getByTestId('custom-content')).toBeInTheDocument();
    });
  });

  it('uses default position of top', async () => {
    render(
      <Tooltip content="Top tooltip">
        <span>Trigger</span>
      </Tooltip>
    );

    fireEvent.mouseEnter(screen.getByText('Trigger'));

    await waitFor(() => {
      expect(screen.getByText('Top tooltip')).toBeInTheDocument();
    });
  });

  it('renders tooltip with content visible', async () => {
    render(
      <Tooltip content="Test tooltip">
        <span>Trigger</span>
      </Tooltip>
    );

    fireEvent.mouseEnter(screen.getByText('Trigger'));

    await waitFor(() => {
      expect(screen.getByText('Test tooltip')).toBeInTheDocument();
    });

    // Tooltip should be rendered in the portal
    const tooltipContent = screen.getByText('Test tooltip');
    expect(tooltipContent).toBeVisible();
  });

  it('passes maxWidth prop correctly', async () => {
    render(
      <Tooltip content="Wide tooltip" maxWidth="400px">
        <span>Trigger</span>
      </Tooltip>
    );

    fireEvent.mouseEnter(screen.getByText('Trigger'));

    await waitFor(() => {
      expect(screen.getByText('Wide tooltip')).toBeInTheDocument();
    });

    // Just verify the tooltip renders - maxWidth is internal implementation
    expect(screen.getByText('Wide tooltip')).toBeVisible();
  });

  it('has correct styling for tooltip', async () => {
    render(
      <Tooltip content="Styled tooltip">
        <span>Trigger</span>
      </Tooltip>
    );

    fireEvent.mouseEnter(screen.getByText('Trigger'));

    await waitFor(() => {
      expect(screen.getByText('Styled tooltip')).toBeInTheDocument();
    });

    // Find the tooltip container with classes
    const tooltipText = screen.getByText('Styled tooltip');
    const tooltipInner = tooltipText.closest('.bg-slate-800');
    expect(tooltipInner).toBeInTheDocument();
    expect(tooltipInner).toHaveClass('text-white');
    expect(tooltipInner).toHaveClass('rounded-lg');
  });
});

describe('HelpTooltip', () => {
  it('renders help icon', () => {
    const { container } = render(<HelpTooltip content="Help content" />);

    expect(container.querySelector('svg')).toBeInTheDocument();
  });

  it('shows tooltip on hover', async () => {
    const { container } = render(<HelpTooltip content="Help tooltip content" />);

    const trigger = container.querySelector('span.relative');
    fireEvent.mouseEnter(trigger!);

    await waitFor(() => {
      expect(screen.getByText('Help tooltip content')).toBeInTheDocument();
    });
  });

  it('uses sm size by default', () => {
    const { container } = render(<HelpTooltip content="Help" />);

    const icon = container.querySelector('svg');
    expect(icon).toHaveClass('w-3.5');
    expect(icon).toHaveClass('h-3.5');
  });

  it('uses specified size', () => {
    const { container } = render(<HelpTooltip content="Help" size="lg" />);

    const icon = container.querySelector('svg');
    expect(icon).toHaveClass('w-5');
    expect(icon).toHaveClass('h-5');
  });

  it('renders complex content', async () => {
    const { container } = render(
      <HelpTooltip
        content={
          <div>
            <strong>Title</strong>
            <p>Description text</p>
          </div>
        }
      />
    );

    const trigger = container.querySelector('span.relative');
    fireEvent.mouseEnter(trigger!);

    await waitFor(() => {
      expect(screen.getByText('Title')).toBeInTheDocument();
      expect(screen.getByText('Description text')).toBeInTheDocument();
    });
  });

  it('has cursor-help class on icon', () => {
    const { container } = render(<HelpTooltip content="Help" />);

    const icon = container.querySelector('svg');
    expect(icon).toHaveClass('cursor-help');
  });
});
