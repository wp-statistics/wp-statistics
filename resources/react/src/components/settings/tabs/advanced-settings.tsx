import { __ } from '@wordpress/i18n'
import { AlertTriangle, Loader2, RotateCcw } from 'lucide-react'
import * as React from 'react'

import { SettingsActionField, SettingsCard, SettingsField, SettingsInfoBox, SettingsPage, SettingsSelectField, SettingsToggleField } from '@/components/settings-ui'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { useSetting, useSettings } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'
import { WordPress } from '@/lib/wordpress'

export function AdvancedSettings() {
  const settings = useSettings({ tab: 'advanced' })
  const [isResetting, setIsResetting] = React.useState(false)
  const [showResetDialog, setShowResetDialog] = React.useState(false)
  const { toast } = useToast()

  // GeoIP Settings
  const [geoipLicenseType, setGeoipLicenseType] = useSetting(settings, 'geoip_license_type', 'js-deliver')
  const [geoipLicenseKey, setGeoipLicenseKey] = useSetting(settings, 'geoip_license_key', '')
  const [geoipDbipLicenseKey, setGeoipDbipLicenseKey] = useSetting(settings, 'geoip_dbip_license_key_option', '')
  const [geoipDetectionMethod, setGeoipDetectionMethod] = useSetting(settings, 'geoip_location_detection_method', 'maxmind')

  // Database Settings
  const [deleteOnUninstall, setDeleteOnUninstall] = useSetting(settings, 'delete_data_on_uninstall', false)

  // Content Analytics Settings
  const [wordCountAnalytics, setWordCountAnalytics] = useSetting(settings, 'word_count_analytics', false)

  // Other Settings
  const [shareAnonymousData, setShareAnonymousData] = useSetting(settings, 'share_anonymous_data', false)

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
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to restore settings. Please try again.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsResetting(false)
    }
  }

  return (
    <SettingsPage settings={settings} saveDescription={__('Advanced settings have been updated.', 'wp-statistics')}>
      <SettingsCard
        title={__('GeoIP Settings', 'wp-statistics')}
        description={__('Configure how visitor locations are detected and displayed.', 'wp-statistics')}
      >
        <SettingsSelectField
          id="geoip-detection"
          label={__('Location Detection Method', 'wp-statistics')}
          layout="stacked"
          value={geoipDetectionMethod as string}
          onValueChange={setGeoipDetectionMethod}
          placeholder={__('Select method', 'wp-statistics')}
          options={[
            { value: 'maxmind', label: __('MaxMind GeoIP', 'wp-statistics') },
            { value: 'dbip', label: __('DB-IP', 'wp-statistics') },
            { value: 'cf', label: __('Cloudflare IP Geolocation', 'wp-statistics') },
          ]}
        />

        {geoipDetectionMethod === 'cf' ? (
          <SettingsInfoBox>
            {__('Cloudflare provides location data via HTTP headers. Make sure IP Geolocation is enabled in your Cloudflare dashboard.', 'wp-statistics')}
          </SettingsInfoBox>
        ) : (
          <>
            <SettingsSelectField
              id="geoip-source"
              label={__('Database Update Source', 'wp-statistics')}
              description={__('Select how the GeoIP database is downloaded. The free option uses a community-maintained database.', 'wp-statistics')}
              layout="stacked"
              value={geoipLicenseType as string}
              onValueChange={setGeoipLicenseType}
              placeholder={__('Select source', 'wp-statistics')}
              options={[
                { value: 'js-deliver', label: __('Free Database', 'wp-statistics') },
                { value: 'user-license', label: __('Custom License Key', 'wp-statistics') },
              ]}
            />

            {geoipLicenseType === 'user-license' && geoipDetectionMethod === 'maxmind' && (
              <SettingsField
                id="maxmind-license"
                label={__('MaxMind License Key', 'wp-statistics')}
                layout="stacked"
                nested
              >
                <Input
                  id="maxmind-license"
                  type="text"
                  placeholder={__('Enter your MaxMind license key', 'wp-statistics')}
                  value={geoipLicenseKey as string}
                  onChange={(e) => setGeoipLicenseKey(e.target.value)}
                />
              </SettingsField>
            )}

            {geoipLicenseType === 'user-license' && geoipDetectionMethod === 'dbip' && (
              <SettingsField
                id="dbip-license"
                label={__('DB-IP License Key', 'wp-statistics')}
                layout="stacked"
                nested
              >
                <Input
                  id="dbip-license"
                  type="text"
                  placeholder={__('Enter your DB-IP license key', 'wp-statistics')}
                  value={geoipDbipLicenseKey as string}
                  onChange={(e) => setGeoipDbipLicenseKey(e.target.value)}
                />
              </SettingsField>
            )}
          </>
        )}
      </SettingsCard>

      <SettingsCard
        title={__('Content Analytics', 'wp-statistics')}
        description={__('Configure content analysis features.', 'wp-statistics')}
      >
        <SettingsToggleField
          id="word-count-analytics"
          label={__('Word Count Analytics', 'wp-statistics')}
          description={__('Calculate and store word count for posts to enable reading time estimates and content length analytics.', 'wp-statistics')}
          checked={!!wordCountAnalytics}
          onCheckedChange={setWordCountAnalytics}
        />
      </SettingsCard>

      <SettingsCard
        title={__('Anonymous Data Sharing', 'wp-statistics')}
        description={__('Help improve WP Statistics.', 'wp-statistics')}
      >
        <SettingsToggleField
          id="share-anonymous"
          label={__('Share Anonymous Usage Data', 'wp-statistics')}
          description={__('Help us improve WP Statistics by sharing anonymous usage data.', 'wp-statistics')}
          checked={!!shareAnonymousData}
          onCheckedChange={setShareAnonymousData}
        />
      </SettingsCard>

      <SettingsCard
        title={__('Danger Zone', 'wp-statistics')}
        variant="danger"
        description={__('These actions are irreversible. Please proceed with caution.', 'wp-statistics')}
      >
        <SettingsToggleField
          id="delete-on-uninstall"
          label={__('Delete All Data on Uninstall', 'wp-statistics')}
          description={__('Remove all WP Statistics data from the database when the plugin is uninstalled. This action cannot be undone.', 'wp-statistics')}
          checked={!!deleteOnUninstall}
          onCheckedChange={setDeleteOnUninstall}
        />

        <SettingsActionField
          label={__('Restore Default Settings', 'wp-statistics')}
          description={__('Reset all WP Statistics settings to their default values. Your statistics data will not be affected.', 'wp-statistics')}
        >
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
        </SettingsActionField>
      </SettingsCard>

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
    </SettingsPage>
  )
}
