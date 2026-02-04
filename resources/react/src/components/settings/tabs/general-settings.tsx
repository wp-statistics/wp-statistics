import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Switch } from '@/components/ui/switch'
import { useSetting, useSettings } from '@/hooks/use-settings'

export function GeneralSettings() {
  const settings = useSettings({ tab: 'general' })

  // Individual settings
  const [visitorsLog, setVisitorsLog] = useSetting(settings, 'visitors_log', false)
  const [bypassAdBlockers, setBypassAdBlockers] = useSetting(settings, 'bypass_ad_blockers', false)

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
      <Card>
        <CardHeader>
          <CardTitle>Tracking Options</CardTitle>
          <CardDescription>Configure what data WP Statistics collects from your visitors.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="visitors-log">Track Logged-In User Activity</Label>
              <p className="text-sm text-muted-foreground">
                Tracks activities of logged-in users with their WordPress User IDs. If disabled, logged-in users are
                tracked anonymously.
              </p>
            </div>
            <Switch id="visitors-log" checked={!!visitorsLog} onCheckedChange={setVisitorsLog} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Tracker Configuration</CardTitle>
          <CardDescription>Configure how the tracking script works on your site.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="bypass-ad-blockers">Bypass Ad Blockers</Label>
              <p className="text-sm text-muted-foreground">
                Dynamically load the tracking script with a unique name and address to bypass ad blockers.
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
          Save Changes
        </Button>
      </div>
    </div>
  )
}
