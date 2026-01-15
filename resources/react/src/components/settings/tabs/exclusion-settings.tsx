import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Switch } from '@/components/ui/switch'
import { Textarea } from '@/components/ui/textarea'
import { useSetting,useSettings } from '@/hooks/use-settings'

export function ExclusionSettings() {
  const settings = useSettings({ tab: 'exclusions' })

  // IP/URL Exclusions
  const [excludeIp, setExcludeIp] = useSetting(settings, 'exclude_ip', '')
  const [excludedUrls, setExcludedUrls] = useSetting(settings, 'excluded_urls', '')
  const [excludedCountries, setExcludedCountries] = useSetting(settings, 'excluded_countries', '')
  const [includedCountries, setIncludedCountries] = useSetting(settings, 'included_countries', '')

  // Bot Exclusions
  const [robotlist, setRobotlist] = useSetting(settings, 'robotlist', '')
  const [robotThreshold, setRobotThreshold] = useSetting(settings, 'robot_threshold', 10)
  const [recordExclusions, setRecordExclusions] = useSetting(settings, 'record_exclusions', false)

  // Page Exclusions
  const [excludeLoginpage, setExcludeLoginpage] = useSetting(settings, 'exclude_loginpage', false)
  const [excludeFeeds, setExcludeFeeds] = useSetting(settings, 'exclude_feeds', false)
  const [exclude404s, setExclude404s] = useSetting(settings, 'exclude_404s', false)

  // Role Exclusions
  const [excludeAdmin, setExcludeAdmin] = useSetting(settings, 'exclude_administrator', false)
  const [excludeEditor, setExcludeEditor] = useSetting(settings, 'exclude_editor', false)
  const [excludeAuthor, setExcludeAuthor] = useSetting(settings, 'exclude_author', false)
  const [excludeContributor, setExcludeContributor] = useSetting(settings, 'exclude_contributor', false)
  const [excludeSubscriber, setExcludeSubscriber] = useSetting(settings, 'exclude_subscriber', false)
  const [excludeAnonymous, setExcludeAnonymous] = useSetting(settings, 'exclude_anonymous_users', false)

  // Query Parameters
  const [queryParamsAllowList, setQueryParamsAllowList] = useSetting(settings, 'query_params_allow_list', '')

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
          <CardTitle>Page Exclusions</CardTitle>
          <CardDescription>Exclude specific pages or paths from being tracked.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-loginpage">Exclude Login Page</Label>
              <p className="text-sm text-muted-foreground">Don't track WordPress login page visits.</p>
            </div>
            <Switch id="exclude-loginpage" checked={!!excludeLoginpage} onCheckedChange={setExcludeLoginpage} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-feeds">Exclude RSS Feeds</Label>
              <p className="text-sm text-muted-foreground">Don't count RSS feed requests in statistics.</p>
            </div>
            <Switch id="exclude-feeds" checked={!!excludeFeeds} onCheckedChange={setExcludeFeeds} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-404s">Exclude 404 Pages</Label>
              <p className="text-sm text-muted-foreground">Don't track visits to pages that return 404 errors.</p>
            </div>
            <Switch id="exclude-404s" checked={!!exclude404s} onCheckedChange={setExclude404s} />
          </div>

          <div className="space-y-2">
            <Label htmlFor="exclude-urls">Excluded URLs</Label>
            <Textarea
              id="exclude-urls"
              placeholder="/admin&#10;/wp-json&#10;/api/*"
              value={excludedUrls as string}
              onChange={(e) => setExcludedUrls(e.target.value)}
              rows={4}
            />
            <p className="text-xs text-muted-foreground">
              Enter URL paths to exclude, one per line. Supports wildcards (*).
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>IP Exclusions</CardTitle>
          <CardDescription>Exclude specific IP addresses from being tracked.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="exclude-ips">Excluded IP Addresses</Label>
            <Textarea
              id="exclude-ips"
              placeholder="192.168.1.1&#10;10.0.0.0/8"
              value={excludeIp as string}
              onChange={(e) => setExcludeIp(e.target.value)}
              rows={4}
            />
            <p className="text-xs text-muted-foreground">Enter IP addresses or CIDR ranges, one per line.</p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Country Filters</CardTitle>
          <CardDescription>Filter visitors by country.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="excluded-countries">Excluded Countries</Label>
            <Textarea
              id="excluded-countries"
              placeholder="US&#10;CN"
              value={excludedCountries as string}
              onChange={(e) => setExcludedCountries(e.target.value)}
              rows={3}
            />
            <p className="text-xs text-muted-foreground">Enter 2-letter country codes to exclude, one per line.</p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="included-countries">Included Countries Only</Label>
            <Textarea
              id="included-countries"
              placeholder="US&#10;CA&#10;GB"
              value={includedCountries as string}
              onChange={(e) => setIncludedCountries(e.target.value)}
              rows={3}
            />
            <p className="text-xs text-muted-foreground">If specified, only track visitors from these countries.</p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>URL Query Parameters</CardTitle>
          <CardDescription>Control which URL query parameters are retained in your statistics.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="query-params">Allowed Query Parameters</Label>
            <Textarea
              id="query-params"
              placeholder="ref&#10;source&#10;utm_source&#10;utm_medium&#10;utm_campaign"
              value={queryParamsAllowList as string}
              onChange={(e) => setQueryParamsAllowList(e.target.value)}
              rows={5}
            />
            <p className="text-xs text-muted-foreground">
              Enter parameter names to retain, one per line. Default: ref, source, utm_source, utm_medium, utm_campaign,
              utm_content, utm_term, utm_id, s, p.
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Bot & Crawler Detection</CardTitle>
          <CardDescription>Filter out bots and search engine crawlers.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="robotlist">Bot User Agent List</Label>
            <Textarea
              id="robotlist"
              placeholder="Googlebot&#10;Bingbot&#10;YandexBot"
              value={robotlist as string}
              onChange={(e) => setRobotlist(e.target.value)}
              rows={5}
            />
            <p className="text-xs text-muted-foreground">Enter bot user agent names to exclude, one per line.</p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="robot-threshold">Daily Hit Threshold</Label>
            <Input
              id="robot-threshold"
              type="number"
              min="0"
              value={robotThreshold as number}
              onChange={(e) => setRobotThreshold(parseInt(e.target.value) || 0)}
              className="w-32"
            />
            <p className="text-xs text-muted-foreground">
              Consider visitors with more than this many daily hits as bots (0 to disable).
            </p>
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="record-exclusions">Record Exclusions</Label>
              <p className="text-sm text-muted-foreground">Log excluded visitors for debugging purposes.</p>
            </div>
            <Switch id="record-exclusions" checked={!!recordExclusions} onCheckedChange={setRecordExclusions} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>User Role Exclusions</CardTitle>
          <CardDescription>Exclude users with specific roles from being tracked.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-admin">Administrators</Label>
              <p className="text-sm text-muted-foreground">Exclude users with administrator role.</p>
            </div>
            <Switch id="exclude-admin" checked={!!excludeAdmin} onCheckedChange={setExcludeAdmin} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-editor">Editors</Label>
              <p className="text-sm text-muted-foreground">Exclude users with editor role.</p>
            </div>
            <Switch id="exclude-editor" checked={!!excludeEditor} onCheckedChange={setExcludeEditor} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-author">Authors</Label>
              <p className="text-sm text-muted-foreground">Exclude users with author role.</p>
            </div>
            <Switch id="exclude-author" checked={!!excludeAuthor} onCheckedChange={setExcludeAuthor} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-contributor">Contributors</Label>
              <p className="text-sm text-muted-foreground">Exclude users with contributor role.</p>
            </div>
            <Switch id="exclude-contributor" checked={!!excludeContributor} onCheckedChange={setExcludeContributor} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-subscriber">Subscribers</Label>
              <p className="text-sm text-muted-foreground">Exclude users with subscriber role.</p>
            </div>
            <Switch id="exclude-subscriber" checked={!!excludeSubscriber} onCheckedChange={setExcludeSubscriber} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-anonymous">Anonymous Users</Label>
              <p className="text-sm text-muted-foreground">Exclude users who are not logged in.</p>
            </div>
            <Switch id="exclude-anonymous" checked={!!excludeAnonymous} onCheckedChange={setExcludeAnonymous} />
          </div>
        </CardContent>
      </Card>

      {settings.error && <NoticeBanner id="settings-error" message={settings.error} type="error" dismissible={false} />}

      <div className="flex justify-end">
        <Button onClick={handleSave} disabled={settings.isSaving}>
          {settings.isSaving && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          Save Changes
        </Button>
      </div>
    </div>
  )
}
