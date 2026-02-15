import { Database } from 'lucide-react'
import * as React from 'react'

import { cn } from '@/lib/utils'

interface EmptyStateProps {
  icon?: React.ReactNode
  title?: string
  description?: string
  action?: React.ReactNode
  className?: string
}

export function EmptyState({ icon, title = 'No data available', description, action, className }: EmptyStateProps) {
  return (
    <div className={cn('flex flex-col items-center justify-center py-12 px-4 text-center', className)}>
      <div className="text-neutral-300 mb-4">{icon || <Database className="h-12 w-12" />}</div>
      <h3 className="text-sm font-medium text-neutral-600 mb-1">{title}</h3>
      {description && <p className="text-xs text-neutral-500 max-w-[200px]">{description}</p>}
      {action && <div className="mt-4">{action}</div>}
    </div>
  )
}
