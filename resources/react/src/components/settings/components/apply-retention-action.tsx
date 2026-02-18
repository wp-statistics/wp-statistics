import { __ } from '@wordpress/i18n'
import { Loader2, Trash2 } from 'lucide-react'
import * as React from 'react'

import { SettingsActionField } from '@/components/settings-ui'
import { Button } from '@/components/ui/button'
import { ConfirmDialog } from '@/components/ui/confirm-dialog'
import type { UseSettingsReturn } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'
import { WordPress } from '@/lib/wordpress'

/**
 * "Apply Retention Policy Now" action button with confirmation dialog.
 * Registered as a `type: 'component'` field in the data-management danger-zone card.
 */
export function ApplyRetentionAction({ settings }: { settings: UseSettingsReturn }) {
  const [isPurging, setIsPurging] = React.useState(false)
  const [showPurgeDialog, setShowPurgeDialog] = React.useState(false)
  const { toast } = useToast()

  const retentionMode = settings.getValue('data_retention_mode', 'forever') as string
  const retentionDays = settings.getValue('data_retention_days', 180) as number

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

  return (
    <>
      <SettingsActionField
        label={__('Apply Retention Policy Now', 'wp-statistics')}
        description={
          __('Immediately apply the retention policy to existing data.', 'wp-statistics') +
          (retentionMode === 'forever'
            ? ` ${__('(Disabled - retention mode is set to "Keep forever")', 'wp-statistics')}`
            : ` ${__('Data older than', 'wp-statistics')} ${retentionDays} ${__('days will be', 'wp-statistics')} ${retentionMode === 'delete' ? __('deleted', 'wp-statistics') : __('archived', 'wp-statistics')}.`)
        }
      >
        <Button
          variant="destructive"
          size="sm"
          onClick={() => setShowPurgeDialog(true)}
          disabled={isPurging || retentionMode === 'forever'}
        >
          {isPurging ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Trash2 className="mr-2 h-4 w-4" />}
          {__('Apply Now', 'wp-statistics')}
        </Button>
      </SettingsActionField>

      <ConfirmDialog
        open={showPurgeDialog}
        onOpenChange={setShowPurgeDialog}
        title={__('Apply Retention Policy', 'wp-statistics')}
        description={
          <>
            {retentionMode === 'delete'
              ? __('This will permanently delete data older than', 'wp-statistics')
              : __('This will archive and then delete data older than', 'wp-statistics')}{' '}
            {retentionDays} {__('days. This action cannot be undone.', 'wp-statistics')}
          </>
        }
        confirmLabel={__('Apply Now', 'wp-statistics')}
        onConfirm={applyRetentionNow}
      />
    </>
  )
}
