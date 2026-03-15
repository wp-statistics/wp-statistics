import type { LucideIcon } from 'lucide-react'
import { AlertTriangle } from 'lucide-react'
import * as React from 'react'

import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { cn } from '@/lib/utils'

interface SettingsCardProps {
  title: string
  description?: string
  icon?: LucideIcon
  variant?: 'default' | 'danger'
  action?: React.ReactNode
  children: React.ReactNode
}

export function SettingsCard({ title, description, icon: Icon, variant = 'default', action, children }: SettingsCardProps) {
  const isDanger = variant === 'danger'

  return (
    <Card className={cn('gap-4', isDanger && 'border-destructive/50 bg-destructive/5')}>
      <CardHeader>
        <CardTitle className={cn('flex items-center gap-2 text-base', isDanger && 'text-destructive')}>
          {isDanger ? <AlertTriangle className="h-5 w-5" /> : Icon ? <Icon className="h-5 w-5" /> : null}
          {title}
        </CardTitle>
        {description && <CardDescription>{description}</CardDescription>}
        {action && <CardAction>{action}</CardAction>}
      </CardHeader>
      <CardContent className="space-y-5">{children}</CardContent>
    </Card>
  )
}
