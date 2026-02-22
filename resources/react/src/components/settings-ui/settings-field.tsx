import * as React from 'react'

import { Label } from '@/components/ui/label'
import { cn } from '@/lib/utils'

interface SettingsFieldProps {
  id: string
  label: string
  description?: string
  layout?: 'inline' | 'stacked'
  nested?: boolean
  children: React.ReactNode
}

export function SettingsField({ id, label, description, layout = 'inline', nested, children }: SettingsFieldProps) {
  const content =
    layout === 'inline' ? (
      <div className="flex items-center justify-between gap-4">
        <div className="space-y-1">
          <Label htmlFor={id}>{label}</Label>
          {description && <p className="text-xs text-muted-foreground">{description}</p>}
        </div>
        {children}
      </div>
    ) : (
      <div className="space-y-2">
        <Label htmlFor={id}>{label}</Label>
        {children}
        {description && <p className="text-xs text-muted-foreground">{description}</p>}
      </div>
    )

  if (nested) {
    return <div className={cn('ml-6 pl-4 border-l-2 border-muted')}>{content}</div>
  }

  return content
}
