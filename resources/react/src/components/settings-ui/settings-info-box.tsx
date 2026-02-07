import type { LucideIcon } from 'lucide-react'
import * as React from 'react'

interface SettingsInfoBoxProps {
  title?: string
  icon?: LucideIcon
  children: React.ReactNode
}

export function SettingsInfoBox({ title, icon: Icon, children }: SettingsInfoBoxProps) {
  return (
    <div className="rounded-lg border bg-muted/50 px-4 py-3">
      {(title || Icon) && (
        <div className="flex items-center gap-2 mb-2">
          {Icon && <Icon className="h-4 w-4 text-muted-foreground" />}
          {title && <h4 className="text-sm font-medium">{title}</h4>}
        </div>
      )}
      <div className="text-sm text-muted-foreground">{children}</div>
    </div>
  )
}
