import * as React from 'react'
import { Loader2, AlertTriangle, RefreshCw, Globe, Server, RotateCcw } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { useSettings, useSetting } from '@/hooks/use-settings'

export function AdvancedSettings() {
  const settings = useSettings({ tab: 'advanced' })
  const [detectedIp, setDetectedIp] = React.useState<string>('Loading...')
  const [externalIp, setExternalIp] = React.useState<string>('Loading...')
  const [isResetting, setIsResetting] = React.useState(false)

  // Fetch detected IP on mount
  React.useEffect(() => {
    // Get WP Statistics detected IP from localized data
    const wpStatsIp = (window as any).wps_react?.globals?.userIp
    if (wpStatsIp) {
      setDetectedIp(wpStatsIp)
    } else {
      setDetectedIp('Not available')
    }

    // Fetch external IP from ipify
    fetch('https://api.ipify.org?format=json')
      .then((res) => res.json())
      .then((data) => setExternalIp(data.ip))
      .catch(() => setExternalIp('Unable to detect'))
  }, [])

  // IP Detection Settings
  const [ipMethod, setIpMethod] = useSetting(settings, 'ip_method', 'sequential')
  const [customHeaderIpMethod, setCustomHeaderIpMethod] = useSetting(
    settings,
    'user_custom_header_ip_method',
    ''
  )

  // GeoIP Settings
  const [geoipLicenseType, setGeoipLicenseType] = useSetting(
    settings,
    'geoip_license_type',
    'js-deliver'
  )
  const [geoipLicenseKey, setGeoipLicenseKey] = useSetting(settings, 'geoip_license_key', '')
  const [geoipDbipLicenseKey, setGeoipDbipLicenseKey] = useSetting(
    settings,
    'geoip_dbip_license_key_option',
    ''
  )
  const [geoipDetectionMethod, setGeoipDetectionMethod] = useSetting(
    settings,
    'geoip_location_detection_method',
    'maxmind'
  )
  const [scheduleGeoip, setScheduleGeoip] = useSetting(settings, 'schedule_geoip', false)
  const [autoPop, setAutoPop] = useSetting(settings, 'auto_pop', false)
  const [privateCountryCode, setPrivateCountryCode] = useSetting(
    settings,
    'private_country_code',
    '000'
  )

  // Database Settings
  const [deleteOnUninstall, setDeleteOnUninstall] = useSetting(
    settings,
    'delete_data_on_uninstall',
    false
  )

  // Content Analytics Settings
  const [wordCountAnalytics, setWordCountAnalytics] = useSetting(
    settings,
    'word_count_analytics',
    false
  )

  // Other Settings
  const [shareAnonymousData, setShareAnonymousData] = useSetting(
    settings,
    'share_anonymous_data',
    false
  )

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
          <CardTitle>IP Detection</CardTitle>
          <CardDescription>
            Configure how visitor IP addresses are detected.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="space-y-2">
            <Label htmlFor="ip-method">IP Detection Method</Label>
            <Select value={ipMethod as string} onValueChange={setIpMethod}>
              <SelectTrigger id="ip-method">
                <SelectValue placeholder="Select method" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="sequential">Sequential (Check All Headers)</SelectItem>
                <SelectItem value="REMOTE_ADDR">REMOTE_ADDR</SelectItem>
                <SelectItem value="HTTP_CLIENT_IP">HTTP_CLIENT_IP</SelectItem>
                <SelectItem value="HTTP_X_FORWARDED_FOR">HTTP_X_FORWARDED_FOR</SelectItem>
                <SelectItem value="HTTP_X_FORWARDED">HTTP_X_FORWARDED</SelectItem>
                <SelectItem value="HTTP_FORWARDED_FOR">HTTP_FORWARDED_FOR</SelectItem>
                <SelectItem value="HTTP_FORWARDED">HTTP_FORWARDED</SelectItem>
                <SelectItem value="HTTP_X_REAL_IP">HTTP_X_REAL_IP</SelectItem>
                <SelectItem value="HTTP_CF_CONNECTING_IP">HTTP_CF_CONNECTING_IP (Cloudflare)</SelectItem>
                <SelectItem value="custom">Custom Header</SelectItem>
              </SelectContent>
            </Select>
            <p className="text-xs text-muted-foreground">
              Select how visitor IP addresses should be detected. Use 'Sequential' to check all
              headers automatically, or specify a specific header for your server configuration.
            </p>
          </div>

          {ipMethod === 'custom' && (
            <div className="space-y-2">
              <Label htmlFor="custom-header">Custom Header Name</Label>
              <Input
                id="custom-header"
                type="text"
                placeholder="HTTP_X_CUSTOM_IP"
                value={customHeaderIpMethod as string}
                onChange={(e) => setCustomHeaderIpMethod(e.target.value)}
              />
              <p className="text-xs text-muted-foreground">
                Enter the custom server header name that contains the visitor's IP address.
              </p>
            </div>
          )}

          <div className="rounded-lg border bg-muted/50 p-4">
            <h4 className="text-sm font-medium mb-3">Your IP Information</h4>
            <div className="grid gap-3 sm:grid-cols-2">
              <div className="flex items-center gap-2">
                <Server className="h-4 w-4 text-muted-foreground" />
                <div>
                  <p className="text-xs text-muted-foreground">WP Statistics Detected</p>
                  <p className="text-sm font-mono">{detectedIp}</p>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <Globe className="h-4 w-4 text-muted-foreground" />
                <div>
                  <p className="text-xs text-muted-foreground">External IP (ipify.org)</p>
                  <p className="text-sm font-mono">{externalIp}</p>
                </div>
              </div>
            </div>
            {detectedIp !== 'Loading...' && externalIp !== 'Loading...' && detectedIp !== externalIp && (
              <div className="mt-3 flex items-start gap-2 rounded-md bg-amber-500/10 p-2">
                <AlertTriangle className="h-4 w-4 text-amber-500 mt-0.5" />
                <p className="text-xs text-amber-600 dark:text-amber-400">
                  The IPs don't match. If you're behind a proxy or CDN, ensure the correct header is selected above.
                </p>
              </div>
            )}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>GeoIP Settings</CardTitle>
          <CardDescription>
            Configure how visitor locations are detected and displayed.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="geoip-detection">Location Detection Method</Label>
              <Select
                value={geoipDetectionMethod as string}
                onValueChange={setGeoipDetectionMethod}
              >
                <SelectTrigger id="geoip-detection">
                  <SelectValue placeholder="Select method" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="maxmind">MaxMind GeoIP</SelectItem>
                  <SelectItem value="dbip">DB-IP</SelectItem>
                  <SelectItem value="cf">Cloudflare IP Geolocation</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="geoip-source">Database Update Source</Label>
              <Select value={geoipLicenseType as string} onValueChange={setGeoipLicenseType}>
                <SelectTrigger id="geoip-source">
                  <SelectValue placeholder="Select source" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="js-deliver">JsDelivr (Free)</SelectItem>
                  <SelectItem value="user-license">Custom License Key</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          {geoipLicenseType === 'user-license' && (
            <div className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="maxmind-license">MaxMind License Key</Label>
                <Input
                  id="maxmind-license"
                  type="text"
                  placeholder="Enter your MaxMind license key"
                  value={geoipLicenseKey as string}
                  onChange={(e) => setGeoipLicenseKey(e.target.value)}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="dbip-license">DB-IP License Key</Label>
                <Input
                  id="dbip-license"
                  type="text"
                  placeholder="Enter your DB-IP license key"
                  value={geoipDbipLicenseKey as string}
                  onChange={(e) => setGeoipDbipLicenseKey(e.target.value)}
                />
              </div>
            </div>
          )}

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="schedule-geoip">Auto-Update GeoIP Database</Label>
              <p className="text-sm text-muted-foreground">
                Automatically download the latest GeoIP database weekly.
              </p>
            </div>
            <Switch
              id="schedule-geoip"
              checked={!!scheduleGeoip}
              onCheckedChange={setScheduleGeoip}
            />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="auto-pop">Auto-Fill Missing Locations</Label>
              <p className="text-sm text-muted-foreground">
                Automatically fill in location data for visitors with incomplete records.
              </p>
            </div>
            <Switch id="auto-pop" checked={!!autoPop} onCheckedChange={setAutoPop} />
          </div>

          <div className="space-y-2">
            <Label htmlFor="private-country">Private IP Country Code</Label>
            <Input
              id="private-country"
              type="text"
              maxLength={3}
              placeholder="000"
              value={privateCountryCode as string}
              onChange={(e) => setPrivateCountryCode(e.target.value)}
              className="w-24"
            />
            <p className="text-xs text-muted-foreground">
              Country code to use for private/local IP addresses.
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Content Analytics</CardTitle>
          <CardDescription>Configure content analysis features.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="word-count-analytics">Word Count Analytics</Label>
              <p className="text-sm text-muted-foreground">
                Calculate and store word count for posts to enable reading time estimates and content
                length analytics.
              </p>
            </div>
            <Switch
              id="word-count-analytics"
              checked={!!wordCountAnalytics}
              onCheckedChange={setWordCountAnalytics}
            />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Anonymous Data Sharing</CardTitle>
          <CardDescription>Help improve WP Statistics.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="share-anonymous">Share Anonymous Usage Data</Label>
              <p className="text-sm text-muted-foreground">
                Help us improve WP Statistics by sharing anonymous usage data.
              </p>
            </div>
            <Switch
              id="share-anonymous"
              checked={!!shareAnonymousData}
              onCheckedChange={setShareAnonymousData}
            />
          </div>
        </CardContent>
      </Card>

      <Card className="border-destructive/50">
        <CardHeader>
          <CardTitle className="text-destructive">Danger Zone</CardTitle>
          <CardDescription>
            These actions are irreversible. Please proceed with caution.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="delete-on-uninstall">Delete All Data on Uninstall</Label>
              <p className="text-sm text-muted-foreground">
                Remove all WP Statistics data from the database when the plugin is uninstalled. This
                action cannot be undone.
              </p>
            </div>
            <Switch
              id="delete-on-uninstall"
              checked={!!deleteOnUninstall}
              onCheckedChange={setDeleteOnUninstall}
            />
          </div>

          <div className="border-t pt-6">
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Restore Default Settings</Label>
                <p className="text-sm text-muted-foreground">
                  Reset all WP Statistics settings to their default values. Your statistics data will
                  not be affected.
                </p>
              </div>
              <Button
                variant="destructive"
                size="sm"
                onClick={async () => {
                  if (
                    confirm(
                      'Are you sure you want to restore all settings to defaults? This cannot be undone.'
                    )
                  ) {
                    setIsResetting(true)
                    try {
                      const response = await fetch(
                        `${(window as any).wps_react?.ajaxUrl}?action=wps_restore_defaults&nonce=${(window as any).wps_react?.nonce}`,
                        { method: 'POST' }
                      )
                      if (response.ok) {
                        alert('Settings have been restored to defaults. The page will now reload.')
                        window.location.reload()
                      }
                    } catch (error) {
                      alert('Failed to restore settings. Please try again.')
                    } finally {
                      setIsResetting(false)
                    }
                  }
                }}
                disabled={isResetting}
              >
                {isResetting ? (
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                ) : (
                  <RotateCcw className="mr-2 h-4 w-4" />
                )}
                Restore Defaults
              </Button>
            </div>
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
