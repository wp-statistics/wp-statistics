import * as React from 'react'

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'

export function ExclusionSettings() {
  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Traffic Exclusions</CardTitle>
          <CardDescription>
            Configure which traffic should be excluded from your statistics.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-bots">Exclude Bots & Crawlers</Label>
              <p className="text-sm text-muted-foreground">
                Filter out known bots and search engine crawlers.
              </p>
            </div>
            <Switch id="exclude-bots" defaultChecked />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-feeds">Exclude RSS Feeds</Label>
              <p className="text-sm text-muted-foreground">
                Don't count RSS feed requests in statistics.
              </p>
            </div>
            <Switch id="exclude-feeds" />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-self-referral">Exclude Self-Referrals</Label>
              <p className="text-sm text-muted-foreground">
                Ignore traffic coming from your own domain.
              </p>
            </div>
            <Switch id="exclude-self-referral" />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>IP Exclusions</CardTitle>
          <CardDescription>
            Exclude specific IP addresses from being tracked.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="exclude-ips">Excluded IP Addresses</Label>
            <Input
              id="exclude-ips"
              placeholder="192.168.1.1, 10.0.0.0/8"
            />
            <p className="text-xs text-muted-foreground">
              Enter IP addresses or CIDR ranges, separated by commas.
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>URL Exclusions</CardTitle>
          <CardDescription>
            Exclude specific pages or paths from being tracked.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="exclude-urls">Excluded URLs</Label>
            <Input
              id="exclude-urls"
              placeholder="/admin, /wp-json, /api/*"
            />
            <p className="text-xs text-muted-foreground">
              Enter URL paths to exclude. Supports wildcards (*).
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>User Role Exclusions</CardTitle>
          <CardDescription>
            Exclude users with specific roles from being tracked.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label>Administrators</Label>
              <p className="text-sm text-muted-foreground">
                Exclude users with administrator role.
              </p>
            </div>
            <Switch defaultChecked />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label>Editors</Label>
              <p className="text-sm text-muted-foreground">
                Exclude users with editor role.
              </p>
            </div>
            <Switch />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label>Authors</Label>
              <p className="text-sm text-muted-foreground">
                Exclude users with author role.
              </p>
            </div>
            <Switch />
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
