import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { useSetting, useSettings } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'

export function DisplaySettings() {
  const settings = useSettings({ tab: 'display' })
  const { toast } = useToast()

  // Admin Interface Settings
  const [disableEditor, setDisableEditor] = useSetting(settings, 'disable_editor', false)
  const [disableColumn, setDisableColumn] = useSetting(settings, 'disable_column', false)
  const [enableUserColumn, setEnableUserColumn] = useSetting(settings, 'enable_user_column', false)
  const [menuBar, setMenuBar] = useSetting(settings, 'menu_bar', false)
  const [disableDashboard, setDisableDashboard] = useSetting(settings, 'disable_dashboard', false)
  const [displayNotifications, setDisplayNotifications] = useSetting(settings, 'display_notifications', true)
  const [hideNotices, setHideNotices] = useSetting(settings, 'hide_notices', false)

  // Frontend Display Settings
  const [showHits, setShowHits] = useSetting(settings, 'show_hits', false)
  const [displayHitsPosition, setDisplayHitsPosition] = useSetting(settings, 'display_hits_position', 'none')

  const handleSave = async () => {
    const success = await settings.save()
    if (success) {
      toast({
        title: __('Settings saved', 'wp-statistics'),
        description: __('Display settings have been updated.', 'wp-statistics'),
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
          <CardTitle>{__('Admin Interface', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Configure how WP Statistics appears in the WordPress admin area.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="disable-editor">{__('View Stats in Editor', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Show a summary of content view statistics in the post editor.', 'wp-statistics')}
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
              <Label htmlFor="disable-column">{__('Stats Column in Content List', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Display the statistics column in the content list menus, showing page view or visitor counts.', 'wp-statistics')}
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
              <Label htmlFor="enable-user-column">{__('Views Column in User List', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Display the "Views" column in the admin user list. Requires "Track Logged-In User Activity" to be enabled.', 'wp-statistics')}
              </p>
            </div>
            <Switch id="enable-user-column" checked={!!enableUserColumn} onCheckedChange={setEnableUserColumn} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="menu-bar">{__('Show Stats in Admin Menu Bar', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__("View your site's statistics directly from the WordPress admin menu bar.", 'wp-statistics')}
              </p>
            </div>
            <Switch id="menu-bar" checked={!!menuBar} onCheckedChange={setMenuBar} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="disable-dashboard">{__('WP Statistics Widgets in Dashboard', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__('View WP Statistics widgets in the WordPress dashboard.', 'wp-statistics')}</p>
            </div>
            <Switch
              id="disable-dashboard"
              checked={!disableDashboard}
              onCheckedChange={(checked) => setDisableDashboard(!checked)}
            />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="display-notifications">{__('WP Statistics Notifications', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Display important notifications such as new version releases, feature updates, and news.', 'wp-statistics')}
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
              <Label htmlFor="hide-notices">{__('Disable Admin Notices', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Hides configuration and optimization notices in the admin area. Critical database notices will still be shown.', 'wp-statistics')}
              </p>
            </div>
            <Switch id="hide-notices" checked={!!hideNotices} onCheckedChange={setHideNotices} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('Frontend Display', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Configure how statistics appear on your website frontend.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="show-hits">{__('Views in Single Contents', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__("Shows the view count on the content's page for visitor insight.", 'wp-statistics')}
              </p>
            </div>
            <Switch id="show-hits" checked={!!showHits} onCheckedChange={setShowHits} />
          </div>

          {showHits && (
            <div className="space-y-2">
              <Label htmlFor="display-hits-position">{__('Display Position', 'wp-statistics')}</Label>
              <Select value={displayHitsPosition as string} onValueChange={setDisplayHitsPosition}>
                <SelectTrigger id="display-hits-position" className="w-[200px]">
                  <SelectValue placeholder={__('Select position', 'wp-statistics')} />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="none">{__('Please select', 'wp-statistics')}</SelectItem>
                  <SelectItem value="before_content">{__('Before Content', 'wp-statistics')}</SelectItem>
                  <SelectItem value="after_content">{__('After Content', 'wp-statistics')}</SelectItem>
                </SelectContent>
              </Select>
              <p className="text-xs text-muted-foreground">{__('Choose the position to show views on your content pages.', 'wp-statistics')}</p>
            </div>
          )}
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
