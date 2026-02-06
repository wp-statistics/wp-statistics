import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Switch } from '@/components/ui/switch'
import { useSetting, useSettings } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'

export function GeneralSettings() {
  const settings = useSettings({ tab: 'general' })
  const { toast } = useToast()

  // Individual settings
  const [visitorsLog, setVisitorsLog] = useSetting(settings, 'visitors_log', false)
  const [bypassAdBlockers, setBypassAdBlockers] = useSetting(settings, 'bypass_ad_blockers', false)

  const handleSave = async () => {
    const success = await settings.save()
    if (success) {
      toast({
        title: __('Settings saved', 'wp-statistics'),
        description: __('General settings have been updated.', 'wp-statistics'),
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
      <Card>
        <CardHeader>
          <CardTitle>{__('Tracking Options', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Configure what data WP Statistics collects from your visitors.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="visitors-log">{__('Track Logged-In User Activity', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Tracks activities of logged-in users with their WordPress User IDs. If disabled, logged-in users are tracked anonymously.', 'wp-statistics')}
              </p>
            </div>
            <Switch id="visitors-log" checked={!!visitorsLog} onCheckedChange={setVisitorsLog} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('Tracker Configuration', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Configure how the tracking script works on your site.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="bypass-ad-blockers">{__('Bypass Ad Blockers', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Dynamically load the tracking script with a unique name and address to bypass ad blockers.', 'wp-statistics')}
              </p>
            </div>
            <Switch id="bypass-ad-blockers" checked={!!bypassAdBlockers} onCheckedChange={setBypassAdBlockers} />
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
