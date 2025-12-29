import {
  AlertTriangle,
  AlertCircle,
  Info,
  CheckCircle2,
  XCircle,
  ShieldAlert,
  X,
} from 'lucide-react';
import { useState } from 'react';

type AlertVariant = 'info' | 'warning' | 'error' | 'success' | 'danger';

interface AlertProps {
  variant?: AlertVariant;
  title?: string;
  children: React.ReactNode;
  dismissible?: boolean;
  onDismiss?: () => void;
  icon?: React.ReactNode;
  action?: React.ReactNode;
  className?: string;
}

const variantConfig = {
  info: {
    icon: Info,
    bgColor: 'bg-blue-50',
    borderColor: 'border-blue-300',
    iconColor: 'text-blue-500',
    titleColor: 'text-blue-800',
    textColor: 'text-blue-700',
  },
  warning: {
    icon: AlertTriangle,
    bgColor: 'bg-amber-50',
    borderColor: 'border-amber-300',
    iconColor: 'text-amber-500',
    titleColor: 'text-amber-800',
    textColor: 'text-amber-700',
  },
  error: {
    icon: XCircle,
    bgColor: 'bg-red-50',
    borderColor: 'border-red-300',
    iconColor: 'text-red-500',
    titleColor: 'text-red-800',
    textColor: 'text-red-700',
  },
  success: {
    icon: CheckCircle2,
    bgColor: 'bg-green-50',
    borderColor: 'border-green-300',
    iconColor: 'text-green-500',
    titleColor: 'text-green-800',
    textColor: 'text-green-700',
  },
  danger: {
    icon: ShieldAlert,
    bgColor: 'bg-red-50',
    borderColor: 'border-red-400',
    iconColor: 'text-red-600',
    titleColor: 'text-red-900',
    textColor: 'text-red-800',
  },
};

export function Alert({
  variant = 'info',
  title,
  children,
  dismissible = false,
  onDismiss,
  icon,
  action,
  className = '',
}: AlertProps) {
  const [isDismissed, setIsDismissed] = useState(false);
  const config = variantConfig[variant];
  const Icon = config.icon;

  if (isDismissed) return null;

  const handleDismiss = () => {
    setIsDismissed(true);
    onDismiss?.();
  };

  return (
    <div
      className={`flex gap-3 p-4 rounded-lg border ${config.bgColor} ${config.borderColor} ${className}`}
      role="alert"
    >
      <div className={`flex-shrink-0 ${config.iconColor}`}>
        {icon || <Icon className="w-5 h-5" />}
      </div>
      <div className="flex-1 min-w-0">
        {title && (
          <h4 className={`font-semibold ${config.titleColor}`}>{title}</h4>
        )}
        <div className={`text-sm ${config.textColor} ${title ? 'mt-1' : ''}`}>
          {children}
        </div>
        {action && <div className="mt-3">{action}</div>}
      </div>
      {dismissible && (
        <button
          onClick={handleDismiss}
          className={`flex-shrink-0 ${config.iconColor} hover:opacity-70 transition-opacity`}
          aria-label="Dismiss"
        >
          <X className="w-4 h-4" />
        </button>
      )}
    </div>
  );
}

// Security warning alert
interface SecurityAlertProps {
  title?: string;
  children: React.ReactNode;
  severity?: 'low' | 'medium' | 'high' | 'critical';
  className?: string;
}

export function SecurityAlert({
  title = 'Security Notice',
  children,
  severity = 'medium',
  className = '',
}: SecurityAlertProps) {
  const severityConfig = {
    low: {
      bgColor: 'bg-slate-50',
      borderColor: 'border-slate-300',
      iconColor: 'text-slate-500',
      label: 'Low Risk',
      labelBg: 'bg-slate-200 text-slate-700',
    },
    medium: {
      bgColor: 'bg-amber-50',
      borderColor: 'border-amber-300',
      iconColor: 'text-amber-500',
      label: 'Medium Risk',
      labelBg: 'bg-amber-200 text-amber-800',
    },
    high: {
      bgColor: 'bg-orange-50',
      borderColor: 'border-orange-400',
      iconColor: 'text-orange-500',
      label: 'High Risk',
      labelBg: 'bg-orange-200 text-orange-800',
    },
    critical: {
      bgColor: 'bg-red-50',
      borderColor: 'border-red-500',
      iconColor: 'text-red-600',
      label: 'Critical',
      labelBg: 'bg-red-200 text-red-800',
    },
  };

  const config = severityConfig[severity];

  return (
    <div
      className={`rounded-lg border-2 ${config.bgColor} ${config.borderColor} p-4 ${className}`}
    >
      <div className="flex items-start gap-3">
        <ShieldAlert className={`w-6 h-6 ${config.iconColor} flex-shrink-0`} />
        <div className="flex-1">
          <div className="flex items-center gap-2 mb-2">
            <h4 className="font-semibold text-slate-900">{title}</h4>
            <span
              className={`text-xs font-medium px-2 py-0.5 rounded ${config.labelBg}`}
            >
              {config.label}
            </span>
          </div>
          <div className="text-sm text-slate-700">{children}</div>
        </div>
      </div>
    </div>
  );
}

// Recommendation alert
interface RecommendationProps {
  title: string;
  children: React.ReactNode;
  action?: {
    label: string;
    onClick: () => void;
  };
}

export function Recommendation({ title, children, action }: RecommendationProps) {
  return (
    <div className="flex gap-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
      <div className="flex-shrink-0">
        <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
          <AlertCircle className="w-4 h-4 text-blue-600" />
        </div>
      </div>
      <div className="flex-1">
        <h4 className="font-medium text-blue-900">{title}</h4>
        <p className="text-sm text-blue-700 mt-1">{children}</p>
        {action && (
          <button
            onClick={action.onClick}
            className="mt-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors"
          >
            {action.label} →
          </button>
        )}
      </div>
    </div>
  );
}
