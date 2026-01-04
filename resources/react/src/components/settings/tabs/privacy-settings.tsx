import * as React from 'react'

import { Button } from '@/components/ui/button'
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
  const [ipMethod, setIpMethod] = useSetting(settings, 'ip_method', 'sequential')
  const [doNotTrack, setDoNotTrack] = useSetting(settings, 'do_not_track', false)
  const [anonymousTracking, setAnonymousTracking] = useSetting(settings, 'anonymous_tracking', false)
  const [consentLevel, setConsentLevel] = useSetting(settings, 'consent_level_integration', 'disabled')
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
          <CardTitle>IP Address Handling</CardTitle>
          <CardDescription>
            Configure how visitor IP addresses are stored and processed.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="anonymize-ips">Anonymize IP Addresses</Label>
              <p className="text-sm text-muted-foreground">
                Remove the last octet of IP addresses (e.g., 192.168.1.xxx).
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
                Store hashed versions of IP addresses for privacy compliance.
              </p>
            </div>
            <Switch id="hash-ips" checked={!!hashIps} onCheckedChange={setHashIps} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="ip-method">IP Detection Method</Label>
              <p className="text-sm text-muted-foreground">
                How to detect visitor IP addresses from request headers.
              </p>
            </div>
            <Select value={ipMethod as string} onValueChange={setIpMethod}>
              <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Select method" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="sequential">Sequential</SelectItem>
                <SelectItem value="REMOTE_ADDR">REMOTE_ADDR</SelectItem>
                <SelectItem value="X_FORWARDED_FOR">X-Forwarded-For</SelectItem>
                <SelectItem value="HTTP_X_REAL_IP">X-Real-IP</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Data Collection</CardTitle>
          <CardDescription>Control what visitor data is collected and stored.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="do-not-track">Respect Do Not Track</Label>
              <p className="text-sm text-muted-foreground">
                Honor the browser's Do Not Track setting.
              </p>
            </div>
            <Switch id="do-not-track" checked={!!doNotTrack} onCheckedChange={setDoNotTrack} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="anonymous-tracking">Anonymous Tracking</Label>
              <p className="text-sm text-muted-foreground">
                Track visitors without storing any personal data.
              </p>
            </div>
            <Switch
              id="anonymous-tracking"
              checked={!!anonymousTracking}
              onCheckedChange={setAnonymousTracking}
            />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="consent-level">Consent Integration</Label>
              <p className="text-sm text-muted-foreground">
                Integrate with consent management plugins.
              </p>
            </div>
            <Select value={consentLevel as string} onValueChange={setConsentLevel}>
              <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Select integration" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="disabled">Disabled</SelectItem>
                <SelectItem value="complianz">Complianz</SelectItem>
                <SelectItem value="cookieyes">CookieYes</SelectItem>
                <SelectItem value="cookie-notice">Cookie Notice</SelectItem>
              </SelectContent>
            </Select>
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
