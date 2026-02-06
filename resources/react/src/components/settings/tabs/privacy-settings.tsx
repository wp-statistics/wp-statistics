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

export function PrivacySettings() {
  const settings = useSettings({ tab: 'privacy' })
  const { toast } = useToast()

  // Individual settings
  const [storeIp, setStoreIp] = useSetting(settings, 'store_ip', false)
  const [hashRotationInterval, setHashRotationInterval] = useSetting(settings, 'hash_rotation_interval', 'daily')
  const [anonymousTracking, setAnonymousTracking] = useSetting(settings, 'anonymous_tracking', false)
  const [consentIntegration, setConsentIntegration] = useSetting(settings, 'consent_integration', 'none')
  const [consentLevel, setConsentLevel] = useSetting(settings, 'consent_level_integration', 'functional')
  const [privacyAudit, setPrivacyAudit] = useSetting(settings, 'privacy_audit', true)

  const handleSave = async () => {
    const success = await settings.save()
    if (success) {
      toast({
        title: __('Settings saved', 'wp-statistics'),
        description: __('Privacy settings have been updated.', 'wp-statistics'),
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
          <CardTitle>{__('Data Protection', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Configure how visitor IP addresses are stored and processed.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="store-ip">{__('Store IP Addresses', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Record full visitor IP addresses in the database. When disabled, only anonymous hashes are stored.', 'wp-statistics')}
              </p>
            </div>
            <Switch id="store-ip" checked={!!storeIp} onCheckedChange={setStoreIp} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="hash-rotation-interval">{__('Hash Rotation Interval', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('How often the salt used for visitor hashing rotates. Shorter intervals improve privacy but reduce returning-visitor detection accuracy.', 'wp-statistics')}
              </p>
            </div>
            <Select value={hashRotationInterval as string} onValueChange={setHashRotationInterval}>
              <SelectTrigger className="w-[200px]">
                <SelectValue placeholder={__('Select interval', 'wp-statistics')} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="daily">{__('Daily', 'wp-statistics')}</SelectItem>
                <SelectItem value="weekly">{__('Weekly', 'wp-statistics')}</SelectItem>
                <SelectItem value="monthly">{__('Monthly', 'wp-statistics')}</SelectItem>
                <SelectItem value="disabled">{__('Disabled', 'wp-statistics')}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {hashRotationInterval === 'disabled' && (
            <NoticeBanner
              id="hash-rotation-warning"
              type="warning"
              message={__('Disabling hash rotation means the same visitor will always produce the same hash. This improves returning-visitor detection but reduces privacy protection.', 'wp-statistics')}
              dismissible={false}
            />
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('User Preferences', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Configure consent integration and respect user privacy preferences.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="consent-integration">{__('Consent Plugin Integration', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__('Integrate with supported consent management plugins.', 'wp-statistics')}</p>
            </div>
            <Select value={consentIntegration as string} onValueChange={setConsentIntegration}>
              <SelectTrigger className="w-[200px]">
                <SelectValue placeholder={__('Select plugin', 'wp-statistics')} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="none">{__('None', 'wp-statistics')}</SelectItem>
                <SelectItem value="wp_consent_api">{__('Via WP Consent API', 'wp-statistics')}</SelectItem>
                <SelectItem value="complianz">{__('Complianz', 'wp-statistics')}</SelectItem>
                <SelectItem value="cookieyes">{__('CookieYes', 'wp-statistics')}</SelectItem>
                <SelectItem value="real_cookie_banner">{__('Real Cookie Banner', 'wp-statistics')}</SelectItem>
                <SelectItem value="borlabs_cookie">{__('Borlabs Cookie', 'wp-statistics')}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {consentIntegration === 'wp_consent_api' && (
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label htmlFor="consent-level">{__('Consent Category', 'wp-statistics')}</Label>
                <p className="text-sm text-muted-foreground">{__('Select the consent category WP Statistics should track.', 'wp-statistics')}</p>
              </div>
              <Select value={consentLevel as string} onValueChange={setConsentLevel}>
                <SelectTrigger className="w-[200px]">
                  <SelectValue placeholder={__('Select category', 'wp-statistics')} />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="functional">{__('Functional', 'wp-statistics')}</SelectItem>
                  <SelectItem value="statistics-anonymous">{__('Statistics-Anonymous', 'wp-statistics')}</SelectItem>
                  <SelectItem value="statistics">{__('Statistics', 'wp-statistics')}</SelectItem>
                  <SelectItem value="marketing">{__('Marketing', 'wp-statistics')}</SelectItem>
                </SelectContent>
              </Select>
            </div>
          )}

          {consentIntegration && consentIntegration !== 'none' && (
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label htmlFor="anonymous-tracking">{__('Anonymous Tracking', 'wp-statistics')}</Label>
                <p className="text-sm text-muted-foreground">{__('Track all users anonymously without PII by default.', 'wp-statistics')}</p>
              </div>
              <Switch id="anonymous-tracking" checked={!!anonymousTracking} onCheckedChange={setAnonymousTracking} />
            </div>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('Privacy Audit', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Enable privacy monitoring and compliance tools.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="privacy-audit">{__('Enable Privacy Audit', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">
                {__('Show privacy indicators on settings that affect user privacy.', 'wp-statistics')}
              </p>
            </div>
            <Switch id="privacy-audit" checked={!!privacyAudit} onCheckedChange={setPrivacyAudit} />
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
