import * as React from 'react'
import { Loader2 } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { Textarea } from '@/components/ui/textarea'
import { useSettings, useSetting } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'

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
  const [showPrivacyIssues, setShowPrivacyIssues] = useSetting(
    settings,
    'show_privacy_issues_in_report',
    false
  )

  const handleSave = async () => {
    const success = await settings.save()
    if (success) {
      toast({
        title: 'Settings Saved',
        description: 'Notification settings have been updated.',
      })
    }
  }

  const handlePreviewEmail = async () => {
    setIsPreviewLoading(true)
    try {
      const formData = new FormData()
      formData.append('action', 'wp_statistics_email_preview')
      formData.append('wps_nonce', (window as any).wps_react?.globals?.restNonce || '')

      const response = await fetch(
        (window as any).wps_react?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php',
        {
          method: 'POST',
          body: formData,
        }
      )

      const data = await response.json()
      if (data.success && data.data.html) {
        // Open preview in new window
        const previewWindow = window.open('', '_blank', 'width=700,height=800')
        if (previewWindow) {
          previewWindow.document.write(data.data.html)
          previewWindow.document.close()
        }
      } else {
        toast({
          title: 'Preview Error',
          description: data.data?.message || 'Unable to generate preview.',
          variant: 'destructive',
        })
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'An error occurred while generating preview.',
        variant: 'destructive',
      })
    } finally {
      setIsPreviewLoading(false)
    }
  }

  const handleSendTestEmail = async () => {
    setIsSendingTest(true)
    try {
      const formData = new FormData()
      formData.append('action', 'wp_statistics_email_send_test')
      formData.append('wps_nonce', (window as any).wps_react?.globals?.restNonce || '')

      const response = await fetch(
        (window as any).wps_react?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php',
        {
          method: 'POST',
          body: formData,
        }
      )

      const data = await response.json()
      if (data.success) {
        toast({
          title: 'Test Email Sent',
          description: `A test email has been sent to ${data.data.email}.`,
        })
      } else {
        toast({
          title: 'Error',
          description: data.data?.message || 'Failed to send test email.',
          variant: 'destructive',
        })
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'An error occurred while sending the test email.',
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
        <span className="ml-2">Loading settings...</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Email Reports</CardTitle>
          <CardDescription>
            Configure automated email reports with your site statistics.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="report-frequency">Report Frequency</Label>
              <Select value={timeReport as string} onValueChange={setTimeReport}>
                <SelectTrigger id="report-frequency">
                  <SelectValue placeholder="Select frequency" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="0">Disabled</SelectItem>
                  <SelectItem value="daily">Daily</SelectItem>
                  <SelectItem value="weekly">Weekly</SelectItem>
                  <SelectItem value="biweekly">Bi-weekly</SelectItem>
                  <SelectItem value="monthly">Monthly</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="delivery-method">Delivery Method</Label>
              <Select value={sendReport as string} onValueChange={setSendReport}>
                <SelectTrigger id="delivery-method">
                  <SelectValue placeholder="Select method" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="0">Please select</SelectItem>
                  <SelectItem value="mail">Email</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="email-list">Email Recipients</Label>
            <Input
              id="email-list"
              type="text"
              placeholder="admin@example.com, user@example.com"
              value={emailList as string}
              onChange={(e) => setEmailList(e.target.value)}
            />
            <p className="text-xs text-muted-foreground">
              Enter comma-separated email addresses to receive reports.
            </p>
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="privacy-issues">Show Privacy Issues in Report</Label>
              <p className="text-sm text-muted-foreground">
                Include privacy audit results in email reports.
              </p>
            </div>
            <Switch
              id="privacy-issues"
              checked={!!showPrivacyIssues}
              onCheckedChange={setShowPrivacyIssues}
            />
          </div>

          <div className="rounded-lg border bg-muted/50 p-4">
            <h4 className="text-sm font-medium mb-2">Enhanced Visual Report</h4>
            <p className="text-sm text-muted-foreground">
              For graphical representations of your data, explore our{' '}
              <a
                href="https://wp-statistics.com/add-ons/wp-statistics-advanced-reporting/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings"
                target="_blank"
                rel="noopener noreferrer"
                className="text-primary hover:underline"
              >
                Advanced Reporting Add-on
              </a>{' '}
              for additional chart and graph options in your email reports.
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Email Content</CardTitle>
          <CardDescription>
            Customize the content of your email reports. You can use shortcodes to display statistics.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="space-y-2">
            <Label htmlFor="email-header">Custom Header Content</Label>
            <Textarea
              id="email-header"
              placeholder="Enter custom header text..."
              value={emailHeader as string}
              onChange={(e) => setEmailHeader(e.target.value)}
              rows={3}
            />
            <p className="text-xs text-muted-foreground">
              This text will appear at the top of your email reports.
            </p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="content-report">Report Content</Label>
            <Textarea
              id="content-report"
              placeholder="Enter report content with shortcodes..."
              value={contentReport as string}
              onChange={(e) => setContentReport(e.target.value)}
              rows={6}
            />
            <p className="text-xs text-muted-foreground">
              Customize the main content of your email report. Shortcodes are supported.
            </p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="email-footer">Custom Footer Content</Label>
            <Textarea
              id="email-footer"
              placeholder="Enter custom footer text..."
              value={emailFooter as string}
              onChange={(e) => setEmailFooter(e.target.value)}
              rows={3}
            />
            <p className="text-xs text-muted-foreground">
              This text will appear at the bottom of your email reports.
            </p>
          </div>

          <div className="flex gap-2">
            <Button
              variant="outline"
              className="flex-1"
              onClick={handlePreviewEmail}
              disabled={isPreviewLoading}
            >
              {isPreviewLoading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              Preview Email
            </Button>
            <Button
              variant="outline"
              className="flex-1"
              onClick={handleSendTestEmail}
              disabled={isSendingTest}
            >
              {isSendingTest && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              Send Test Email
            </Button>
          </div>
        </CardContent>
      </Card>

      {settings.error && (
        <NoticeBanner
          id="settings-error"
          message={settings.error}
          type="error"
          dismissible={false}
        />
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
