import { useState } from 'react';
import { AlertTriangle, ShieldX, Skull, Lock, XCircle } from 'lucide-react';
import Button from './Button';
import Modal from './Modal';
import Input from './Input';

interface DangerZoneProps {
  title?: string;
  description?: string;
  children: React.ReactNode;
}

export function DangerZone({
  title = 'Danger Zone',
  description = 'Actions in this section are potentially destructive and cannot be undone. Please proceed with caution.',
  children,
}: DangerZoneProps) {
  return (
    <div className="rounded-lg border-2 border-red-300 bg-red-50/50 overflow-hidden">
      <div className="bg-red-100 border-b border-red-200 px-4 py-3">
        <div className="flex items-center gap-2">
          <ShieldX className="w-5 h-5 text-red-600" />
          <h3 className="font-semibold text-red-800">{title}</h3>
        </div>
        <p className="text-sm text-red-600 mt-1">{description}</p>
      </div>
      <div className="p-4 space-y-4">{children}</div>
    </div>
  );
}

// Individual danger action item
interface DangerActionProps {
  icon?: React.ReactNode;
  title: string;
  description: string;
  buttonLabel: string;
  onAction: () => void | Promise<void>;
  confirmText?: string;
  confirmPlaceholder?: string;
  warningMessage?: string;
  loading?: boolean;
}

export function DangerAction({
  icon,
  title,
  description,
  buttonLabel,
  onAction,
  confirmText,
  confirmPlaceholder = 'Type to confirm...',
  warningMessage,
  loading = false,
}: DangerActionProps) {
  const [showConfirm, setShowConfirm] = useState(false);
  const [confirmInput, setConfirmInput] = useState('');
  const [isProcessing, setIsProcessing] = useState(false);

  const handleAction = async () => {
    if (confirmText && confirmInput !== confirmText) return;

    setIsProcessing(true);
    try {
      await onAction();
      setShowConfirm(false);
      setConfirmInput('');
    } finally {
      setIsProcessing(false);
    }
  };

  const canConfirm = !confirmText || confirmInput === confirmText;

  return (
    <>
      <div className="flex items-center justify-between p-4 bg-white rounded-lg border border-red-200">
        <div className="flex items-start gap-3">
          <div className="p-2 bg-red-100 rounded-lg text-red-600">
            {icon || <AlertTriangle className="w-5 h-5" />}
          </div>
          <div>
            <h4 className="font-medium text-slate-900">{title}</h4>
            <p className="text-sm text-slate-600 mt-0.5">{description}</p>
          </div>
        </div>
        <Button
          variant="danger"
          size="sm"
          onClick={() => setShowConfirm(true)}
          loading={loading}
        >
          {buttonLabel}
        </Button>
      </div>

      <Modal
        isOpen={showConfirm}
        onClose={() => {
          setShowConfirm(false);
          setConfirmInput('');
        }}
        title={title}
        size="md"
      >
        <div className="space-y-4">
          <div className="flex items-start gap-3 p-4 bg-red-50 rounded-lg border border-red-200">
            <Skull className="w-6 h-6 text-red-600 flex-shrink-0" />
            <div>
              <p className="font-medium text-red-800">Are you absolutely sure?</p>
              <p className="text-sm text-red-700 mt-1">
                {warningMessage || 'This action cannot be undone. Please make sure you understand the consequences before proceeding.'}
              </p>
            </div>
          </div>

          {confirmText && (
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">
                Type <code className="bg-slate-100 px-1.5 py-0.5 rounded text-red-600">{confirmText}</code> to confirm:
              </label>
              <Input
                value={confirmInput}
                onChange={(e: React.ChangeEvent<HTMLInputElement>) => setConfirmInput(e.target.value)}
                placeholder={confirmPlaceholder}
                className="font-mono"
              />
            </div>
          )}

          <div className="flex justify-end gap-3 pt-2">
            <Button
              variant="outline"
              onClick={() => {
                setShowConfirm(false);
                setConfirmInput('');
              }}
            >
              Cancel
            </Button>
            <Button
              variant="danger"
              onClick={handleAction}
              disabled={!canConfirm}
              loading={isProcessing}
            >
              {buttonLabel}
            </Button>
          </div>
        </div>
      </Modal>
    </>
  );
}

// Warning banner for pages with dangerous content
interface DangerBannerProps {
  message?: string;
  dismissible?: boolean;
}

export function DangerBanner({
  message = 'This page contains settings that can affect your site\'s functionality. Make changes carefully.',
  dismissible = true,
}: DangerBannerProps) {
  const [isDismissed, setIsDismissed] = useState(false);

  if (isDismissed) return null;

  return (
    <div className="flex items-center gap-3 p-3 bg-gradient-to-r from-red-500 to-orange-500 text-white rounded-lg mb-6">
      <AlertTriangle className="w-5 h-5 flex-shrink-0" />
      <p className="text-sm flex-1">{message}</p>
      {dismissible && (
        <button
          onClick={() => setIsDismissed(true)}
          className="p-1 hover:bg-white/20 rounded transition-colors"
          aria-label="Dismiss"
        >
          <XCircle className="w-4 h-4" />
        </button>
      )}
    </div>
  );
}

// Lock indicator for protected actions
interface LockedActionProps {
  title: string;
  reason: string;
}

export function LockedAction({ title, reason }: LockedActionProps) {
  return (
    <div className="flex items-center gap-3 p-4 bg-slate-100 rounded-lg border border-slate-200 opacity-60">
      <Lock className="w-5 h-5 text-slate-400" />
      <div>
        <h4 className="font-medium text-slate-600">{title}</h4>
        <p className="text-sm text-slate-500">{reason}</p>
      </div>
    </div>
  );
}
