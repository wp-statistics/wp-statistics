import { __ } from '@wordpress/i18n'
import { ShieldCheck, TriangleAlert } from 'lucide-react'

import { Badge } from '@/components/ui/badge'
import { NoticeBanner } from '@/components/ui/notice-banner'
import type { UseSettingsReturn } from '@/hooks/use-settings'

interface ConsentProvider {
  key: string
  name: string
  available: boolean
  compatible_plugins?: string[]
}

const badgeGreen =
  'bg-emerald-100 text-emerald-700 border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800 dark:hover:bg-emerald-900/30'
const badgeAmber =
  'bg-amber-100 text-amber-700 border-amber-200 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800 dark:hover:bg-amber-900/30'

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

  const isNone = activeKey === 'none'
  const availableProviders = providers.filter((p) => p.available && p.key !== 'none')

  const wpConsentProvider = providers.find((p) => p.key === 'wp_consent_api')
  const wpConsentAvailable = wpConsentProvider?.available ?? false
  const compatiblePlugins = wpConsentProvider?.compatible_plugins ?? []
  const wpConsentHasBanner = compatiblePlugins.length > 0

  const getBadgeStyle = (provider: ConsentProvider): string => {
    // WP Consent API without compatible banner → warning style
    if (provider.key === 'wp_consent_api' && !wpConsentHasBanner) {
      return badgeAmber
    }
    // In conflict scenario, active = green, others = amber
    if (hasConflict) {
      return provider.key === activeKey ? badgeGreen : badgeAmber
    }
    return badgeGreen
  }

  const getBadgeIcon = (provider: ConsentProvider) => {
    if (provider.key === 'wp_consent_api' && !wpConsentHasBanner) {
      return <TriangleAlert className="mr-1 h-3 w-3" />
    }
    return <ShieldCheck className="mr-1 h-3 w-3" />
  }

  return (
    <div className="-mt-2 space-y-3">
      <div className="flex items-center gap-1.5 flex-wrap">
        {isNone ? (
          <Badge variant="outline" className="text-muted-foreground">
            {__('No consent plugin detected', 'wp-statistics')}
          </Badge>
        ) : (
          availableProviders.map((provider) => (
            <Badge key={provider.key} className={getBadgeStyle(provider)}>
              {getBadgeIcon(provider)}
              {provider.name}
            </Badge>
          ))
        )}
      </div>
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
      {wpConsentAvailable && !wpConsentHasBanner && (
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
    </div>
  )
}
