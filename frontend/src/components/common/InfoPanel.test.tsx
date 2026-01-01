import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { Info } from 'lucide-react';
import {
  InfoPanel,
  InfoBadge,
  FeatureCard,
  CollapsibleBanner,
} from './InfoPanel';

describe('InfoPanel', () => {
  it('renders title', () => {
    render(
      <InfoPanel title="Information">
        <p>Content</p>
      </InfoPanel>
    );

    expect(screen.getByText('Information')).toBeInTheDocument();
  });

  it('renders children', () => {
    render(
      <InfoPanel title="Title">
        <p>Panel content here</p>
      </InfoPanel>
    );

    expect(screen.getByText('Panel content here')).toBeInTheDocument();
  });

  // Variant tests
  it('applies info variant styles by default', () => {
    const { container } = render(
      <InfoPanel title="Info">
        <p>Content</p>
      </InfoPanel>
    );

    expect(container.firstChild).toHaveClass('bg-blue-50');
    expect(container.firstChild).toHaveClass('border-blue-200');
  });

  it('applies tip variant styles', () => {
    const { container } = render(
      <InfoPanel variant="tip" title="Tip">
        <p>Content</p>
      </InfoPanel>
    );

    expect(container.firstChild).toHaveClass('bg-amber-50');
    expect(container.firstChild).toHaveClass('border-amber-200');
  });

  it('applies guide variant styles', () => {
    const { container } = render(
      <InfoPanel variant="guide" title="Guide">
        <p>Content</p>
      </InfoPanel>
    );

    expect(container.firstChild).toHaveClass('bg-purple-50');
    expect(container.firstChild).toHaveClass('border-purple-200');
  });

  it('applies warning variant styles', () => {
    const { container } = render(
      <InfoPanel variant="warning" title="Warning">
        <p>Content</p>
      </InfoPanel>
    );

    expect(container.firstChild).toHaveClass('bg-orange-50');
    expect(container.firstChild).toHaveClass('border-orange-200');
  });

  it('applies success variant styles', () => {
    const { container } = render(
      <InfoPanel variant="success" title="Success">
        <p>Content</p>
      </InfoPanel>
    );

    expect(container.firstChild).toHaveClass('bg-green-50');
    expect(container.firstChild).toHaveClass('border-green-200');
  });

  // Collapsible tests
  it('is not collapsible by default', () => {
    render(
      <InfoPanel title="Title">
        <p>Always visible</p>
      </InfoPanel>
    );

    expect(screen.getByText('Always visible')).toBeInTheDocument();
  });

  it('shows content by default when collapsible', () => {
    render(
      <InfoPanel title="Title" collapsible>
        <p>Content</p>
      </InfoPanel>
    );

    expect(screen.getByText('Content')).toBeInTheDocument();
  });

  it('hides content when defaultOpen is false', () => {
    render(
      <InfoPanel title="Title" collapsible defaultOpen={false}>
        <p>Hidden content</p>
      </InfoPanel>
    );

    expect(screen.queryByText('Hidden content')).not.toBeInTheDocument();
  });

  it('toggles content when clicked in collapsible mode', () => {
    render(
      <InfoPanel title="Clickable Title" collapsible>
        <p>Toggleable content</p>
      </InfoPanel>
    );

    expect(screen.getByText('Toggleable content')).toBeInTheDocument();

    fireEvent.click(screen.getByText('Clickable Title'));

    expect(screen.queryByText('Toggleable content')).not.toBeInTheDocument();
  });

  // Learn more link
  it('renders learn more link when URL is provided', () => {
    render(
      <InfoPanel title="Title" learnMoreUrl="https://example.com">
        <p>Content</p>
      </InfoPanel>
    );

    const link = screen.getByText('Learn more');
    expect(link).toBeInTheDocument();
    expect(link).toHaveAttribute('href', 'https://example.com');
  });

  it('opens learn more in new tab', () => {
    render(
      <InfoPanel title="Title" learnMoreUrl="https://example.com">
        <p>Content</p>
      </InfoPanel>
    );

    const link = screen.getByText('Learn more');
    expect(link).toHaveAttribute('target', '_blank');
    expect(link).toHaveAttribute('rel', 'noopener noreferrer');
  });

  // Custom className
  it('applies custom className', () => {
    const { container } = render(
      <InfoPanel title="Title" className="custom-class">
        <p>Content</p>
      </InfoPanel>
    );

    expect(container.firstChild).toHaveClass('custom-class');
  });
});

describe('InfoBadge', () => {
  it('renders label', () => {
    render(<InfoBadge label="New" />);

    expect(screen.getByText('New')).toBeInTheDocument();
  });

  it('applies default variant styles', () => {
    render(<InfoBadge label="Default" />);

    const badge = screen.getByText('Default');
    expect(badge).toHaveClass('bg-slate-100');
    expect(badge).toHaveClass('text-slate-600');
  });

  it('applies warning variant styles', () => {
    render(<InfoBadge label="Warning" variant="warning" />);

    const badge = screen.getByText('Warning');
    expect(badge).toHaveClass('bg-amber-100');
    expect(badge).toHaveClass('text-amber-700');
  });

  it('applies success variant styles', () => {
    render(<InfoBadge label="Success" variant="success" />);

    const badge = screen.getByText('Success');
    expect(badge).toHaveClass('bg-green-100');
    expect(badge).toHaveClass('text-green-700');
  });

  it('applies danger variant styles', () => {
    render(<InfoBadge label="Danger" variant="danger" />);

    const badge = screen.getByText('Danger');
    expect(badge).toHaveClass('bg-red-100');
    expect(badge).toHaveClass('text-red-700');
  });

  it('renders with tooltip', () => {
    render(<InfoBadge label="Info" tooltip="Additional info" />);

    expect(screen.getByText('Info')).toHaveAttribute('title', 'Additional info');
  });
});

describe('FeatureCard', () => {
  it('renders title', () => {
    render(
      <FeatureCard
        icon={<Info data-testid="icon" />}
        title="Feature Title"
        description="Feature description"
      />
    );

    expect(screen.getByText('Feature Title')).toBeInTheDocument();
  });

  it('renders description', () => {
    render(
      <FeatureCard
        icon={<Info />}
        title="Title"
        description="This is the description"
      />
    );

    expect(screen.getByText('This is the description')).toBeInTheDocument();
  });

  it('renders icon', () => {
    render(
      <FeatureCard
        icon={<Info data-testid="feature-icon" />}
        title="Title"
        description="Description"
      />
    );

    expect(screen.getByTestId('feature-icon')).toBeInTheDocument();
  });

  it('renders use cases when provided', () => {
    render(
      <FeatureCard
        icon={<Info />}
        title="Title"
        description="Description"
        useCases={['Use case 1', 'Use case 2']}
      />
    );

    expect(screen.getByText('Use Cases')).toBeInTheDocument();
    expect(screen.getByText('Use case 1')).toBeInTheDocument();
    expect(screen.getByText('Use case 2')).toBeInTheDocument();
  });

  it('does not render use cases section when not provided', () => {
    render(
      <FeatureCard
        icon={<Info />}
        title="Title"
        description="Description"
      />
    );

    expect(screen.queryByText('Use Cases')).not.toBeInTheDocument();
  });

  it('does not render use cases section when empty array', () => {
    render(
      <FeatureCard
        icon={<Info />}
        title="Title"
        description="Description"
        useCases={[]}
      />
    );

    expect(screen.queryByText('Use Cases')).not.toBeInTheDocument();
  });

  it('has card styling', () => {
    const { container } = render(
      <FeatureCard
        icon={<Info />}
        title="Title"
        description="Description"
      />
    );

    expect(container.firstChild).toHaveClass('bg-white');
    expect(container.firstChild).toHaveClass('rounded-lg');
    expect(container.firstChild).toHaveClass('border');
  });
});

describe('CollapsibleBanner', () => {
  it('renders title', () => {
    render(
      <CollapsibleBanner title="Banner Title">
        <p>Content</p>
      </CollapsibleBanner>
    );

    expect(screen.getByText('Banner Title')).toBeInTheDocument();
  });

  it('renders children by default', () => {
    render(
      <CollapsibleBanner title="Title">
        <p>Banner content</p>
      </CollapsibleBanner>
    );

    expect(screen.getByText('Banner content')).toBeInTheDocument();
  });

  it('hides content when defaultOpen is false', () => {
    render(
      <CollapsibleBanner title="Title" defaultOpen={false}>
        <p>Hidden content</p>
      </CollapsibleBanner>
    );

    expect(screen.queryByText('Hidden content')).not.toBeInTheDocument();
  });

  it('toggles content on click', () => {
    render(
      <CollapsibleBanner title="Clickable">
        <p>Toggleable</p>
      </CollapsibleBanner>
    );

    expect(screen.getByText('Toggleable')).toBeInTheDocument();

    fireEvent.click(screen.getByText('Clickable'));

    expect(screen.queryByText('Toggleable')).not.toBeInTheDocument();
  });

  // Variant tests
  it('applies amber variant by default', () => {
    const { container } = render(
      <CollapsibleBanner title="Title">
        <p>Content</p>
      </CollapsibleBanner>
    );

    expect(container.firstChild).toHaveClass('bg-amber-50');
    expect(container.firstChild).toHaveClass('border-l-amber-400');
  });

  it('applies info variant', () => {
    const { container } = render(
      <CollapsibleBanner variant="info" title="Title">
        <p>Content</p>
      </CollapsibleBanner>
    );

    expect(container.firstChild).toHaveClass('bg-blue-50');
    expect(container.firstChild).toHaveClass('border-l-blue-400');
  });

  it('applies warning variant', () => {
    const { container } = render(
      <CollapsibleBanner variant="warning" title="Title">
        <p>Content</p>
      </CollapsibleBanner>
    );

    expect(container.firstChild).toHaveClass('bg-orange-50');
    expect(container.firstChild).toHaveClass('border-l-orange-400');
  });

  it('applies success variant', () => {
    const { container } = render(
      <CollapsibleBanner variant="success" title="Title">
        <p>Content</p>
      </CollapsibleBanner>
    );

    expect(container.firstChild).toHaveClass('bg-green-50');
    expect(container.firstChild).toHaveClass('border-l-green-400');
  });

  // Icon
  it('renders custom icon', () => {
    render(
      <CollapsibleBanner title="Title" icon={<Info data-testid="custom-icon" />}>
        <p>Content</p>
      </CollapsibleBanner>
    );

    expect(screen.getByTestId('custom-icon')).toBeInTheDocument();
  });

  // Dismissible
  it('does not show dismiss button by default', () => {
    render(
      <CollapsibleBanner title="Title">
        <p>Content</p>
      </CollapsibleBanner>
    );

    // The close button has an SVG with specific path
    expect(screen.queryByRole('button')).not.toBeInTheDocument();
  });

  it('shows dismiss button when dismissible', () => {
    const { container } = render(
      <CollapsibleBanner title="Title" dismissible>
        <p>Content</p>
      </CollapsibleBanner>
    );

    // Find button with close icon
    const buttons = container.querySelectorAll('button');
    expect(buttons.length).toBeGreaterThan(0);
  });

  it('dismisses and calls onDismiss when dismiss button clicked', () => {
    const onDismiss = vi.fn();
    const { container } = render(
      <CollapsibleBanner title="Title" dismissible onDismiss={onDismiss}>
        <p>Content</p>
      </CollapsibleBanner>
    );

    const dismissButton = container.querySelector('button');
    fireEvent.click(dismissButton!);

    expect(onDismiss).toHaveBeenCalled();
    expect(screen.queryByText('Title')).not.toBeInTheDocument();
  });

  // Custom className
  it('applies custom className', () => {
    const { container } = render(
      <CollapsibleBanner title="Title" className="custom-banner">
        <p>Content</p>
      </CollapsibleBanner>
    );

    expect(container.firstChild).toHaveClass('custom-banner');
  });
});
