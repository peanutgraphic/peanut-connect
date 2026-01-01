import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import {
  DangerZone,
  DangerAction,
  DangerBanner,
  LockedAction,
} from './DangerZone';

describe('DangerZone', () => {
  it('renders with default title', () => {
    render(
      <DangerZone>
        <div>Content</div>
      </DangerZone>
    );

    expect(screen.getByText('Danger Zone')).toBeInTheDocument();
  });

  it('renders custom title', () => {
    render(
      <DangerZone title="Critical Actions">
        <div>Content</div>
      </DangerZone>
    );

    expect(screen.getByText('Critical Actions')).toBeInTheDocument();
  });

  it('renders default description', () => {
    render(
      <DangerZone>
        <div>Content</div>
      </DangerZone>
    );

    expect(
      screen.getByText(/Actions in this section are potentially destructive/)
    ).toBeInTheDocument();
  });

  it('renders custom description', () => {
    render(
      <DangerZone description="Be careful here!">
        <div>Content</div>
      </DangerZone>
    );

    expect(screen.getByText('Be careful here!')).toBeInTheDocument();
  });

  it('renders children', () => {
    render(
      <DangerZone>
        <div data-testid="child">Child content</div>
      </DangerZone>
    );

    expect(screen.getByTestId('child')).toBeInTheDocument();
  });

  it('has danger styling', () => {
    const { container } = render(
      <DangerZone>
        <div>Content</div>
      </DangerZone>
    );

    expect(container.firstChild).toHaveClass('border-red-300');
  });
});

describe('DangerAction', () => {
  it('renders title and description', () => {
    render(
      <DangerAction
        title="Delete Site"
        description="This will delete your site"
        buttonLabel="Delete"
        onAction={() => {}}
      />
    );

    expect(screen.getByText('Delete Site')).toBeInTheDocument();
    expect(screen.getByText('This will delete your site')).toBeInTheDocument();
  });

  it('renders action button', () => {
    render(
      <DangerAction
        title="Delete"
        description="Delete action"
        buttonLabel="Delete Now"
        onAction={() => {}}
      />
    );

    expect(screen.getByRole('button', { name: 'Delete Now' })).toBeInTheDocument();
  });

  it('opens confirmation modal on button click', async () => {
    render(
      <DangerAction
        title="Delete Account"
        description="Remove your account"
        buttonLabel="Delete"
        onAction={() => {}}
      />
    );

    fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

    await waitFor(() => {
      expect(screen.getByText('Are you absolutely sure?')).toBeInTheDocument();
    });
  });

  it('renders custom warning message in modal', async () => {
    render(
      <DangerAction
        title="Delete"
        description="Delete action"
        buttonLabel="Delete"
        onAction={() => {}}
        warningMessage="Custom warning message"
      />
    );

    fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

    await waitFor(() => {
      expect(screen.getByText('Custom warning message')).toBeInTheDocument();
    });
  });

  it('shows confirm input when confirmText is provided', async () => {
    render(
      <DangerAction
        title="Delete"
        description="Delete action"
        buttonLabel="Delete"
        onAction={() => {}}
        confirmText="DELETE"
      />
    );

    fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

    await waitFor(() => {
      expect(screen.getByPlaceholderText('Type to confirm...')).toBeInTheDocument();
    });
  });

  it('shows custom confirm placeholder', async () => {
    render(
      <DangerAction
        title="Delete"
        description="Delete action"
        buttonLabel="Delete"
        onAction={() => {}}
        confirmText="DELETE"
        confirmPlaceholder="Enter DELETE"
      />
    );

    fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

    await waitFor(() => {
      expect(screen.getByPlaceholderText('Enter DELETE')).toBeInTheDocument();
    });
  });

  it('disables confirm button until correct text is entered', async () => {
    render(
      <DangerAction
        title="Delete"
        description="Delete action"
        buttonLabel="Delete"
        onAction={() => {}}
        confirmText="DELETE"
      />
    );

    fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

    await waitFor(() => {
      const confirmButtons = screen.getAllByRole('button', { name: 'Delete' });
      // Modal confirm button should be disabled
      expect(confirmButtons[confirmButtons.length - 1]).toBeDisabled();
    });
  });

  it('enables confirm button when correct text is entered', async () => {
    render(
      <DangerAction
        title="Delete"
        description="Delete action"
        buttonLabel="Delete"
        onAction={() => {}}
        confirmText="DELETE"
      />
    );

    fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

    await waitFor(() => {
      expect(screen.getByPlaceholderText('Type to confirm...')).toBeInTheDocument();
    });

    fireEvent.change(screen.getByPlaceholderText('Type to confirm...'), {
      target: { value: 'DELETE' },
    });

    await waitFor(() => {
      const confirmButtons = screen.getAllByRole('button', { name: 'Delete' });
      expect(confirmButtons[confirmButtons.length - 1]).not.toBeDisabled();
    });
  });

  it('calls onAction when confirmed without confirmText', async () => {
    const onAction = vi.fn();
    render(
      <DangerAction
        title="Delete"
        description="Delete action"
        buttonLabel="Delete"
        onAction={onAction}
      />
    );

    fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

    await waitFor(() => {
      expect(screen.getByText('Are you absolutely sure?')).toBeInTheDocument();
    });

    const confirmButtons = screen.getAllByRole('button', { name: 'Delete' });
    fireEvent.click(confirmButtons[confirmButtons.length - 1]);

    await waitFor(() => {
      expect(onAction).toHaveBeenCalled();
    });
  });

  it('closes modal when cancel is clicked', async () => {
    render(
      <DangerAction
        title="Delete"
        description="Delete action"
        buttonLabel="Delete"
        onAction={() => {}}
      />
    );

    fireEvent.click(screen.getByRole('button', { name: 'Delete' }));

    await waitFor(() => {
      expect(screen.getByText('Are you absolutely sure?')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByRole('button', { name: 'Cancel' }));

    await waitFor(() => {
      expect(screen.queryByText('Are you absolutely sure?')).not.toBeInTheDocument();
    });
  });

  it('shows loading state on button', () => {
    render(
      <DangerAction
        title="Delete"
        description="Delete action"
        buttonLabel="Delete"
        onAction={() => {}}
        loading={true}
      />
    );

    expect(screen.getByRole('button', { name: 'Delete' })).toBeInTheDocument();
  });
});

describe('DangerBanner', () => {
  it('renders with default message', () => {
    render(<DangerBanner />);

    expect(
      screen.getByText(/This page contains settings that can affect/)
    ).toBeInTheDocument();
  });

  it('renders custom message', () => {
    render(<DangerBanner message="Custom danger message" />);

    expect(screen.getByText('Custom danger message')).toBeInTheDocument();
  });

  it('has dismiss button by default', () => {
    render(<DangerBanner />);

    expect(screen.getByLabelText('Dismiss')).toBeInTheDocument();
  });

  it('dismisses when dismiss button is clicked', () => {
    render(<DangerBanner />);

    fireEvent.click(screen.getByLabelText('Dismiss'));

    expect(
      screen.queryByText(/This page contains settings/)
    ).not.toBeInTheDocument();
  });

  it('hides dismiss button when dismissible is false', () => {
    render(<DangerBanner dismissible={false} />);

    expect(screen.queryByLabelText('Dismiss')).not.toBeInTheDocument();
  });

  it('has gradient background', () => {
    const { container } = render(<DangerBanner />);

    expect(container.firstChild).toHaveClass('bg-gradient-to-r');
  });
});

describe('LockedAction', () => {
  it('renders title', () => {
    render(<LockedAction title="Update WordPress" reason="Updates are disabled" />);

    expect(screen.getByText('Update WordPress')).toBeInTheDocument();
  });

  it('renders reason', () => {
    render(<LockedAction title="Update WordPress" reason="Updates are disabled" />);

    expect(screen.getByText('Updates are disabled')).toBeInTheDocument();
  });

  it('has opacity styling', () => {
    const { container } = render(
      <LockedAction title="Locked" reason="Reason" />
    );

    expect(container.firstChild).toHaveClass('opacity-60');
  });

  it('has slate background', () => {
    const { container } = render(
      <LockedAction title="Locked" reason="Reason" />
    );

    expect(container.firstChild).toHaveClass('bg-slate-100');
  });
});
