import { Link } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { Archive, Infinity as InfinityIcon, Info, type LucideIcon,Trash2 } from 'lucide-react'

import { SettingsInfoBox } from '@/components/settings-ui'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { RadioCardGroup, type RadioCardOption } from '@/components/ui/radio-card-group'
import type { UseSettingsReturn } from '@/hooks/use-settings'
import { cn } from '@/lib/utils'

type RetentionMode = 'forever' | 'delete' | 'archive'

const retentionIcon = (Icon: LucideIcon) =>
  ({ isSelected }: { isSelected: boolean }) => (
    <div
      className={cn(
        'mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg',
        isSelected ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'
      )}
    >
      <Icon className="h-5 w-5" />
    </div>
  )

const retentionOptions: RadioCardOption[] = [
  {
    value: 'forever',
    icon: retentionIcon(InfinityIcon),
    label: __('Keep forever', 'wp-statistics'),
    description: __('Store all data indefinitely (may increase database size over time)', 'wp-statistics'),
  },
  {
    value: 'delete',
    icon: retentionIcon(Trash2),
    label: __('Delete after X days', 'wp-statistics'),
    description: __('Permanently remove all data older than specified days', 'wp-statistics'),
  },
  {
    value: 'archive',
    icon: retentionIcon(Archive),
    label: __('Archive after X days', 'wp-statistics'),
    description: __('Create automatic backups, then delete raw data (recommended)', 'wp-statistics'),
    badge: __('Recommended', 'wp-statistics'),
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
      <RadioCardGroup
        name="retention-mode"
        value={retentionMode}
        onValueChange={(v) => setRetentionMode(v as RetentionMode)}
        options={retentionOptions}
        indicator="radio"
      />

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
