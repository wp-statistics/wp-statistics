import * as React from 'react'
import {
  Loader2,
  Trash2,
  AlertTriangle,
  Infinity,
  Clock,
  Archive,
  Info,
} from 'lucide-react'
import { Link } from '@tanstack/react-router'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useSettings, useSetting } from '@/hooks/use-settings'
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
    icon: <Infinity className="h-5 w-5" />,
    title: 'Keep forever',
    description: 'Store all data indefinitely (may increase database size over time)',
  },
  {
    value: 'delete',
    icon: <Trash2 className="h-5 w-5" />,
    title: 'Delete after X days',
    description: 'Permanently remove all data older than specified days',
  },
  {
    value: 'archive',
    icon: <Archive className="h-5 w-5" />,
    title: 'Archive after X days',
    description: 'Create automatic backups, then delete raw data (recommended)',
  },
]

// Helper to get config
const getConfig = () => {
  const wpsReact = (window as any).wps_react
  return {
    ajaxUrl: wpsReact?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php',
    nonce: wpsReact?.importExport?.nonce || wpsReact?.globals?.nonce || '',
    actions: {
      purgeDataNow: 'wp_statistics_purge_data_now',
      ...(wpsReact?.importExport?.actions || {}),
    },
  }
}

export function DataManagementSettings() {
  const settings = useSettings({ tab: 'data' })
  const [isPurging, setIsPurging] = React.useState(false)

  // Data retention settings
  const [retentionMode, setRetentionMode] = useSetting(
    settings,
    'data_retention_mode',
    'forever'
  )
  const [retentionDays, setRetentionDays] = useSetting(settings, 'data_retention_days', 180)

  const applyRetentionNow = async () => {
    if (retentionMode === 'forever') {
      return
    }

    if (
      !confirm(
        `Are you sure you want to apply the retention policy now? This will ${
          retentionMode === 'delete' ? 'permanently delete' : 'archive and then delete'
        } data older than ${retentionDays} days. This action cannot be undone.`
      )
    ) {
      return
    }

    setIsPurging(true)
    try {
      const config = getConfig()
      const formData = new FormData()
      formData.append('_wpnonce', config.nonce)

      const response = await fetch(
        `${config.ajaxUrl}?action=${config.actions.purgeDataNow}`,
        {
          method: 'POST',
          headers: {
            'X-WP-Nonce': config.nonce,
          },
          body: formData,
        }
      )

      const data = await response.json()

      if (data.success) {
        alert(data.data?.message || 'Data cleanup completed successfully.')
      } else {
        alert(data.data?.message || 'Failed to apply retention policy.')
      }
    } catch (error) {
      alert('Failed to apply retention policy. Please try again.')
    } finally {
      setIsPurging(false)
    }
  }

  const handleSave = async () => {
    const success = await settings.save()
    if (success) {
      // Could show a toast notification here
    }
  }

  if (settings.isLoading) {
    return (
      <div className="flex items-center justify-center p-8">
        <Loader2 className="h-6 w-6 animate-spin" />
        <span className="ml-2">Loading settings...</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Data Retention Section */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Clock className="h-5 w-5" />
            Data Retention
          </CardTitle>
          <CardDescription>
            Choose how to manage old statistics data. This affects database size and query
            performance.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-3">
            {retentionOptions.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => setRetentionMode(option.value)}
                className={cn(
                  'flex items-start gap-4 rounded-lg border p-4 text-left transition-colors',
                  retentionMode === option.value
                    ? 'border-primary bg-primary/5'
                    : 'border-border hover:bg-muted/50'
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
                    <span
                      className={cn(
                        'font-medium',
                        retentionMode === option.value && 'text-primary'
                      )}
                    >
                      {option.title}
                    </span>
                    {option.value === 'archive' && (
                      <span className="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        Recommended
                      </span>
                    )}
                  </div>
                  <p className="mt-1 text-sm text-muted-foreground">{option.description}</p>
                </div>
                <div
                  className={cn(
                    'mt-1 h-4 w-4 rounded-full border-2',
                    retentionMode === option.value
                      ? 'border-primary bg-primary'
                      : 'border-muted-foreground/50'
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
              <Label htmlFor="retention-days">Retention Period</Label>
              <div className="flex items-center gap-3">
                <Input
                  id="retention-days"
                  type="number"
                  min={30}
                  max={365}
                  value={retentionDays as number}
                  onChange={(e) => setRetentionDays(parseInt(e.target.value) || 180)}
                  className="w-24"
                />
                <span className="text-sm text-muted-foreground">days</span>
              </div>
              <p className="text-xs text-muted-foreground">
                {retentionMode === 'delete'
                  ? 'All visitor, session, and view data older than this will be permanently deleted.'
                  : 'A backup will be created automatically, then raw session and view data will be removed.'}
              </p>
            </div>
          )}

          {retentionMode === 'archive' && (
            <div className="rounded-lg border bg-muted/50 p-4">
              <div className="flex gap-3">
                <Info className="h-5 w-5 text-muted-foreground flex-shrink-0 mt-0.5" />
                <p className="text-sm text-muted-foreground">
                  When using Archive mode, automatic backups are created before data is deleted.
                  You can manage these backups in{' '}
                  <Link to="/tools/backups" className="font-medium underline underline-offset-4">
                    Tools &rarr; Backups
                  </Link>
                  .
                </p>
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Danger Zone */}
      <Card className="border-destructive/50">
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-destructive">
            <AlertTriangle className="h-5 w-5" />
            Danger Zone
          </CardTitle>
          <CardDescription>
            These actions are irreversible. Please proceed with caution.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label>Apply Retention Policy Now</Label>
              <p className="text-sm text-muted-foreground">
                Immediately apply the retention policy to existing data.
                {retentionMode === 'forever'
                  ? ' (Disabled - retention mode is set to "Keep forever")'
                  : ` Data older than ${retentionDays} days will be ${retentionMode === 'delete' ? 'deleted' : 'archived'}.`}
              </p>
            </div>
            <Button
              variant="destructive"
              size="sm"
              onClick={applyRetentionNow}
              disabled={isPurging || retentionMode === 'forever'}
            >
              {isPurging ? (
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
              ) : (
                <Trash2 className="mr-2 h-4 w-4" />
              )}
              Apply Now
            </Button>
          </div>
        </CardContent>
      </Card>

      {settings.error && (
        <div className="rounded-md bg-destructive/15 p-3 text-sm text-destructive">
          {settings.error}
        </div>
      )}

      <div className="flex justify-end">
        <Button onClick={handleSave} disabled={settings.isSaving}>
          {settings.isSaving && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          Save Changes
        </Button>
      </div>
    </div>
  )
}
