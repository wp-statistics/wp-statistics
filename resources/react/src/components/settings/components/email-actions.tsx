import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { SettingsActionField } from '@/components/settings-ui'
import { Button } from '@/components/ui/button'
import type { UseSettingsReturn } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'
import { previewEmail, sendTestEmail } from '@/services/settings'

/**
 * Preview & Send Test Email action buttons.
 * Registered as a `type: 'component'` field in the notifications tab.
 */
export function EmailActions({ settings }: { settings: UseSettingsReturn }) {
  const [isPreviewLoading, setIsPreviewLoading] = React.useState(false)
  const [isSendingTest, setIsSendingTest] = React.useState(false)
  const { toast } = useToast()

  const emailList = settings.getValue('email_list', '') as string

  const handlePreviewEmail = async () => {
    setIsPreviewLoading(true)
    try {
      const result = await previewEmail()
      if (result.success && result.html) {
        const previewWindow = window.open('', '_blank', 'width=700,height=800')
        if (previewWindow) {
          previewWindow.document.write(result.html)
          previewWindow.document.close()
        }
      } else {
        toast({
          title: __('Preview Error', 'wp-statistics'),
          description: result.message || __('Unable to generate preview.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('An error occurred while generating preview.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsPreviewLoading(false)
    }
  }

  const handleSendTestEmail = async () => {
    setIsSendingTest(true)
    try {
      const result = await sendTestEmail(emailList)
      if (result.success) {
        toast({
          title: __('Test Email Sent', 'wp-statistics'),
          description: result.email ? `${__('A test email has been sent to', 'wp-statistics')} ${result.email}.` : undefined,
        })
      } else {
        toast({
          title: __('Error', 'wp-statistics'),
          description: result.message || __('Failed to send test email.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('An error occurred while sending the test email.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsSendingTest(false)
    }
  }

  return (
    <SettingsActionField
      label={__('Email Actions', 'wp-statistics')}
      description={__('Preview or send a test email to verify your configuration.', 'wp-statistics')}
    >
      <div className="flex gap-2">
        <Button variant="outline" size="sm" onClick={handlePreviewEmail} disabled={isPreviewLoading}>
          {isPreviewLoading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          {__('Preview Email', 'wp-statistics')}
        </Button>
        <Button variant="outline" size="sm" onClick={handleSendTestEmail} disabled={isSendingTest}>
          {isSendingTest && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          {__('Send Test Email', 'wp-statistics')}
        </Button>
      </div>
    </SettingsActionField>
  )
}
