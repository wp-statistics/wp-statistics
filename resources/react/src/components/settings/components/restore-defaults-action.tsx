import { __ } from '@wordpress/i18n'
import { Loader2, RotateCcw } from 'lucide-react'
import * as React from 'react'

import { SettingsActionField } from '@/components/settings-ui'
import { Button } from '@/components/ui/button'
import { ConfirmDialog } from '@/components/ui/confirm-dialog'
import { useToast } from '@/hooks/use-toast'
import { WordPress } from '@/lib/wordpress'

/**
 * Restore Default Settings action with confirmation dialog.
 * Registered as a `type: 'component'` field in the advanced danger-zone card.
 */
export function RestoreDefaultsAction() {
  const [isResetting, setIsResetting] = React.useState(false)
  const [showResetDialog, setShowResetDialog] = React.useState(false)
  const { toast } = useToast()

  const handleRestoreDefaults = async () => {
    setShowResetDialog(false)
    setIsResetting(true)
    try {
      const wp = WordPress.getInstance()
      const response = await fetch(
        `${wp.getAjaxUrl()}?action=wps_restore_defaults&nonce=${wp.getNonce()}`,
        { method: 'POST', credentials: 'same-origin' }
      )
      if (response.ok) {
        toast({
          title: __('Settings restored', 'wp-statistics'),
          description: __('Settings have been restored to defaults. The page will now reload.', 'wp-statistics'),
        })
        setTimeout(() => window.location.reload(), 1500)
      } else {
        toast({
          title: __('Error', 'wp-statistics'),
          description: __('Failed to restore settings. Please try again.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to restore settings. Please try again.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsResetting(false)
    }
  }

  return (
    <>
      <SettingsActionField
        label={__('Restore Default Settings', 'wp-statistics')}
        description={__('Reset all WP Statistics settings to their default values. Your statistics data will not be affected.', 'wp-statistics')}
      >
        <Button
          variant="destructive"
          size="sm"
          onClick={() => setShowResetDialog(true)}
          disabled={isResetting}
        >
          {isResetting ? (
            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
          ) : (
            <RotateCcw className="mr-2 h-4 w-4" />
          )}
          {__('Restore Defaults', 'wp-statistics')}
        </Button>
      </SettingsActionField>

      <ConfirmDialog
        open={showResetDialog}
        onOpenChange={setShowResetDialog}
        title={__('Restore Default Settings', 'wp-statistics')}
        description={__('Are you sure you want to restore all settings to defaults? This cannot be undone.', 'wp-statistics')}
        confirmLabel={__('Restore Defaults', 'wp-statistics')}
        onConfirm={handleRestoreDefaults}
      />
    </>
  )
}
