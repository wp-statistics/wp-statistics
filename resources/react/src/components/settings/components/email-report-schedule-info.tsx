import { __ } from '@wordpress/i18n'

import type { UseSettingsReturn } from '@/hooks/use-settings'
import { WordPress } from '@/lib/wordpress'

/**
 * Shows a computed "Next email scheduled for..." info line.
 * Updates live as the user changes frequency or delivery hour.
 */
export function EmailReportScheduleInfo({ settings }: { settings: UseSettingsReturn }) {
  const frequency = (settings.getValue('email_reports_frequency', 'weekly') as string)
  const hour = parseInt(settings.getValue('email_reports_delivery_hour', '8') as string, 10)

  const now = new Date()
  const clampedHour = Number.isFinite(hour) ? Math.min(23, Math.max(0, hour)) : 8
  let next: Date
  const todayAtHour = new Date(now.getFullYear(), now.getMonth(), now.getDate(), clampedHour, 0, 0)

  switch (frequency) {
    case 'daily': {
      next = todayAtHour <= now
        ? new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, clampedHour, 0, 0)
        : todayAtHour
      break
    }
    case 'monthly': {
      const firstOfMonth = new Date(now.getFullYear(), now.getMonth(), 1, clampedHour, 0, 0)
      next = firstOfMonth <= now
        ? new Date(now.getFullYear(), now.getMonth() + 1, 1, clampedHour, 0, 0)
        : firstOfMonth
      break
    }
    case 'weekly':
    default: {
      const rawStartOfWeek = WordPress.getInstance().getStartOfWeek()
      const startOfWeek = Number.isFinite(rawStartOfWeek) ? Math.min(6, Math.max(0, rawStartOfWeek)) : 0
      const daysUntilStart = (startOfWeek - now.getDay() + 7) % 7
      const candidate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + daysUntilStart, clampedHour, 0, 0)
      next = candidate <= now
        ? new Date(now.getFullYear(), now.getMonth(), now.getDate() + daysUntilStart + 7, clampedHour, 0, 0)
        : candidate
      break
    }
  }

  const formatted = new Intl.DateTimeFormat(undefined, {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  }).format(next)

  return (
    <p className="text-xs text-muted-foreground">
      {__('Next email scheduled for', 'wp-statistics')}{' '}
      <span className="font-medium text-foreground">{formatted}</span>
    </p>
  )
}
