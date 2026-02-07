import { __ } from '@wordpress/i18n'

import { SettingsCard, SettingsPage, SettingsSelectField, SettingsToggleField } from '@/components/settings-ui'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { useSetting, useSettings } from '@/hooks/use-settings'

export function PrivacySettings() {
  const settings = useSettings({ tab: 'privacy' })

  const [storeIp, setStoreIp] = useSetting(settings, 'store_ip', false)
  const [hashRotationInterval, setHashRotationInterval] = useSetting(settings, 'hash_rotation_interval', 'daily')
  const [anonymousTracking, setAnonymousTracking] = useSetting(settings, 'anonymous_tracking', false)
  const [consentIntegration, setConsentIntegration] = useSetting(settings, 'consent_integration', 'none')
  const [consentLevel, setConsentLevel] = useSetting(settings, 'consent_level_integration', 'functional')
  const [privacyAudit, setPrivacyAudit] = useSetting(settings, 'privacy_audit', true)

  return (
    <SettingsPage settings={settings} saveDescription={__('Privacy settings have been updated.', 'wp-statistics')}>
      <SettingsCard
        title={__('Data Protection', 'wp-statistics')}
        description={__('Configure how visitor IP addresses are stored and processed.', 'wp-statistics')}
      >
        <SettingsToggleField
          id="store-ip"
          label={__('Store IP Addresses', 'wp-statistics')}
          description={__('Record full visitor IP addresses in the database. When disabled, only anonymous hashes are stored.', 'wp-statistics')}
          checked={!!storeIp}
          onCheckedChange={setStoreIp}
        />

        <SettingsSelectField
          id="hash-rotation-interval"
          label={__('Hash Rotation Interval', 'wp-statistics')}
          description={__('How often the salt used for visitor hashing rotates. Shorter intervals improve privacy but reduce returning-visitor detection accuracy.', 'wp-statistics')}
          value={hashRotationInterval as string}
          onValueChange={setHashRotationInterval}
          placeholder={__('Select interval', 'wp-statistics')}
          options={[
            { value: 'daily', label: __('Daily', 'wp-statistics') },
            { value: 'weekly', label: __('Weekly', 'wp-statistics') },
            { value: 'monthly', label: __('Monthly', 'wp-statistics') },
            { value: 'disabled', label: __('Disabled', 'wp-statistics') },
          ]}
        />

        {hashRotationInterval === 'disabled' && (
          <NoticeBanner
            id="hash-rotation-warning"
            type="warning"
            message={__('Disabling hash rotation means the same visitor will always produce the same hash. This improves returning-visitor detection but reduces privacy protection.', 'wp-statistics')}
            dismissible={false}
          />
        )}
      </SettingsCard>

      <SettingsCard
        title={__('User Preferences', 'wp-statistics')}
        description={__('Configure consent integration and respect user privacy preferences.', 'wp-statistics')}
      >
        <SettingsSelectField
          id="consent-integration"
          label={__('Consent Plugin Integration', 'wp-statistics')}
          description={__('Integrate with supported consent management plugins.', 'wp-statistics')}
          value={consentIntegration as string}
          onValueChange={setConsentIntegration}
          placeholder={__('Select plugin', 'wp-statistics')}
          options={[
            { value: 'none', label: __('None', 'wp-statistics') },
            { value: 'wp_consent_api', label: __('Via WP Consent API', 'wp-statistics') },
            { value: 'complianz', label: __('Complianz', 'wp-statistics') },
            { value: 'cookieyes', label: __('CookieYes', 'wp-statistics') },
            { value: 'real_cookie_banner', label: __('Real Cookie Banner', 'wp-statistics') },
            { value: 'borlabs_cookie', label: __('Borlabs Cookie', 'wp-statistics') },
          ]}
        />

        {consentIntegration === 'wp_consent_api' && (
          <SettingsSelectField
            id="consent-level"
            label={__('Consent Category', 'wp-statistics')}
            description={__('Select the consent category WP Statistics should track.', 'wp-statistics')}
            nested
            value={consentLevel as string}
            onValueChange={setConsentLevel}
            placeholder={__('Select category', 'wp-statistics')}
            options={[
              { value: 'functional', label: __('Functional', 'wp-statistics') },
              { value: 'statistics-anonymous', label: __('Statistics-Anonymous', 'wp-statistics') },
              { value: 'statistics', label: __('Statistics', 'wp-statistics') },
              { value: 'marketing', label: __('Marketing', 'wp-statistics') },
            ]}
          />
        )}

        {consentIntegration && consentIntegration !== 'none' && (
          <SettingsToggleField
            id="anonymous-tracking"
            label={__('Anonymous Tracking', 'wp-statistics')}
            description={__('Track all users anonymously without PII by default.', 'wp-statistics')}
            checked={!!anonymousTracking}
            onCheckedChange={setAnonymousTracking}
            nested
          />
        )}
      </SettingsCard>

      <SettingsCard
        title={__('Privacy Audit', 'wp-statistics')}
        description={__('Enable privacy monitoring and compliance tools.', 'wp-statistics')}
      >
        <SettingsToggleField
          id="privacy-audit"
          label={__('Enable Privacy Audit', 'wp-statistics')}
          description={__('Show privacy indicators on settings that affect user privacy.', 'wp-statistics')}
          checked={!!privacyAudit}
          onCheckedChange={setPrivacyAudit}
        />
      </SettingsCard>
    </SettingsPage>
  )
}
