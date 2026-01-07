import * as React from 'react'

import { Button } from '@/components/ui/button'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
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
import { Loader2 } from 'lucide-react'

export function PrivacySettings() {
  const settings = useSettings({ tab: 'privacy' })

  // Individual settings
  const [anonymizeIps, setAnonymizeIps] = useSetting(settings, 'anonymize_ips', true)
  const [hashIps, setHashIps] = useSetting(settings, 'hash_ips', true)
  const [doNotTrack, setDoNotTrack] = useSetting(settings, 'do_not_track', false)
  const [anonymousTracking, setAnonymousTracking] = useSetting(settings, 'anonymous_tracking', false)
  const [consentIntegration, setConsentIntegration] = useSetting(
    settings,
    'consent_integration',
    'none'
  )
  const [consentLevel, setConsentLevel] = useSetting(
    settings,
    'consent_level_integration',
    'functional'
  )
  const [privacyAudit, setPrivacyAudit] = useSetting(settings, 'privacy_audit', true)

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
          <CardTitle>Data Protection</CardTitle>
          <CardDescription>
            Configure how visitor IP addresses are stored and processed.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="anonymize-ips">Anonymize IP Addresses</Label>
              <p className="text-sm text-muted-foreground">
                Masks the last segment of IP addresses for privacy compliance.
              </p>
            </div>
            <Switch
              id="anonymize-ips"
              checked={!!anonymizeIps}
              onCheckedChange={setAnonymizeIps}
            />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="hash-ips">Hash IP Addresses</Label>
              <p className="text-sm text-muted-foreground">
                Transforms IP addresses into a unique, non-reversible string.
              </p>
            </div>
            <Switch id="hash-ips" checked={!!hashIps} onCheckedChange={setHashIps} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>User Preferences</CardTitle>
          <CardDescription>
            Configure consent integration and respect user privacy preferences.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="consent-integration">Consent Plugin Integration</Label>
              <p className="text-sm text-muted-foreground">
                Integrate with supported consent management plugins.
              </p>
            </div>
            <Select
              value={consentIntegration as string}
              onValueChange={setConsentIntegration}
            >
              <SelectTrigger className="w-[200px]">
                <SelectValue placeholder="Select plugin" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="none">None</SelectItem>
                <SelectItem value="wp_consent_api">Via WP Consent API</SelectItem>
                <SelectItem value="complianz">Complianz</SelectItem>
                <SelectItem value="cookieyes">CookieYes</SelectItem>
                <SelectItem value="real_cookie_banner">Real Cookie Banner</SelectItem>
                <SelectItem value="borlabs_cookie">Borlabs Cookie</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {consentIntegration === 'wp_consent_api' && (
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label htmlFor="consent-level">Consent Category</Label>
                <p className="text-sm text-muted-foreground">
                  Select the consent category WP Statistics should track.
                </p>
              </div>
              <Select value={consentLevel as string} onValueChange={setConsentLevel}>
                <SelectTrigger className="w-[200px]">
                  <SelectValue placeholder="Select category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="functional">Functional</SelectItem>
                  <SelectItem value="statistics-anonymous">Statistics-Anonymous</SelectItem>
                  <SelectItem value="statistics">Statistics</SelectItem>
                  <SelectItem value="marketing">Marketing</SelectItem>
                </SelectContent>
              </Select>
            </div>
          )}

          {consentIntegration && consentIntegration !== 'none' && (
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label htmlFor="anonymous-tracking">Anonymous Tracking</Label>
                <p className="text-sm text-muted-foreground">
                  Track all users anonymously without PII by default.
                </p>
              </div>
              <Switch
                id="anonymous-tracking"
                checked={!!anonymousTracking}
                onCheckedChange={setAnonymousTracking}
              />
            </div>
          )}

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="do-not-track">Do Not Track (DNT)</Label>
              <p className="text-sm text-muted-foreground">
                Respect the visitor's browser setting to not track their activity.
              </p>
            </div>
            <Switch id="do-not-track" checked={!!doNotTrack} onCheckedChange={setDoNotTrack} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Privacy Audit</CardTitle>
          <CardDescription>Enable privacy monitoring and compliance tools.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="privacy-audit">Enable Privacy Audit</Label>
              <p className="text-sm text-muted-foreground">
                Show privacy indicators on settings that affect user privacy.
              </p>
            </div>
            <Switch
              id="privacy-audit"
              checked={!!privacyAudit}
              onCheckedChange={setPrivacyAudit}
            />
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
