import { Link } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { Archive, Infinity as InfinityIcon, Info, Trash2 } from 'lucide-react'
import * as React from 'react'

import { SettingsInfoBox } from '@/components/settings-ui'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import type { UseSettingsReturn } from '@/hooks/use-settings'
import { cn } from '@/lib/utils'

type RetentionMode = 'forever' | 'delete' | 'archive'

interface RetentionOption {
  value: RetentionMode
  icon: React.ReactNode
  title: string
  description: string
}

const retentionOptions: RetentionOption[] = [
  {
    value: 'forever',
    icon: <InfinityIcon className="h-5 w-5" />,
    title: __('Keep forever', 'wp-statistics'),
    description: __('Store all data indefinitely (may increase database size over time)', 'wp-statistics'),
  },
  {
    value: 'delete',
    icon: <Trash2 className="h-5 w-5" />,
    title: __('Delete after X days', 'wp-statistics'),
    description: __('Permanently remove all data older than specified days', 'wp-statistics'),
  },
  {
    value: 'archive',
    icon: <Archive className="h-5 w-5" />,
    title: __('Archive after X days', 'wp-statistics'),
    description: __('Create automatic backups, then delete raw data (recommended)', 'wp-statistics'),
  },
]

/**
 * Radio-card selector for data retention mode + days input.
 * Registered as a `type: 'component'` field in the data-management tab.
 */
export function RetentionModeSelector({ settings }: { settings: UseSettingsReturn }) {
  const retentionMode = settings.getValue('data_retention_mode', 'forever') as RetentionMode
  const retentionDays = settings.getValue('data_retention_days', 180) as number

  const setRetentionMode = (value: RetentionMode) => settings.setValue('data_retention_mode', value)
  const setRetentionDays = (value: number) => settings.setValue('data_retention_days', value)

  return (
    <>
      <div className="grid gap-3">
        {retentionOptions.map((option) => (
          <button
            key={option.value}
            type="button"
            onClick={() => setRetentionMode(option.value)}
            className={cn(
              'flex items-start gap-4 rounded-lg border p-4 text-left transition-colors',
              retentionMode === option.value ? 'border-primary bg-primary/5' : 'border-border hover:bg-muted/50'
            )}
          >
            <div
              className={cn(
                'mt-0.5 flex h-10 w-10 items-center justify-center rounded-lg',
                retentionMode === option.value
                  ? 'bg-primary text-primary-foreground'
                  : 'bg-muted text-muted-foreground'
              )}
            >
              {option.icon}
            </div>
            <div className="flex-1">
              <div className="flex items-center gap-2">
                <span className={cn('font-medium', retentionMode === option.value && 'text-primary')}>
                  {option.title}
                </span>
                {option.value === 'archive' && (
                  <span className="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                    {__('Recommended', 'wp-statistics')}
                  </span>
                )}
              </div>
              <p className="mt-1 text-sm text-muted-foreground">{option.description}</p>
            </div>
            <div
              className={cn(
                'mt-1 h-4 w-4 rounded-full border-2',
                retentionMode === option.value ? 'border-primary bg-primary' : 'border-muted-foreground/50'
              )}
            >
              {retentionMode === option.value && (
                <div className="h-full w-full flex items-center justify-center">
                  <div className="h-1.5 w-1.5 rounded-full bg-white" />
                </div>
              )}
            </div>
          </button>
        ))}
      </div>

      {retentionMode !== 'forever' && (
        <div className="space-y-2 rounded-lg border bg-muted/30 p-4">
          <Label htmlFor="retention-days">{__('Retention Period', 'wp-statistics')}</Label>
          <div className="flex items-center gap-3">
            <Input
              id="retention-days"
              type="number"
              min={30}
              max={365}
              value={retentionDays}
              onChange={(e) => setRetentionDays(parseInt(e.target.value) || 180)}
              className="w-24"
            />
            <span className="text-sm text-muted-foreground">{__('days', 'wp-statistics')}</span>
          </div>
          <p className="text-sm text-muted-foreground">
            {retentionMode === 'delete'
              ? __('All visitor, session, and view data older than this will be permanently deleted.', 'wp-statistics')
              : __('A backup will be created automatically, then raw session and view data will be removed.', 'wp-statistics')}
          </p>
        </div>
      )}

      {retentionMode === 'archive' && (
        <SettingsInfoBox icon={Info}>
          <p>
            {__('When using Archive mode, automatic backups are created before data is deleted. You can manage these backups in', 'wp-statistics')}{' '}
            <Link to="/tools/backups" className="font-medium underline underline-offset-4">
              {__('Tools', 'wp-statistics')} &rarr; {__('Backups', 'wp-statistics')}
            </Link>
            .
          </p>
        </SettingsInfoBox>
      )}
    </>
  )
}
