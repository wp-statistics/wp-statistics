import { __ } from '@wordpress/i18n'

import { SettingsCard, SettingsPage, SettingsToggleField } from '@/components/settings-ui'
import { useSetting, useSettings } from '@/hooks/use-settings'

export function GeneralSettings() {
  const settings = useSettings({ tab: 'general' })

  const [visitorsLog, setVisitorsLog] = useSetting(settings, 'visitors_log', false)
  const [bypassAdBlockers, setBypassAdBlockers] = useSetting(settings, 'bypass_ad_blockers', false)

  return (
    <SettingsPage settings={settings} saveDescription={__('General settings have been updated.', 'wp-statistics')}>
      <SettingsCard
        title={__('Tracking Options', 'wp-statistics')}
        description={__('Configure what data WP Statistics collects from your visitors.', 'wp-statistics')}
      >
        <SettingsToggleField
          id="visitors-log"
          label={__('Track Logged-In User Activity', 'wp-statistics')}
          description={__('Tracks activities of logged-in users with their WordPress User IDs. If disabled, logged-in users are tracked anonymously.', 'wp-statistics')}
          checked={!!visitorsLog}
          onCheckedChange={setVisitorsLog}
        />
      </SettingsCard>

      <SettingsCard
        title={__('Tracker Configuration', 'wp-statistics')}
        description={__('Configure how the tracking script works on your site.', 'wp-statistics')}
      >
        <SettingsToggleField
          id="bypass-ad-blockers"
          label={__('Bypass Ad Blockers', 'wp-statistics')}
          description={__('Dynamically load the tracking script with a unique name and address to bypass ad blockers.', 'wp-statistics')}
          checked={!!bypassAdBlockers}
          onCheckedChange={setBypassAdBlockers}
        />
      </SettingsCard>
    </SettingsPage>
  )
}
