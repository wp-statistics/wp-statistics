import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { Textarea } from '@/components/ui/textarea'
import { useSetting, useSettings } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'
import { previewEmail, sendTestEmail } from '@/services/settings'

export function NotificationSettings() {
  const settings = useSettings({ tab: 'notifications' })
  const [isPreviewLoading, setIsPreviewLoading] = React.useState(false)
  const [isSendingTest, setIsSendingTest] = React.useState(false)
  const { toast } = useToast()

  // Individual settings
  const [timeReport, setTimeReport] = useSetting(settings, 'time_report', '0')
  const [sendReport, setSendReport] = useSetting(settings, 'send_report', '0')
  const [emailList, setEmailList] = useSetting(settings, 'email_list', '')
  const [contentReport, setContentReport] = useSetting(settings, 'content_report', '')
  const [emailHeader, setEmailHeader] = useSetting(settings, 'email_free_content_header', '')
  const [emailFooter, setEmailFooter] = useSetting(settings, 'email_free_content_footer', '')
  const [showPrivacyIssues, setShowPrivacyIssues] = useSetting(settings, 'show_privacy_issues_in_report', false)

  const handleSave = async () => {
    const success = await settings.save()
    if (success) {
      toast({
        title: __('Settings saved', 'wp-statistics'),
        description: __('Notification settings have been updated.', 'wp-statistics'),
      })
    }
  }

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
    } catch (error) {
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
    } catch (error) {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('An error occurred while sending the test email.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsSendingTest(false)
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
      <Card>
        <CardHeader>
          <CardTitle>{__('Email Reports', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Configure automated email reports with your site statistics.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="report-frequency">{__('Report Frequency', 'wp-statistics')}</Label>
              <Select value={timeReport as string} onValueChange={setTimeReport}>
                <SelectTrigger id="report-frequency">
                  <SelectValue placeholder={__('Select frequency', 'wp-statistics')} />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="0">{__('Disabled', 'wp-statistics')}</SelectItem>
                  <SelectItem value="daily">{__('Daily', 'wp-statistics')}</SelectItem>
                  <SelectItem value="weekly">{__('Weekly', 'wp-statistics')}</SelectItem>
                  <SelectItem value="biweekly">{__('Bi-weekly', 'wp-statistics')}</SelectItem>
                  <SelectItem value="monthly">{__('Monthly', 'wp-statistics')}</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="delivery-method">{__('Delivery Method', 'wp-statistics')}</Label>
              <Select value={sendReport as string} onValueChange={setSendReport}>
                <SelectTrigger id="delivery-method">
                  <SelectValue placeholder={__('Select method', 'wp-statistics')} />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="0">{__('Please select', 'wp-statistics')}</SelectItem>
                  <SelectItem value="mail">{__('Email', 'wp-statistics')}</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="email-list">{__('Email Recipients', 'wp-statistics')}</Label>
            <Input
              id="email-list"
              type="text"
              placeholder="admin@example.com, user@example.com"
              value={emailList as string}
              onChange={(e) => setEmailList(e.target.value)}
            />
            <p className="text-xs text-muted-foreground">{__('Enter comma-separated email addresses to receive reports.', 'wp-statistics')}</p>
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="privacy-issues">{__('Show Privacy Issues in Report', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__('Include privacy audit results in email reports.', 'wp-statistics')}</p>
            </div>
            <Switch id="privacy-issues" checked={!!showPrivacyIssues} onCheckedChange={setShowPrivacyIssues} />
          </div>

          <div className="rounded-lg border bg-muted/50 p-4">
            <h4 className="text-sm font-medium mb-2">{__('Enhanced Visual Report', 'wp-statistics')}</h4>
            <p className="text-sm text-muted-foreground">
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
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('Email Content', 'wp-statistics')}</CardTitle>
          <CardDescription>
            {__('Customize the content of your email reports. You can use shortcodes to display statistics.', 'wp-statistics')}
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="space-y-2">
            <Label htmlFor="email-header">{__('Custom Header Content', 'wp-statistics')}</Label>
            <Textarea
              id="email-header"
              placeholder={__('Enter custom header text...', 'wp-statistics')}
              value={emailHeader as string}
              onChange={(e) => setEmailHeader(e.target.value)}
              rows={3}
            />
            <p className="text-xs text-muted-foreground">{__('This text will appear at the top of your email reports.', 'wp-statistics')}</p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="content-report">{__('Report Content', 'wp-statistics')}</Label>
            <Textarea
              id="content-report"
              placeholder={__('Enter report content with shortcodes...', 'wp-statistics')}
              value={contentReport as string}
              onChange={(e) => setContentReport(e.target.value)}
              rows={6}
            />
            <p className="text-xs text-muted-foreground">
              {__('Customize the main content of your email report. Shortcodes are supported.', 'wp-statistics')}
            </p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="email-footer">{__('Custom Footer Content', 'wp-statistics')}</Label>
            <Textarea
              id="email-footer"
              placeholder={__('Enter custom footer text...', 'wp-statistics')}
              value={emailFooter as string}
              onChange={(e) => setEmailFooter(e.target.value)}
              rows={3}
            />
            <p className="text-xs text-muted-foreground">{__('This text will appear at the bottom of your email reports.', 'wp-statistics')}</p>
          </div>

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
        </CardContent>
      </Card>

      {settings.error && <NoticeBanner id="settings-error" message={settings.error} type="error" dismissible={false} />}

      <div className="flex justify-end">
        <Button onClick={handleSave} disabled={settings.isSaving}>
          {settings.isSaving && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          {__('Save Changes', 'wp-statistics')}
        </Button>
      </div>
    </div>
  )
}
