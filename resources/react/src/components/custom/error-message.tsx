import { AlertCircle } from 'lucide-react'

import { cn } from '@/lib/utils'

export interface ErrorMessageProps {
  /** The error message to display */
  message: string
  /** Additional CSS classes */
  className?: string
  /** Whether to show an icon */
  showIcon?: boolean
}

/**
 * ErrorMessage - Standardized error message component
 *
 * Uses semantic color tokens for consistent error styling across the application.
 * Replaces hardcoded `text-red-500` classes with `text-destructive`.
 *
 * @example
 * <ErrorMessage message={__('Failed to load data', 'wp-statistics')} />
 *
 * @example
 * <ErrorMessage
 *   message="Something went wrong"
 *   showIcon
 *   className="mt-4"
 * />
 */
export function ErrorMessage({ message, className, showIcon = false }: ErrorMessageProps) {
  return (
    <p className={cn('text-sm text-destructive', className)}>
      {showIcon && <AlertCircle className="inline-block h-4 w-4 mr-1.5 -mt-0.5" />}
      {message}
    </p>
  )
}
