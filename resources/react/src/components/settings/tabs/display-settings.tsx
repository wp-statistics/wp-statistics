import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { useSetting,useSettings } from '@/hooks/use-settings'

export function DisplaySettings() {
  const settings = useSettings({ tab: 'display' })

  // Admin Interface Settings
  const [disableEditor, setDisableEditor] = useSetting(settings, 'disable_editor', false)
  const [disableColumn, setDisableColumn] = useSetting(settings, 'disable_column', false)
  const [enableUserColumn, setEnableUserColumn] = useSetting(settings, 'enable_user_column', false)
  const [menuBar, setMenuBar] = useSetting(settings, 'menu_bar', false)
  const [chartsPreviousPeriod, setChartsPreviousPeriod] = useSetting(settings, 'charts_previous_period', true)
  const [disableDashboard, setDisableDashboard] = useSetting(settings, 'disable_dashboard', false)
  const [displayNotifications, setDisplayNotifications] = useSetting(settings, 'display_notifications', true)
  const [hideNotices, setHideNotices] = useSetting(settings, 'hide_notices', false)

  // Frontend Display Settings
  const [showHits, setShowHits] = useSetting(settings, 'show_hits', false)
  const [displayHitsPosition, setDisplayHitsPosition] = useSetting(settings, 'display_hits_position', 'none')

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
          <CardTitle>Admin Interface</CardTitle>
          <CardDescription>Configure how WP Statistics appears in the WordPress admin area.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="disable-editor">View Stats in Editor</Label>
              <p className="text-sm text-muted-foreground">
                Show a summary of content view statistics in the post editor.
              </p>
            </div>
            <Switch
              id="disable-editor"
              checked={!disableEditor}
              onCheckedChange={(checked) => setDisableEditor(!checked)}
            />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="disable-column">Stats Column in Content List</Label>
              <p className="text-sm text-muted-foreground">
                Display the statistics column in the content list menus, showing page view or visitor counts.
              </p>
            </div>
            <Switch
              id="disable-column"
              checked={!disableColumn}
              onCheckedChange={(checked) => setDisableColumn(!checked)}
            />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="enable-user-column">Views Column in User List</Label>
              <p className="text-sm text-muted-foreground">
                Display the "Views" column in the admin user list. Requires "Track Logged-In User Activity" to be
                enabled.
              </p>
            </div>
            <Switch id="enable-user-column" checked={!!enableUserColumn} onCheckedChange={setEnableUserColumn} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="menu-bar">Show Stats in Admin Menu Bar</Label>
              <p className="text-sm text-muted-foreground">
                View your site's statistics directly from the WordPress admin menu bar.
              </p>
            </div>
            <Switch id="menu-bar" checked={!!menuBar} onCheckedChange={setMenuBar} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="charts-previous-period">Previous Period in Charts</Label>
              <p className="text-sm text-muted-foreground">
                Show data from the previous period in charts for comparison.
              </p>
            </div>
            <Switch
              id="charts-previous-period"
              checked={!!chartsPreviousPeriod}
              onCheckedChange={setChartsPreviousPeriod}
            />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="disable-dashboard">WP Statistics Widgets in Dashboard</Label>
              <p className="text-sm text-muted-foreground">View WP Statistics widgets in the WordPress dashboard.</p>
            </div>
            <Switch
              id="disable-dashboard"
              checked={!disableDashboard}
              onCheckedChange={(checked) => setDisableDashboard(!checked)}
            />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="display-notifications">WP Statistics Notifications</Label>
              <p className="text-sm text-muted-foreground">
                Display important notifications such as new version releases, feature updates, and news.
              </p>
            </div>
            <Switch
              id="display-notifications"
              checked={!!displayNotifications}
              onCheckedChange={setDisplayNotifications}
            />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="hide-notices">Disable Inactive Feature Notices</Label>
              <p className="text-sm text-muted-foreground">
                Stops displaying messages for essential features that are currently switched off.
              </p>
            </div>
            <Switch id="hide-notices" checked={!!hideNotices} onCheckedChange={setHideNotices} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Frontend Display</CardTitle>
          <CardDescription>Configure how statistics appear on your website frontend.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="show-hits">Views in Single Contents</Label>
              <p className="text-sm text-muted-foreground">
                Shows the view count on the content's page for visitor insight.
              </p>
            </div>
            <Switch id="show-hits" checked={!!showHits} onCheckedChange={setShowHits} />
          </div>

          {showHits && (
            <div className="space-y-2">
              <Label htmlFor="display-hits-position">Display Position</Label>
              <Select value={displayHitsPosition as string} onValueChange={setDisplayHitsPosition}>
                <SelectTrigger id="display-hits-position" className="w-[200px]">
                  <SelectValue placeholder="Select position" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="none">Please select</SelectItem>
                  <SelectItem value="before_content">Before Content</SelectItem>
                  <SelectItem value="after_content">After Content</SelectItem>
                </SelectContent>
              </Select>
              <p className="text-xs text-muted-foreground">Choose the position to show views on your content pages.</p>
            </div>
          )}
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
