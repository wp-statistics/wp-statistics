import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Switch } from '@/components/ui/switch'
import { Textarea } from '@/components/ui/textarea'
import { useSetting, useSettings } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'

export function ExclusionSettings() {
  const settings = useSettings({ tab: 'exclusions' })
  const { toast } = useToast()

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
      toast({
        title: __('Settings saved', 'wp-statistics'),
        description: __('Exclusion settings have been updated.', 'wp-statistics'),
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
          <CardTitle>{__('Page Exclusions', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Exclude specific pages or paths from being tracked.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-loginpage">{__('Exclude Login Page', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__("Don't track WordPress login page visits.", 'wp-statistics')}</p>
            </div>
            <Switch id="exclude-loginpage" checked={!!excludeLoginpage} onCheckedChange={setExcludeLoginpage} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-feeds">{__('Exclude RSS Feeds', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__("Don't count RSS feed requests in statistics.", 'wp-statistics')}</p>
            </div>
            <Switch id="exclude-feeds" checked={!!excludeFeeds} onCheckedChange={setExcludeFeeds} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-404s">{__('Exclude 404 Pages', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__("Don't track visits to pages that return 404 errors.", 'wp-statistics')}</p>
            </div>
            <Switch id="exclude-404s" checked={!!exclude404s} onCheckedChange={setExclude404s} />
          </div>

          <div className="space-y-2">
            <Label htmlFor="exclude-urls">{__('Excluded URLs', 'wp-statistics')}</Label>
            <Textarea
              id="exclude-urls"
              placeholder="/admin&#10;/wp-json&#10;/api/*"
              value={excludedUrls as string}
              onChange={(e) => setExcludedUrls(e.target.value)}
              rows={4}
            />
            <p className="text-xs text-muted-foreground">
              {__('Enter URL paths to exclude, one per line. Supports wildcards (*).', 'wp-statistics')}
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('IP Exclusions', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Exclude specific IP addresses from being tracked.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="exclude-ips">{__('Excluded IP Addresses', 'wp-statistics')}</Label>
            <Textarea
              id="exclude-ips"
              placeholder="192.168.1.1&#10;10.0.0.0/8"
              value={excludeIp as string}
              onChange={(e) => setExcludeIp(e.target.value)}
              rows={4}
            />
            <p className="text-xs text-muted-foreground">{__('Enter IP addresses or CIDR ranges, one per line.', 'wp-statistics')}</p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('Country Filters', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Filter visitors by country.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="excluded-countries">{__('Excluded Countries', 'wp-statistics')}</Label>
            <Textarea
              id="excluded-countries"
              placeholder="US&#10;CN"
              value={excludedCountries as string}
              onChange={(e) => setExcludedCountries(e.target.value)}
              rows={3}
            />
            <p className="text-xs text-muted-foreground">{__('Enter 2-letter country codes to exclude, one per line.', 'wp-statistics')}</p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="included-countries">{__('Included Countries Only', 'wp-statistics')}</Label>
            <Textarea
              id="included-countries"
              placeholder="US&#10;CA&#10;GB"
              value={includedCountries as string}
              onChange={(e) => setIncludedCountries(e.target.value)}
              rows={3}
            />
            <p className="text-xs text-muted-foreground">{__('If specified, only track visitors from these countries.', 'wp-statistics')}</p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('URL Query Parameters', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Control which URL query parameters are retained in your statistics.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="query-params">{__('Allowed Query Parameters', 'wp-statistics')}</Label>
            <Textarea
              id="query-params"
              placeholder="ref&#10;source&#10;utm_source&#10;utm_medium&#10;utm_campaign"
              value={queryParamsAllowList as string}
              onChange={(e) => setQueryParamsAllowList(e.target.value)}
              rows={5}
            />
            <p className="text-xs text-muted-foreground">
              {__('Enter parameter names to retain, one per line. Default: ref, source, utm_source, utm_medium, utm_campaign, utm_content, utm_term, utm_id, s, p.', 'wp-statistics')}
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('Bot & Crawler Detection', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Filter out bots and search engine crawlers.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="robotlist">{__('Bot User Agent List', 'wp-statistics')}</Label>
            <Textarea
              id="robotlist"
              placeholder="Googlebot&#10;Bingbot&#10;YandexBot"
              value={robotlist as string}
              onChange={(e) => setRobotlist(e.target.value)}
              rows={5}
            />
            <p className="text-xs text-muted-foreground">{__('Enter bot user agent names to exclude, one per line.', 'wp-statistics')}</p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="robot-threshold">{__('Daily Hit Threshold', 'wp-statistics')}</Label>
            <Input
              id="robot-threshold"
              type="number"
              min="0"
              value={robotThreshold as number}
              onChange={(e) => setRobotThreshold(parseInt(e.target.value) || 0)}
              className="w-32"
            />
            <p className="text-xs text-muted-foreground">
              {__('Consider visitors with more than this many daily hits as bots (0 to disable).', 'wp-statistics')}
            </p>
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="record-exclusions">{__('Record Exclusions', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__('Log excluded visitors for debugging purposes.', 'wp-statistics')}</p>
            </div>
            <Switch id="record-exclusions" checked={!!recordExclusions} onCheckedChange={setRecordExclusions} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{__('User Role Exclusions', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Exclude users with specific roles from being tracked.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-admin">{__('Administrators', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__('Exclude users with administrator role.', 'wp-statistics')}</p>
            </div>
            <Switch id="exclude-admin" checked={!!excludeAdmin} onCheckedChange={setExcludeAdmin} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-editor">{__('Editors', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__('Exclude users with editor role.', 'wp-statistics')}</p>
            </div>
            <Switch id="exclude-editor" checked={!!excludeEditor} onCheckedChange={setExcludeEditor} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-author">{__('Authors', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__('Exclude users with author role.', 'wp-statistics')}</p>
            </div>
            <Switch id="exclude-author" checked={!!excludeAuthor} onCheckedChange={setExcludeAuthor} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-contributor">{__('Contributors', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__('Exclude users with contributor role.', 'wp-statistics')}</p>
            </div>
            <Switch id="exclude-contributor" checked={!!excludeContributor} onCheckedChange={setExcludeContributor} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-subscriber">{__('Subscribers', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__('Exclude users with subscriber role.', 'wp-statistics')}</p>
            </div>
            <Switch id="exclude-subscriber" checked={!!excludeSubscriber} onCheckedChange={setExcludeSubscriber} />
          </div>

          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label htmlFor="exclude-anonymous">{__('Anonymous Users', 'wp-statistics')}</Label>
              <p className="text-sm text-muted-foreground">{__('Exclude users who are not logged in.', 'wp-statistics')}</p>
            </div>
            <Switch id="exclude-anonymous" checked={!!excludeAnonymous} onCheckedChange={setExcludeAnonymous} />
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
