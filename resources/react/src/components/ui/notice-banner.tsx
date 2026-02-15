import { __ } from '@wordpress/i18n'
import { AlertCircle, AlertTriangle, CheckCircle, Info, type LucideIcon,X } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

const typeStyles = {
  info: {
    container: 'bg-blue-50 border-blue-200',
    icon: 'text-blue-500',
    text: 'text-blue-800',
  },
  warning: {
    container: 'bg-yellow-50 border-yellow-200',
    icon: 'text-yellow-600',
    text: 'text-yellow-800',
  },
  error: {
    container: 'bg-red-50 border-red-200',
    icon: 'text-red-500',
    text: 'text-red-800',
  },
  success: {
    container: 'bg-green-50 border-green-200',
    icon: 'text-green-500',
    text: 'text-green-800',
  },
  // Neutral variant for informational boxes (like "System Diagnostics")
  neutral: {
    container: 'bg-muted/50 border-border',
    icon: 'text-muted-foreground',
    text: 'text-foreground',
  },
}

const defaultIconMap: Record<string, LucideIcon> = {
  info: Info,
  warning: AlertTriangle,
  error: AlertCircle,
  success: CheckCircle,
  neutral: Info,
}

export interface NoticeBannerProps extends React.HTMLAttributes<HTMLDivElement> {
  /**
   * Unique identifier for the notice (required for dismissible notices)
   */
  id?: string
  /**
   * Optional title displayed above the message
   */
  title?: string
  /**
   * The notice message to display
   */
  message: string
  /**
   * Notice type determining the visual style
   * - info: Blue background for informational notices
   * - warning: Yellow background for warnings
   * - error: Red background for errors/critical issues
   * - success: Green background for success messages
   * - neutral: Gray background for informational boxes
   */
  type?: 'info' | 'warning' | 'error' | 'success' | 'neutral'
  /**
   * Custom icon to display (overrides default type icon)
   */
  icon?: LucideIcon
  /**
   * URL for the primary action button
   */
  actionUrl?: string | null
  /**
   * Label for the primary action button
   */
  actionLabel?: string | null
  /**
   * URL for help/documentation link
   */
  helpUrl?: string | null
  /**
   * Whether the notice can be dismissed
   */
  dismissible?: boolean
  /**
   * Callback when the notice is dismissed
   */
  onDismiss?: (id: string) => void
}

/**
 * Notice banner component for displaying admin notices and informational boxes.
 *
 * @example
 * ```tsx
 * // Dismissible alert notice
 * <NoticeBanner
 *   id="diagnostic_issues"
 *   message="2 issues detected that may affect functionality."
 *   type="warning"
 *   actionUrl="/tools/diagnostics"
 *   actionLabel="View Diagnostics"
 *   dismissible
 *   onDismiss={(id) => console.log('Dismissed:', id)}
 * />
 *
 * // Informational box (non-dismissible)
 * <NoticeBanner
 *   title="System Diagnostics"
 *   message="These checks help identify potential issues."
 *   type="neutral"
 *   icon={Stethoscope}
 *   dismissible={false}
 * />
 * ```
 */
export function NoticeBanner({
  id,
  title,
  message,
  type = 'info',
  icon,
  actionUrl,
  actionLabel,
  helpUrl,
  dismissible = true,
  onDismiss,
  className,
  ...props
}: NoticeBannerProps) {
  const Icon = icon || defaultIconMap[type] || Info
  const styles = typeStyles[type] || typeStyles.info

  const handleDismiss = () => {
    if (id) {
      onDismiss?.(id)
    }
  }

  return (
    <div
      className={cn('rounded-lg border px-4 py-2.5', styles.container, className)}
      role={dismissible ? 'alert' : 'note'}
      {...props}
    >
      <div className="flex items-center gap-3">
        <Icon className={cn('h-5 w-5 shrink-0', styles.icon)} />
        <div className="flex-1 min-w-0 flex items-center gap-2">
          {title && <span className={cn('font-medium', styles.text)}>{title}</span>}
          <p className={cn('text-sm', title ? 'text-muted-foreground' : styles.text)}>{message}</p>
        </div>
        {(actionUrl || helpUrl) && (
          <div className="flex items-center gap-3 shrink-0">
            {actionUrl && (
              <a href={actionUrl} className={cn('text-sm font-medium hover:underline whitespace-nowrap', styles.text)}>
                {actionLabel || __('View', 'wp-statistics')}
              </a>
            )}
            {helpUrl && (
              <a
                href={helpUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="text-sm text-muted-foreground hover:text-foreground whitespace-nowrap"
              >
                {__('Learn more', 'wp-statistics')}
              </a>
            )}
          </div>
        )}
        {dismissible && id && (
          <Button
            variant="ghost"
            size="sm"
            onClick={handleDismiss}
            className="h-6 w-6 shrink-0 p-0 text-muted-foreground hover:text-foreground hover:bg-transparent"
            aria-label={__('Dismiss', 'wp-statistics')}
          >
            <X className="h-4 w-4" />
          </Button>
        )}
      </div>
    </div>
  )
}
