import { __ } from '@wordpress/i18n'
import { AlertCircle, RefreshCw } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

export interface ApiErrorProps {
  /**
   * Error title
   */
  title?: string
  /**
   * Error object or message
   */
  error?: Error | string | null
  /**
   * Callback when retry button is clicked
   */
  onRetry?: () => void
  /**
   * Additional CSS classes
   */
  className?: string
  /**
   * Size variant
   */
  size?: 'sm' | 'md' | 'lg'
}

/**
 * Standardized API error display component.
 * Shows an error message with optional retry button.
 *
 * @example
 * ```tsx
 * const { data, error, refetch, isError } = useQuery(...)
 *
 * if (isError) {
 *   return <ApiError error={error} onRetry={refetch} />
 * }
 * ```
 */
export function ApiError({
  title,
  error,
  onRetry,
  className,
  size = 'md',
}: ApiErrorProps) {
  const errorMessage = error instanceof Error ? error.message : error

  const sizeClasses = {
    sm: 'p-3 gap-2',
    md: 'p-4 gap-3',
    lg: 'p-6 gap-4',
  }

  const iconSizes = {
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-6 w-6',
  }

  const textSizes = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base',
  }

  return (
    <div
      className={cn(
        'flex flex-col items-center justify-center text-center',
        sizeClasses[size],
        className
      )}
    >
      <AlertCircle className={cn('text-destructive', iconSizes[size])} />
      <div className="flex flex-col gap-1">
        <p className={cn('font-medium text-destructive', textSizes[size])}>
          {title || __('Failed to load data', 'wp-statistics')}
        </p>
        {errorMessage && (
          <p className={cn('text-muted-foreground', textSizes[size])}>
            {errorMessage}
          </p>
        )}
      </div>
      {onRetry && (
        <Button
          variant="outline"
          size={size === 'lg' ? 'default' : 'sm'}
          onClick={onRetry}
          className="mt-2"
        >
          <RefreshCw className="mr-2 h-4 w-4" />
          {__('Try again', 'wp-statistics')}
        </Button>
      )}
    </div>
  )
}

/**
 * Inline API error for use within components
 */
export function ApiErrorInline({
  error,
  onRetry,
  className,
}: Omit<ApiErrorProps, 'title' | 'size'>) {
  const errorMessage = error instanceof Error ? error.message : error

  return (
    <div className={cn('flex items-center gap-2 text-sm text-destructive', className)}>
      <AlertCircle className="h-4 w-4 shrink-0" />
      <span className="truncate">{errorMessage || __('An error occurred', 'wp-statistics')}</span>
      {onRetry && (
        <Button variant="ghost" size="sm" onClick={onRetry} className="h-6 px-2">
          <RefreshCw className="h-3 w-3" />
        </Button>
      )}
    </div>
  )
}
