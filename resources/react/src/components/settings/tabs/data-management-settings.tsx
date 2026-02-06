import { Link } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { AlertTriangle, Archive, Clock, Infinity as InfinityIcon, Info, Loader2, Trash2 } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { useSetting, useSettings } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'
import { cn } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'

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

export function DataManagementSettings() {
  const settings = useSettings({ tab: 'data' })
  const [isPurging, setIsPurging] = React.useState(false)
  const [showPurgeDialog, setShowPurgeDialog] = React.useState(false)
  const { toast } = useToast()

  // Data retention settings
  const [retentionMode, setRetentionMode] = useSetting(settings, 'data_retention_mode', 'forever')
  const [retentionDays, setRetentionDays] = useSetting(settings, 'data_retention_days', 180)

  const applyRetentionNow = async () => {
    setShowPurgeDialog(false)
    setIsPurging(true)
    try {
      const wp = WordPress.getInstance()
      const formData = new FormData()
      formData.append('_wpnonce', wp.getNonce())

      const response = await fetch(`${wp.getAjaxUrl()}?action=wp_statistics_purge_data_now`, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (data.success) {
        toast({
          title: __('Data cleanup completed', 'wp-statistics'),
          description: data.data?.message || __('Data cleanup completed successfully.', 'wp-statistics'),
        })
      } else {
        toast({
          title: __('Error', 'wp-statistics'),
          description: data.data?.message || __('Failed to apply retention policy.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to apply retention policy. Please try again.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsPurging(false)
    }
  }

  const handleSave = async () => {
    const success = await settings.save()
    if (success) {
      toast({
        title: __('Settings saved', 'wp-statistics'),
        description: __('Data management settings have been updated.', 'wp-statistics'),
      })
    }
  }

  if (settings.isLoading) {
    return (
      <div className="flex items-center justify-center p-8">
        <Loader2 className="h-6 w-6 animate-spin" />
        <span className="ml-2">{__('Loading settings...', 'wp-statistics')}</span>
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
            {__('Data Retention', 'wp-statistics')}
          </CardTitle>
          <CardDescription>
            {__('Choose how to manage old statistics data. This affects database size and query performance.', 'wp-statistics')}
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
                  value={retentionDays as number}
                  onChange={(e) => setRetentionDays(parseInt(e.target.value) || 180)}
                  className="w-24"
                />
                <span className="text-sm text-muted-foreground">{__('days', 'wp-statistics')}</span>
              </div>
              <p className="text-xs text-muted-foreground">
                {retentionMode === 'delete'
                  ? __('All visitor, session, and view data older than this will be permanently deleted.', 'wp-statistics')
                  : __('A backup will be created automatically, then raw session and view data will be removed.', 'wp-statistics')}
              </p>
            </div>
          )}

          {retentionMode === 'archive' && (
            <div className="rounded-lg border bg-muted/50 p-4">
              <div className="flex gap-3">
                <Info className="h-5 w-5 text-muted-foreground flex-shrink-0 mt-0.5" />
                <p className="text-sm text-muted-foreground">
                  {__('When using Archive mode, automatic backups are created before data is deleted. You can manage these backups in', 'wp-statistics')}{' '}
                  <Link to="/tools/backups" className="font-medium underline underline-offset-4">
                    {__('Tools', 'wp-statistics')} &rarr; {__('Backups', 'wp-statistics')}
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
            {__('Danger Zone', 'wp-statistics')}
          </CardTitle>
          <CardDescription>{__('These actions are irreversible. Please proceed with caution.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label>{__('Apply Retention Policy Now', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Immediately apply the retention policy to existing data.', 'wp-statistics')}
                {retentionMode === 'forever'
                  ? ` ${__('(Disabled - retention mode is set to "Keep forever")', 'wp-statistics')}`
                  : ` ${__('Data older than', 'wp-statistics')} ${retentionDays} ${__('days will be', 'wp-statistics')} ${retentionMode === 'delete' ? __('deleted', 'wp-statistics') : __('archived', 'wp-statistics')}.`}
              </p>
            </div>
            <Button
              variant="destructive"
              size="sm"
              onClick={() => setShowPurgeDialog(true)}
              disabled={isPurging || retentionMode === 'forever'}
            >
              {isPurging ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Trash2 className="mr-2 h-4 w-4" />}
              {__('Apply Now', 'wp-statistics')}
            </Button>
          </div>
        </CardContent>
      </Card>

      {settings.error && <NoticeBanner id="settings-error" message={settings.error} type="error" dismissible={false} />}

      <div className="flex justify-end">
        <Button onClick={handleSave} disabled={settings.isSaving}>
          {settings.isSaving && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          {__('Save Changes', 'wp-statistics')}
        </Button>
      </div>

      {/* Purge Confirmation Dialog */}
      <Dialog open={showPurgeDialog} onOpenChange={setShowPurgeDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <AlertTriangle className="h-5 w-5 text-destructive" />
              {__('Apply Retention Policy', 'wp-statistics')}
            </DialogTitle>
            <DialogDescription>
              {retentionMode === 'delete'
                ? __('This will permanently delete data older than', 'wp-statistics')
                : __('This will archive and then delete data older than', 'wp-statistics')}{' '}
              {retentionDays} {__('days. This action cannot be undone.', 'wp-statistics')}
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" onClick={() => setShowPurgeDialog(false)}>
              {__('Cancel', 'wp-statistics')}
            </Button>
            <Button variant="destructive" onClick={applyRetentionNow}>
              {__('Apply Now', 'wp-statistics')}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}
