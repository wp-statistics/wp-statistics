import * as React from 'react'
import { Loader2 } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { useToast } from '@/hooks/use-toast'

import { EmailBuilderDialog } from '../email-builder'

export function NotificationSettings() {
  const [isBuilderOpen, setIsBuilderOpen] = React.useState(false)
  const [isPreviewLoading, setIsPreviewLoading] = React.useState(false)
  const [isSendingTest, setIsSendingTest] = React.useState(false)
  const { toast } = useToast()

  const handlePreviewEmail = async () => {
    setIsPreviewLoading(true)
    try {
      const formData = new FormData()
      formData.append('action', 'wp_statistics_email_preview')
      formData.append('_wpnonce', (window as any).wps_react?.globals?.nonce || '')

      const response = await fetch((window as any).wps_react?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
      })

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
      formData.append('_wpnonce', (window as any).wps_react?.globals?.nonce || '')

      const response = await fetch((window as any).wps_react?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
      })

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
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="enable-reports">Enable Email Reports</Label>
              <p className="text-sm text-muted-foreground">
                Receive periodic statistics reports via email.
              </p>
            </div>
            <Switch id="enable-reports" />
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="report-frequency">Report Frequency</Label>
              <Select defaultValue="weekly">
                <SelectTrigger id="report-frequency">
                  <SelectValue placeholder="Select frequency" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="daily">Daily</SelectItem>
                  <SelectItem value="weekly">Weekly</SelectItem>
                  <SelectItem value="biweekly">Bi-weekly</SelectItem>
                  <SelectItem value="monthly">Monthly</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="delivery-method">Delivery Method</Label>
              <Select defaultValue="email">
                <SelectTrigger id="delivery-method">
                  <SelectValue placeholder="Select method" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="email">Email</SelectItem>
                  <SelectItem value="slack" disabled>Slack (Coming soon)</SelectItem>
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
            />
            <p className="text-xs text-muted-foreground">
              Enter comma-separated email addresses to receive reports.
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Email Template</CardTitle>
          <CardDescription>
            Customize the content and appearance of your email reports.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="rounded-lg border border-dashed p-8 text-center">
            <div className="mx-auto flex max-w-[420px] flex-col items-center justify-center text-center">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="h-10 w-10 text-muted-foreground"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={1.5}
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                />
              </svg>
              <h3 className="mt-4 text-lg font-semibold">Email Builder</h3>
              <p className="mt-2 text-sm text-muted-foreground">
                Drag and drop blocks to customize your email report template.
              </p>
              <Button className="mt-4" variant="outline" onClick={() => setIsBuilderOpen(true)}>
                Open Email Builder
              </Button>
            </div>
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

      <EmailBuilderDialog
        open={isBuilderOpen}
        onOpenChange={setIsBuilderOpen}
      />
    </div>
  )
}
