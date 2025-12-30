import { forwardRef, type ButtonHTMLAttributes, type ReactNode } from 'react';
import { clsx } from 'clsx';
import { Loader2 } from 'lucide-react';

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger' | 'success';
  size?: 'sm' | 'md' | 'lg';
  loading?: boolean;
  icon?: ReactNode;
  iconPosition?: 'left' | 'right';
}

const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      className,
      variant = 'primary',
      size = 'md',
      loading = false,
      disabled,
      icon,
      iconPosition = 'left',
      children,
      ...props
    },
    ref
  ) => {
    const baseStyles =
      'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    const variants = {
      primary:
        'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
      secondary:
        'bg-slate-100 text-slate-900 hover:bg-slate-200 focus:ring-slate-500',
      outline:
        'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-primary-500',
      ghost:
        'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:ring-slate-500',
      danger:
        'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
      success:
        'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    };

    const sizes = {
      sm: 'px-3 py-1.5 text-sm gap-1.5',
      md: 'px-4 py-2 text-sm gap-2',
      lg: 'px-5 py-2.5 text-base gap-2',
    };

    const iconSizes = {
      sm: 'w-4 h-4',
      md: 'w-4 h-4',
      lg: 'w-5 h-5',
    };

    return (
      <button
        ref={ref}
        className={clsx(baseStyles, variants[variant], sizes[size], className)}
        disabled={disabled || loading}
        {...props}
      >
        {loading ? (
          <Loader2 className={clsx('animate-spin', iconSizes[size])} />
        ) : icon && iconPosition === 'left' ? (
          <span className={iconSizes[size]}>{icon}</span>
        ) : null}
        {children}
        {!loading && icon && iconPosition === 'right' && (
          <span className={iconSizes[size]}>{icon}</span>
        )}
      </button>
    );
  }
);

Button.displayName = 'Button';

export default Button;
