import { __, sprintf } from '@wordpress/i18n'
import { CircleCheck, ShieldCheck } from 'lucide-react'

import { SettingsField } from '@/components/settings-ui/settings-field'
import { Badge } from '@/components/ui/badge'
import { NoticeBanner } from '@/components/ui/notice-banner'
import type { UseSettingsReturn } from '@/hooks/use-settings'

interface ConsentProvider {
  key: string
  name: string
  available: boolean
  selectable: boolean
  compatible_plugins?: string[]
}

/**
 * Consent integration status display.
 * Shows auto-detected provider info, conflict warnings, and WP Consent API compatibility.
 * Rendered below the consent_integration toggle when it's enabled.
 *
 * Registered as a `type: 'component'` field in the privacy tab.
 */
export function ConsentIntegrationField({ settings }: { settings: UseSettingsReturn }) {
  const enabled = settings.getValue('consent_integration', false) as boolean
  if (!enabled) return null

  const providers = settings.getValue('_consent_providers', []) as ConsentProvider[]
  const activeKey = settings.getValue('_active_consent_provider', 'none') as string
  const hasConflict = settings.getValue('_has_conflicting_providers', false) as boolean

  const activeProvider = providers.find((p) => p.key === activeKey)
  const isNone = activeKey === 'none'

  const wpConsentProvider = providers.find((p) => p.key === 'wp_consent_api')
  const isWpConsentActive = activeKey === 'wp_consent_api'
  const compatiblePlugins = wpConsentProvider?.compatible_plugins ?? []

  return (
    <>
      <SettingsField id="consent-integration-status">
        {isNone ? (
          <Badge variant="outline" className="text-muted-foreground">
            {__('No consent plugin detected', 'wp-statistics')}
          </Badge>
        ) : (
          <div className="flex items-center gap-1.5 flex-wrap">
            <Badge className="bg-emerald-100 text-emerald-700 border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800 dark:hover:bg-emerald-900/30">
              <ShieldCheck className="mr-1 h-3 w-3" />
              {activeProvider?.name ?? activeKey}
            </Badge>
            {isWpConsentActive && compatiblePlugins.length > 0 && (
              <Badge className="bg-emerald-100 text-emerald-700 border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800 dark:hover:bg-emerald-900/30">
                <CircleCheck className="mr-1 h-3 w-3" />
                {sprintf(
                  __('Connected via %s', 'wp-statistics'),
                  compatiblePlugins.join(', ')
                )}
              </Badge>
            )}
          </div>
        )}
      </SettingsField>
      {hasConflict && (
        <NoticeBanner
          type="warning"
          message={__(
            'Multiple consent plugins detected. This may cause conflicts. We recommend keeping only one active consent plugin.',
            'wp-statistics'
          )}
          dismissible={false}
        />
      )}
      {isWpConsentActive && compatiblePlugins.length === 0 && (
        <NoticeBanner
          type="warning"
          message={__(
            'WP Consent API is active, but no compatible consent plugin (e.g. Complianz, CookieYes) was detected. Install a compatible plugin to enforce consent-based tracking.',
            'wp-statistics'
          )}
          helpUrl="https://wp-statistics.com/resources/wp-consent-level-integration/?utm_source=plugin&utm_medium=link&utm_campaign=settings"
          dismissible={false}
        />
      )}
    </>
  )
}
