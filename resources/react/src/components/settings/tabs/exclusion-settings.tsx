import { __, sprintf } from '@wordpress/i18n'

import { SettingsCard, SettingsField, SettingsPage, SettingsToggleField } from '@/components/settings-ui'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { useSetting, useSettings, type UseSettingsReturn } from '@/hooks/use-settings'

function RoleExclusionToggle({ settings, role }: { settings: UseSettingsReturn; role: { slug: string; name: string } }) {
  const key = `exclude_${role.slug}`
  return (
    <SettingsToggleField
      id={`exclude-${role.slug}`}
      label={role.name}
      description={sprintf(__('Exclude users with %s role.', 'wp-statistics'), role.name.toLowerCase())}
      checked={!!settings.getValue(key, false)}
      onCheckedChange={(v) => settings.setValue(key, v)}
    />
  )
}

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

  // Role Exclusions (dynamic from backend)
  const roles = settings.getValue('_roles', []) as { slug: string; name: string }[]

  // Query Parameters
  const [queryParamsAllowList, setQueryParamsAllowList] = useSetting(settings, 'query_params_allow_list', '')

  return (
    <SettingsPage settings={settings} saveDescription={__('Exclusion settings have been updated.', 'wp-statistics')}>
      <SettingsCard
        title={__('Page Exclusions', 'wp-statistics')}
        description={__('Exclude specific pages or paths from being tracked.', 'wp-statistics')}
      >
        <SettingsToggleField
          id="exclude-loginpage"
          label={__('Exclude Login Page', 'wp-statistics')}
          description={__("Don't track WordPress login page visits.", 'wp-statistics')}
          checked={!!excludeLoginpage}
          onCheckedChange={setExcludeLoginpage}
        />

        <SettingsToggleField
          id="exclude-feeds"
          label={__('Exclude RSS Feeds', 'wp-statistics')}
          description={__("Don't count RSS feed requests in statistics.", 'wp-statistics')}
          checked={!!excludeFeeds}
          onCheckedChange={setExcludeFeeds}
        />

        <SettingsToggleField
          id="exclude-404s"
          label={__('Exclude 404 Pages', 'wp-statistics')}
          description={__("Don't track visits to pages that return 404 errors.", 'wp-statistics')}
          checked={!!exclude404s}
          onCheckedChange={setExclude404s}
        />

        <SettingsField
          id="exclude-urls"
          label={__('Excluded URLs', 'wp-statistics')}
          description={__('Enter URL paths to exclude, one per line. Supports wildcards (*).', 'wp-statistics')}
          layout="stacked"
        >
          <Textarea
            id="exclude-urls"
            placeholder="/admin&#10;/wp-json&#10;/api/*"
            value={excludedUrls as string}
            onChange={(e) => setExcludedUrls(e.target.value)}
            rows={4}
          />
        </SettingsField>
      </SettingsCard>

      <SettingsCard
        title={__('User Role Exclusions', 'wp-statistics')}
        description={__('Exclude users with specific roles from being tracked.', 'wp-statistics')}
      >
        {roles.map((role) => (
          <RoleExclusionToggle key={role.slug} settings={settings} role={role} />
        ))}
        <SettingsToggleField
          id="exclude-anonymous"
          label={__('Anonymous Users', 'wp-statistics')}
          description={__('Exclude users who are not logged in.', 'wp-statistics')}
          checked={!!settings.getValue('exclude_anonymous_users', false)}
          onCheckedChange={(v) => settings.setValue('exclude_anonymous_users', v)}
        />
      </SettingsCard>

      <SettingsCard
        title={__('IP Exclusions', 'wp-statistics')}
        description={__('Exclude specific IP addresses from being tracked.', 'wp-statistics')}
      >
        <SettingsField
          id="exclude-ips"
          label={__('Excluded IP Addresses', 'wp-statistics')}
          description={__('Enter IP addresses or CIDR ranges, one per line.', 'wp-statistics')}
          layout="stacked"
        >
          <Textarea
            id="exclude-ips"
            placeholder="192.168.1.1&#10;10.0.0.0/8"
            value={excludeIp as string}
            onChange={(e) => setExcludeIp(e.target.value)}
            rows={4}
          />
        </SettingsField>
      </SettingsCard>

      <SettingsCard
        title={__('Country Filters', 'wp-statistics')}
        description={__('Filter visitors by country.', 'wp-statistics')}
      >
        <SettingsField
          id="excluded-countries"
          label={__('Excluded Countries', 'wp-statistics')}
          description={__('Enter 2-letter country codes to exclude, one per line.', 'wp-statistics')}
          layout="stacked"
        >
          <Textarea
            id="excluded-countries"
            placeholder="US&#10;CN"
            value={excludedCountries as string}
            onChange={(e) => setExcludedCountries(e.target.value)}
            rows={3}
          />
        </SettingsField>

        <SettingsField
          id="included-countries"
          label={__('Included Countries Only', 'wp-statistics')}
          description={__('If specified, only track visitors from these countries.', 'wp-statistics')}
          layout="stacked"
        >
          <Textarea
            id="included-countries"
            placeholder="US&#10;CA&#10;GB"
            value={includedCountries as string}
            onChange={(e) => setIncludedCountries(e.target.value)}
            rows={3}
          />
        </SettingsField>
      </SettingsCard>

      <SettingsCard
        title={__('URL Query Parameters', 'wp-statistics')}
        description={__('Control which URL query parameters are retained in your statistics.', 'wp-statistics')}
      >
        <SettingsField
          id="query-params"
          label={__('Allowed Query Parameters', 'wp-statistics')}
          description={__('Enter parameter names to retain, one per line. Default: ref, source, utm_source, utm_medium, utm_campaign, utm_content, utm_term, utm_id, s, p.', 'wp-statistics')}
          layout="stacked"
        >
          <Textarea
            id="query-params"
            placeholder="ref&#10;source&#10;utm_source&#10;utm_medium&#10;utm_campaign"
            value={queryParamsAllowList as string}
            onChange={(e) => setQueryParamsAllowList(e.target.value)}
            rows={5}
          />
        </SettingsField>
      </SettingsCard>

      <SettingsCard
        title={__('Bot & Crawler Detection', 'wp-statistics')}
        description={__('Filter out bots and search engine crawlers.', 'wp-statistics')}
      >
        <SettingsField
          id="robotlist"
          label={__('Bot User Agent List', 'wp-statistics')}
          description={__('Enter bot user agent names to exclude, one per line.', 'wp-statistics')}
          layout="stacked"
        >
          <Textarea
            id="robotlist"
            placeholder="Googlebot&#10;Bingbot&#10;YandexBot"
            value={robotlist as string}
            onChange={(e) => setRobotlist(e.target.value)}
            rows={5}
          />
        </SettingsField>

        <SettingsField
          id="robot-threshold"
          label={__('Daily Hit Threshold', 'wp-statistics')}
          description={__('Consider visitors with more than this many daily hits as bots (0 to disable).', 'wp-statistics')}
          layout="stacked"
        >
          <Input
            id="robot-threshold"
            type="number"
            min="0"
            value={robotThreshold as number}
            onChange={(e) => setRobotThreshold(parseInt(e.target.value) || 0)}
            className="w-32"
          />
        </SettingsField>

        <SettingsToggleField
          id="record-exclusions"
          label={__('Record Exclusions', 'wp-statistics')}
          description={__('Log excluded visitors for debugging purposes.', 'wp-statistics')}
          checked={!!recordExclusions}
          onCheckedChange={setRecordExclusions}
        />
      </SettingsCard>
    </SettingsPage>
  )
}
