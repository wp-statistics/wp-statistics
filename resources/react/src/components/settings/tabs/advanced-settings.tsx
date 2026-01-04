import * as React from 'react'
import { Loader2 } from 'lucide-react'

import { Button } from '@/components/ui/button'
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
  const [scheduleDays, setScheduleDays] = useSetting(settings, 'schedule_dbmaint_days', 180)
  const [deleteOnUninstall, setDeleteOnUninstall] = useSetting(
    settings,
    'delete_data_on_uninstall',
    false
  )

  // Other Settings
  const [shareAnonymousData, setShareAnonymousData] = useSetting(
    settings,
    'share_anonymous_data',
    false
  )
  const [bypassAdBlockers, setBypassAdBlockers] = useSetting(
    settings,
    'bypass_ad_blockers',
    false
  )
  const [useCachePlugin, setUseCachePlugin] = useSetting(settings, 'use_cache_plugin', true)

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
          <CardTitle>Tracker Configuration</CardTitle>
          <CardDescription>Configure how the tracking script works on your site.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="cache-plugin">Cache Plugin Compatibility</Label>
              <p className="text-sm text-muted-foreground">
                Enable client-side tracking for better accuracy with caching plugins.
              </p>
            </div>
            <Switch
              id="cache-plugin"
              checked={!!useCachePlugin}
              onCheckedChange={setUseCachePlugin}
            />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="bypass-adblockers">Bypass Ad Blockers</Label>
              <p className="text-sm text-muted-foreground">
                Use alternative methods to track visitors who use ad blockers.
              </p>
            </div>
            <Switch
              id="bypass-adblockers"
              checked={!!bypassAdBlockers}
              onCheckedChange={setBypassAdBlockers}
            />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Database Maintenance</CardTitle>
          <CardDescription>Configure automatic data cleanup settings.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="space-y-2">
            <Label htmlFor="data-retention">Data Retention Period (Days)</Label>
            <Input
              id="data-retention"
              type="number"
              min="30"
              max="365"
              value={scheduleDays as number}
              onChange={(e) => setScheduleDays(parseInt(e.target.value) || 180)}
              className="w-32"
            />
            <p className="text-xs text-muted-foreground">
              Automatically aggregate data older than this many days.
            </p>
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="delete-on-uninstall">Delete Data on Uninstall</Label>
              <p className="text-sm text-muted-foreground">
                Remove all plugin data when uninstalling WP Statistics.
              </p>
            </div>
            <Switch
              id="delete-on-uninstall"
              checked={!!deleteOnUninstall}
              onCheckedChange={setDeleteOnUninstall}
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

      {settings.error && (
        <div className="rounded-md bg-destructive/15 p-3 text-sm text-destructive">
          {settings.error}
        </div>
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
