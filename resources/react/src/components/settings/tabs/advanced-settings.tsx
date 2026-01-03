import * as React from 'react'

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'

export function AdvancedSettings() {
  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Performance</CardTitle>
          <CardDescription>
            Configure performance-related settings for the tracking system.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="bypass-ad-blockers">Bypass Ad Blockers</Label>
              <p className="text-sm text-muted-foreground">
                Use alternative methods to track visitors who use ad blockers.
              </p>
            </div>
            <Switch id="bypass-ad-blockers" />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="use-cache">Cache Plugin Compatibility</Label>
              <p className="text-sm text-muted-foreground">
                Enable compatibility mode for caching plugins.
              </p>
            </div>
            <Switch id="use-cache" />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Database</CardTitle>
          <CardDescription>
            Configure database-related settings.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="table-prefix">Table Prefix</Label>
            <Input
              id="table-prefix"
              placeholder="wp_statistics_"
              disabled
            />
            <p className="text-xs text-muted-foreground">
              The database table prefix used by WP Statistics.
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Access Control</CardTitle>
          <CardDescription>
            Configure who can access and manage WP Statistics.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="read-capability">View Statistics</Label>
              <Select defaultValue="manage_options">
                <SelectTrigger id="read-capability">
                  <SelectValue placeholder="Select capability" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="manage_options">Administrators</SelectItem>
                  <SelectItem value="edit_others_posts">Editors</SelectItem>
                  <SelectItem value="publish_posts">Authors</SelectItem>
                  <SelectItem value="edit_posts">Contributors</SelectItem>
                </SelectContent>
              </Select>
              <p className="text-xs text-muted-foreground">
                Minimum capability required to view statistics.
              </p>
            </div>

            <div className="space-y-2">
              <Label htmlFor="manage-capability">Manage Settings</Label>
              <Select defaultValue="manage_options">
                <SelectTrigger id="manage-capability">
                  <SelectValue placeholder="Select capability" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="manage_options">Administrators</SelectItem>
                  <SelectItem value="edit_others_posts">Editors</SelectItem>
                </SelectContent>
              </Select>
              <p className="text-xs text-muted-foreground">
                Minimum capability required to manage settings.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
