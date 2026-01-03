import * as React from 'react'

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'

export function GeneralSettings() {
  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Tracking Settings</CardTitle>
          <CardDescription>
            Configure what data WP Statistics collects from your visitors.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="track-online">Track Online Visitors</Label>
              <p className="text-sm text-muted-foreground">
                Show real-time count of visitors currently on your site.
              </p>
            </div>
            <Switch id="track-online" />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="track-visits">Track Page Visits</Label>
              <p className="text-sm text-muted-foreground">
                Record each page view on your website.
              </p>
            </div>
            <Switch id="track-visits" defaultChecked />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="track-visitors">Track Unique Visitors</Label>
              <p className="text-sm text-muted-foreground">
                Count unique visitors to your website.
              </p>
            </div>
            <Switch id="track-visitors" defaultChecked />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="track-logged-in">Track Logged-in Users</Label>
              <p className="text-sm text-muted-foreground">
                Include logged-in users in your statistics.
              </p>
            </div>
            <Switch id="track-logged-in" />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>404 Page Tracking</CardTitle>
          <CardDescription>
            Monitor broken links and missing pages on your site.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="track-404">Track 404 Pages</Label>
              <p className="text-sm text-muted-foreground">
                Record when visitors encounter missing pages.
              </p>
            </div>
            <Switch id="track-404" />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-query-strings">Exclude Query Strings</Label>
              <p className="text-sm text-muted-foreground">
                Ignore query parameters in 404 page tracking.
              </p>
            </div>
            <Switch id="exclude-query-strings" />
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
