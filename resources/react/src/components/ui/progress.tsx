import * as React from 'react'

import { cn } from '@/lib/utils'

interface ProgressProps extends React.HTMLAttributes<HTMLDivElement> {
  value?: number
  max?: number
  'aria-label'?: string
}

const Progress = React.forwardRef<HTMLDivElement, ProgressProps>(
  ({ className, value = 0, max = 100, 'aria-label': ariaLabel = 'Progress', ...props }, ref) => {
    const percentage = Math.min(Math.max((value / max) * 100, 0), 100)

    return (
      <div
        ref={ref}
        role="progressbar"
        aria-label={ariaLabel}
        aria-valuemin={0}
        aria-valuemax={max}
        aria-valuenow={value}
        className={cn('relative h-2 w-full overflow-hidden rounded-full bg-primary/20', className)}
        {...props}
      >
        <div
          className="h-full bg-primary transition-all duration-300 ease-in-out"
          style={{ width: `${percentage}%` }}
        />
      </div>
    )
  }
)
Progress.displayName = 'Progress'

export { Progress }
