import * as React from 'react'

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'

export function PrivacySettings() {
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
            <Switch id="anonymize-ips" />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="hash-ips">Hash IP Addresses</Label>
              <p className="text-sm text-muted-foreground">
                Store hashed versions of IP addresses for privacy compliance.
              </p>
            </div>
            <Switch id="hash-ips" />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Data Collection</CardTitle>
          <CardDescription>
            Control what visitor data is collected and stored.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="store-ua">Store User Agent</Label>
              <p className="text-sm text-muted-foreground">
                Record browser and device information from visitors.
              </p>
            </div>
            <Switch id="store-ua" />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="consent-required">Require Consent</Label>
              <p className="text-sm text-muted-foreground">
                Only track visitors who have given explicit consent.
              </p>
            </div>
            <Switch id="consent-required" />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="do-not-track">Respect Do Not Track</Label>
              <p className="text-sm text-muted-foreground">
                Honor the browser's Do Not Track setting.
              </p>
            </div>
            <Switch id="do-not-track" />
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
