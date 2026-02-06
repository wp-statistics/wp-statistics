import { __ } from '@wordpress/i18n'
import { AlertTriangle, Globe, Loader2, RotateCcw, Server } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { useSetting, useSettings } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'
import { WordPress } from '@/lib/wordpress'

export function AdvancedSettings() {
  const settings = useSettings({ tab: 'advanced' })
  const [detectedIp, setDetectedIp] = React.useState<string>(__('Loading...', 'wp-statistics'))
  const [externalIp, setExternalIp] = React.useState<string>(__('Loading...', 'wp-statistics'))
  const [isResetting, setIsResetting] = React.useState(false)
  const [showResetDialog, setShowResetDialog] = React.useState(false)
  const { toast } = useToast()

  // Fetch detected IP on mount
  React.useEffect(() => {
    // Get WP Statistics detected IP from localized data
    const wpStatsIp = (window as any).wps_react?.globals?.userIp
    if (wpStatsIp) {
      setDetectedIp(wpStatsIp)
    } else {
      setDetectedIp(__('Not available', 'wp-statistics'))
    }

    // Fetch external IP from ipify
    fetch('https://api.ipify.org?format=json')
      .then((res) => res.json())
      .then((data) => setExternalIp(data.ip))
      .catch(() => setExternalIp(__('Unable to detect', 'wp-statistics')))
  }, [])

  // IP Detection Settings
  const [ipMethod, setIpMethod] = useSetting(settings, 'ip_method', 'sequential')
  const [customHeaderIpMethod, setCustomHeaderIpMethod] = useSetting(settings, 'user_custom_header_ip_method', '')

  // GeoIP Settings
  const [geoipLicenseType, setGeoipLicenseType] = useSetting(settings, 'geoip_license_type', 'js-deliver')
  const [geoipLicenseKey, setGeoipLicenseKey] = useSetting(settings, 'geoip_license_key', '')
  const [geoipDbipLicenseKey, setGeoipDbipLicenseKey] = useSetting(settings, 'geoip_dbip_license_key_option', '')
  const [geoipDetectionMethod, setGeoipDetectionMethod] = useSetting(
    settings,
    'geoip_location_detection_method',
    'maxmind'
  )
  const [scheduleGeoip, setScheduleGeoip] = useSetting(settings, 'schedule_geoip', false)
  const [autoPop, setAutoPop] = useSetting(settings, 'auto_pop', false)
  const [privateCountryCode, setPrivateCountryCode] = useSetting(settings, 'private_country_code', '000')

  // Database Settings
  const [deleteOnUninstall, setDeleteOnUninstall] = useSetting(settings, 'delete_data_on_uninstall', false)

  // Content Analytics Settings
  const [wordCountAnalytics, setWordCountAnalytics] = useSetting(settings, 'word_count_analytics', false)

  // Other Settings
  const [shareAnonymousData, setShareAnonymousData] = useSetting(settings, 'share_anonymous_data', false)

  const handleSave = async () => {
    const success = await settings.save()
    if (success) {
      toast({
        title: __('Settings saved', 'wp-statistics'),
        description: __('Advanced settings have been updated.', 'wp-statistics'),
      })
    }
  }

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
    } catch (error) {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to restore settings. Please try again.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsResetting(false)
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
          <CardTitle>{__('IP Detection', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Configure how visitor IP addresses are detected.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="space-y-2">
            <Label htmlFor="ip-method">{__('IP Detection Method', 'wp-statistics')}</Label>
            <Select value={ipMethod as string} onValueChange={setIpMethod}>
              <SelectTrigger id="ip-method">
                <SelectValue placeholder={__('Select method', 'wp-statistics')} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="sequential">{__('Sequential (Check All Headers)', 'wp-statistics')}</SelectItem>
                <SelectItem value="REMOTE_ADDR">REMOTE_ADDR</SelectItem>
                <SelectItem value="HTTP_CLIENT_IP">HTTP_CLIENT_IP</SelectItem>
                <SelectItem value="HTTP_X_FORWARDED_FOR">HTTP_X_FORWARDED_FOR</SelectItem>
                <SelectItem value="HTTP_X_FORWARDED">HTTP_X_FORWARDED</SelectItem>
                <SelectItem value="HTTP_FORWARDED_FOR">HTTP_FORWARDED_FOR</SelectItem>
                <SelectItem value="HTTP_FORWARDED">HTTP_FORWARDED</SelectItem>
                <SelectItem value="HTTP_X_REAL_IP">HTTP_X_REAL_IP</SelectItem>
                <SelectItem value="HTTP_CF_CONNECTING_IP">{__('HTTP_CF_CONNECTING_IP (Cloudflare)', 'wp-statistics')}</SelectItem>
                <SelectItem value="custom">{__('Custom Header', 'wp-statistics')}</SelectItem>
              </SelectContent>
            </Select>
            <p className="text-xs text-muted-foreground">
              {__("Select how visitor IP addresses should be detected. Use 'Sequential' to check all headers automatically, or specify a specific header for your server configuration.", 'wp-statistics')}
            </p>
          </div>

          {ipMethod === 'custom' && (
            <div className="space-y-2">
              <Label htmlFor="custom-header">{__('Custom Header Name', 'wp-statistics')}</Label>
              <Input
                id="custom-header"
                type="text"
                placeholder="HTTP_X_CUSTOM_IP"
                value={customHeaderIpMethod as string}
                onChange={(e) => setCustomHeaderIpMethod(e.target.value)}
              />
              <p className="text-xs text-muted-foreground">
                {__("Enter the custom server header name that contains the visitor's IP address.", 'wp-statistics')}
              </p>
            </div>
          )}

          <div className="rounded-lg border bg-muted/50 p-4">
            <h4 className="text-sm font-medium mb-3">{__('Your IP Information', 'wp-statistics')}</h4>
            <div className="grid gap-3 sm:grid-cols-2">
              <div className="flex items-center gap-2">
                <Server className="h-4 w-4 text-muted-foreground" />
                <div>
                  <p className="text-xs text-muted-foreground">{__('WP Statistics Detected', 'wp-statistics')}</p>
                  <p className="text-sm font-mono">{detectedIp}</p>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <Globe className="h-4 w-4 text-muted-foreground" />
                <div>
                  <p className="text-xs text-muted-foreground">{__('External IP (ipify.org)', 'wp-statistics')}</p>
                  <p className="text-sm font-mono">{externalIp}</p>
                </div>
              </div>
            </div>
            {detectedIp !== __('Loading...', 'wp-statistics') && externalIp !== __('Loading...', 'wp-statistics') && detectedIp !== externalIp && (
              <div className="mt-3 flex items-start gap-2 rounded-md bg-amber-500/10 p-2">
                <AlertTriangle className="h-4 w-4 text-amber-500 mt-0.5" />
                <p className="text-xs text-amber-600 dark:text-amber-400">
                  {__("The IPs don't match. If you're behind a proxy or CDN, ensure the correct header is selected above.", 'wp-statistics')}
                </p>
              </div>
            )}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('GeoIP Settings', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Configure how visitor locations are detected and displayed.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="geoip-detection">{__('Location Detection Method', 'wp-statistics')}</Label>
              <Select value={geoipDetectionMethod as string} onValueChange={setGeoipDetectionMethod}>
                <SelectTrigger id="geoip-detection">
                  <SelectValue placeholder={__('Select method', 'wp-statistics')} />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="maxmind">{__('MaxMind GeoIP', 'wp-statistics')}</SelectItem>
                  <SelectItem value="dbip">{__('DB-IP', 'wp-statistics')}</SelectItem>
                  <SelectItem value="cf">{__('Cloudflare IP Geolocation', 'wp-statistics')}</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="geoip-source">{__('Database Update Source', 'wp-statistics')}</Label>
              <Select value={geoipLicenseType as string} onValueChange={setGeoipLicenseType}>
                <SelectTrigger id="geoip-source">
                  <SelectValue placeholder={__('Select source', 'wp-statistics')} />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="js-deliver">{__('JsDelivr (Free)', 'wp-statistics')}</SelectItem>
                  <SelectItem value="user-license">{__('Custom License Key', 'wp-statistics')}</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          {geoipLicenseType === 'user-license' && (
            <div className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="maxmind-license">{__('MaxMind License Key', 'wp-statistics')}</Label>
                <Input
                  id="maxmind-license"
                  type="text"
                  placeholder={__('Enter your MaxMind license key', 'wp-statistics')}
                  value={geoipLicenseKey as string}
                  onChange={(e) => setGeoipLicenseKey(e.target.value)}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="dbip-license">{__('DB-IP License Key', 'wp-statistics')}</Label>
                <Input
                  id="dbip-license"
                  type="text"
                  placeholder={__('Enter your DB-IP license key', 'wp-statistics')}
                  value={geoipDbipLicenseKey as string}
                  onChange={(e) => setGeoipDbipLicenseKey(e.target.value)}
                />
              </div>
            </div>
          )}

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="schedule-geoip">{__('Auto-Update GeoIP Database', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__('Automatically download the latest GeoIP database weekly.', 'wp-statistics')}</p>
            </div>
            <Switch id="schedule-geoip" checked={!!scheduleGeoip} onCheckedChange={setScheduleGeoip} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="auto-pop">{__('Auto-Fill Missing Locations', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Automatically fill in location data for visitors with incomplete records.', 'wp-statistics')}
              </p>
            </div>
            <Switch id="auto-pop" checked={!!autoPop} onCheckedChange={setAutoPop} />
          </div>

          <div className="space-y-2">
            <Label htmlFor="private-country">{__('Private IP Country Code', 'wp-statistics')}</Label>
            <Input
              id="private-country"
              type="text"
              maxLength={3}
              placeholder="000"
              value={privateCountryCode as string}
              onChange={(e) => setPrivateCountryCode(e.target.value)}
              className="w-24"
            />
            <p className="text-xs text-muted-foreground">{__('Country code to use for private/local IP addresses.', 'wp-statistics')}</p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('Content Analytics', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Configure content analysis features.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="word-count-analytics">{__('Word Count Analytics', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Calculate and store word count for posts to enable reading time estimates and content length analytics.', 'wp-statistics')}
              </p>
            </div>
            <Switch id="word-count-analytics" checked={!!wordCountAnalytics} onCheckedChange={setWordCountAnalytics} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('Anonymous Data Sharing', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Help improve WP Statistics.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="share-anonymous">{__('Share Anonymous Usage Data', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Help us improve WP Statistics by sharing anonymous usage data.', 'wp-statistics')}
              </p>
            </div>
            <Switch id="share-anonymous" checked={!!shareAnonymousData} onCheckedChange={setShareAnonymousData} />
          </div>
        </CardContent>
      </Card>

      <Card className="border-destructive/50">
        <CardHeader>
          <CardTitle className="text-destructive">{__('Danger Zone', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('These actions are irreversible. Please proceed with caution.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="delete-on-uninstall">{__('Delete All Data on Uninstall', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Remove all WP Statistics data from the database when the plugin is uninstalled. This action cannot be undone.', 'wp-statistics')}
              </p>
            </div>
            <Switch id="delete-on-uninstall" checked={!!deleteOnUninstall} onCheckedChange={setDeleteOnUninstall} />
          </div>

          <div className="border-t pt-6">
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>{__('Restore Default Settings', 'wp-statistics')}</Label>
                <p className="text-sm text-muted-foreground">
                  {__('Reset all WP Statistics settings to their default values. Your statistics data will not be affected.', 'wp-statistics')}
                </p>
              </div>
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
            </div>
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

      {/* Restore Defaults Confirmation Dialog */}
      <Dialog open={showResetDialog} onOpenChange={setShowResetDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <AlertTriangle className="h-5 w-5 text-destructive" />
              {__('Restore Default Settings', 'wp-statistics')}
            </DialogTitle>
            <DialogDescription>
              {__('Are you sure you want to restore all settings to defaults? This cannot be undone.', 'wp-statistics')}
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" onClick={() => setShowResetDialog(false)}>
              {__('Cancel', 'wp-statistics')}
            </Button>
            <Button variant="destructive" onClick={handleRestoreDefaults}>
              {__('Restore Defaults', 'wp-statistics')}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}
