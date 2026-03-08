import { __, sprintf } from '@wordpress/i18n'
import { CircleCheck } from 'lucide-react'

import { SettingsField } from '@/components/settings-ui/settings-field'
import { Badge } from '@/components/ui/badge'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import type { UseSettingsReturn } from '@/hooks/use-settings'

interface ConsentProvider {
  key: string
  name: string
  available: boolean
  selectable: boolean
  compatible_plugins?: string[]
}

/**
 * Dynamic consent integration select with disabled states and warnings.
 * Reads `_consent_providers` metadata from settings and renders each provider
 * as a select option, disabling unavailable ones.
 *
 * Shows a warning when WP Consent API is active but has no compatible plugins,
 * or an info notice listing detected compatible plugins when they exist.
 *
 * Registered as a `type: 'component'` field in the privacy tab.
 */
export function ConsentIntegrationField({ settings }: { settings: UseSettingsReturn }) {
  const providers = settings.getValue('_consent_providers', []) as ConsentProvider[]
  const value = settings.getValue('consent_integration', 'none') as string
  const selectedProvider = providers.find((p) => p.key === value)

  // Borlabs physically blocks the tracking script until consent is given.
  // When its service is installed (selectable), the user cannot switch to "None"
  // without breaking tracking.
  const isForcedIntegration = selectedProvider?.key === 'borlabs_cookie' && selectedProvider.selectable

  const isWpConsentSelected = value === 'wp_consent_api'
  const wpConsentProvider = providers.find((p) => p.key === 'wp_consent_api')
  const compatiblePlugins = wpConsentProvider?.compatible_plugins ?? []

  const isOptionDisabled = (provider: ConsentProvider): boolean => {
    if (provider.key === 'none') return isForcedIntegration
    return !provider.selectable
  }

  const getLabel = (provider: ConsentProvider): string => {
    if (provider.key === 'wp_consent_api') {
      return __('WP Consent API', 'wp-statistics')
    }
    return provider.name
  }

  return (
    <>
      <SettingsField
        id="consent-integration"
        label={__('Consent Plugin Integration', 'wp-statistics')}
        description={__('Integrate with supported consent management plugins.', 'wp-statistics')}
      >
        <div className="space-y-1.5">
          <Select value={value} onValueChange={(v) => settings.setValue('consent_integration', v)}>
            <SelectTrigger id="consent-integration" className="w-[200px]">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {providers.map((provider) => (
                <SelectItem
                  key={provider.key}
                  value={provider.key}
                  disabled={isOptionDisabled(provider)}
                >
                  {getLabel(provider)}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          {isWpConsentSelected && compatiblePlugins.length > 0 && (
            <Badge className="bg-emerald-100 text-emerald-700 border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800 dark:hover:bg-emerald-900/30">
              <CircleCheck className="mr-1 h-3 w-3" />
              {sprintf(
                __('Connected via %s', 'wp-statistics'),
                compatiblePlugins.join(', ')
              )}
            </Badge>
          )}
        </div>
      </SettingsField>
      {isWpConsentSelected && compatiblePlugins.length === 0 && (
        <NoticeBanner
          type="warning"
          message={__(
            'WP Consent API is active, but no compatible consent plugin (e.g. Complianz, CookieYes) was detected. Install a compatible plugin to use this integration.',
            'wp-statistics'
          )}
          helpUrl="https://wp-statistics.com/resources/wp-consent-level-integration/?utm_source=plugin&utm_medium=link&utm_campaign=settings"
          dismissible={false}
        />
      )}
      {selectedProvider && isForcedIntegration && (
        <NoticeBanner
          type="info"
          message={sprintf(
            __('%s has been automatically detected with the WP-Statistics service enabled. To disable this integration, remove the WP-Statistics service from %s settings.', 'wp-statistics'),
            selectedProvider.name,
            selectedProvider.name
          )}
          dismissible={false}
        />
      )}
    </>
  )
}
