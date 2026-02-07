import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { SettingsCard, SettingsField, SettingsInfoBox, SettingsPage, SettingsSelectField, SettingsToggleField } from '@/components/settings-ui'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { useSetting, useSettings } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'
import { previewEmail, sendTestEmail } from '@/services/settings'

export function NotificationSettings() {
  const settings = useSettings({ tab: 'notifications' })
  const [isPreviewLoading, setIsPreviewLoading] = React.useState(false)
  const [isSendingTest, setIsSendingTest] = React.useState(false)
  const { toast } = useToast()

  const [timeReport, setTimeReport] = useSetting(settings, 'time_report', '0')
  const [sendReport, setSendReport] = useSetting(settings, 'send_report', '0')
  const [emailList, setEmailList] = useSetting(settings, 'email_list', '')
  const [contentReport, setContentReport] = useSetting(settings, 'content_report', '')
  const [emailHeader, setEmailHeader] = useSetting(settings, 'email_free_content_header', '')
  const [emailFooter, setEmailFooter] = useSetting(settings, 'email_free_content_footer', '')
  const [showPrivacyIssues, setShowPrivacyIssues] = useSetting(settings, 'show_privacy_issues_in_report', false)

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
      const result = await sendTestEmail()
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
    <SettingsPage settings={settings} saveDescription={__('Notification settings have been updated.', 'wp-statistics')}>
      <SettingsCard
        title={__('Email Reports', 'wp-statistics')}
        description={__('Configure automated email reports with your site statistics.', 'wp-statistics')}
      >
        <div className="grid gap-4 sm:grid-cols-2">
          <SettingsSelectField
            id="report-frequency"
            label={__('Report Frequency', 'wp-statistics')}
            layout="stacked"
            value={timeReport as string}
            onValueChange={setTimeReport}
            placeholder={__('Select frequency', 'wp-statistics')}
            options={[
              { value: '0', label: __('Disabled', 'wp-statistics') },
              { value: 'daily', label: __('Daily', 'wp-statistics') },
              { value: 'weekly', label: __('Weekly', 'wp-statistics') },
              { value: 'biweekly', label: __('Bi-weekly', 'wp-statistics') },
              { value: 'monthly', label: __('Monthly', 'wp-statistics') },
            ]}
          />

          <SettingsSelectField
            id="delivery-method"
            label={__('Delivery Method', 'wp-statistics')}
            layout="stacked"
            value={sendReport as string}
            onValueChange={setSendReport}
            placeholder={__('Select method', 'wp-statistics')}
            options={[
              { value: '0', label: __('Please select', 'wp-statistics') },
              { value: 'mail', label: __('Email', 'wp-statistics') },
            ]}
          />
        </div>

        <SettingsField
          id="email-list"
          label={__('Email Recipients', 'wp-statistics')}
          description={__('Enter comma-separated email addresses to receive reports.', 'wp-statistics')}
          layout="stacked"
        >
          <Input
            id="email-list"
            type="text"
            placeholder="admin@example.com, user@example.com"
            value={emailList as string}
            onChange={(e) => setEmailList(e.target.value)}
          />
        </SettingsField>

        <SettingsToggleField
          id="privacy-issues"
          label={__('Show Privacy Issues in Report', 'wp-statistics')}
          description={__('Include privacy audit results in email reports.', 'wp-statistics')}
          checked={!!showPrivacyIssues}
          onCheckedChange={setShowPrivacyIssues}
        />

        <SettingsInfoBox title={__('Enhanced Visual Report', 'wp-statistics')}>
          <p>
            {__('For graphical representations of your data, explore our', 'wp-statistics')}{' '}
            <a
              href="https://wp-statistics.com/add-ons/wp-statistics-advanced-reporting/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings"
              target="_blank"
              rel="noopener noreferrer"
              className="text-primary hover:underline"
            >
              {__('Advanced Reporting Add-on', 'wp-statistics')}
            </a>{' '}
            {__('for additional chart and graph options in your email reports.', 'wp-statistics')}
          </p>
        </SettingsInfoBox>
      </SettingsCard>

      <SettingsCard
        title={__('Email Content', 'wp-statistics')}
        description={__('Customize the content of your email reports. You can use shortcodes to display statistics.', 'wp-statistics')}
      >
        <SettingsField
          id="email-header"
          label={__('Custom Header Content', 'wp-statistics')}
          description={__('This text will appear at the top of your email reports.', 'wp-statistics')}
          layout="stacked"
        >
          <Textarea
            id="email-header"
            placeholder={__('Enter custom header text...', 'wp-statistics')}
            value={emailHeader as string}
            onChange={(e) => setEmailHeader(e.target.value)}
            rows={3}
          />
        </SettingsField>

        <SettingsField
          id="content-report"
          label={__('Report Content', 'wp-statistics')}
          description={__('Customize the main content of your email report. Shortcodes are supported.', 'wp-statistics')}
          layout="stacked"
        >
          <Textarea
            id="content-report"
            placeholder={__('Enter report content with shortcodes...', 'wp-statistics')}
            value={contentReport as string}
            onChange={(e) => setContentReport(e.target.value)}
            rows={6}
          />
        </SettingsField>

        <SettingsField
          id="email-footer"
          label={__('Custom Footer Content', 'wp-statistics')}
          description={__('This text will appear at the bottom of your email reports.', 'wp-statistics')}
          layout="stacked"
        >
          <Textarea
            id="email-footer"
            placeholder={__('Enter custom footer text...', 'wp-statistics')}
            value={emailFooter as string}
            onChange={(e) => setEmailFooter(e.target.value)}
            rows={3}
          />
        </SettingsField>

        <div className="flex gap-2">
          <Button variant="outline" className="flex-1" onClick={handlePreviewEmail} disabled={isPreviewLoading}>
            {isPreviewLoading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
            {__('Preview Email', 'wp-statistics')}
          </Button>
          <Button variant="outline" className="flex-1" onClick={handleSendTestEmail} disabled={isSendingTest}>
            {isSendingTest && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
            {__('Send Test Email', 'wp-statistics')}
          </Button>
        </div>
      </SettingsCard>
    </SettingsPage>
  )
}
