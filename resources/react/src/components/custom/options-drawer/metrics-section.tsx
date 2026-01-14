import { GaugeIcon } from 'lucide-react'
import { __ } from '@wordpress/i18n'

import { usePageOptions } from '@/hooks/use-page-options'
import { usePremiumFeature } from '@/hooks/use-premium-feature'

import {
  useOptionsDrawer,
  OptionsMenuItem,
  OptionsDetailView,
  OptionsToggleItem,
  LockedMenuItem,
} from './options-drawer'

export function MetricsMenuEntry() {
  const { metricConfigs, getHiddenMetricCount } = usePageOptions()
  const { currentView, setCurrentView } = useOptionsDrawer()
  const { isEnabled, isLoading } = usePremiumFeature('metric-customization')

  if (currentView !== 'main') {
    return null
  }

  // Show locked state if feature is not enabled
  if (!isEnabled && !isLoading) {
    return (
      <LockedMenuItem
        icon={<GaugeIcon className="h-4 w-4" />}
        label={__('Metrics', 'wp-statistics')}
      />
    )
  }

  // Don't show if no metric configs
  if (metricConfigs.length === 0) {
    return null
  }

  const hiddenCount = getHiddenMetricCount()
  const summary = hiddenCount > 0 ? `${hiddenCount} ${__('hidden', 'wp-statistics')}` : undefined

  return (
    <OptionsMenuItem
      icon={<GaugeIcon className="h-4 w-4" />}
      title={__('Metrics', 'wp-statistics')}
      summary={summary}
      onClick={() => setCurrentView('metrics')}
    />
  )
}

export function MetricsDetailView() {
  const { metricConfigs, isMetricVisible, toggleMetric } = usePageOptions()
  const { currentView } = useOptionsDrawer()
  const { isEnabled } = usePremiumFeature('metric-customization')

  if (currentView !== 'metrics' || metricConfigs.length === 0 || !isEnabled) {
    return null
  }

  return (
    <OptionsDetailView description={__('Show or hide metrics in the overview', 'wp-statistics')}>
      <div>
        {metricConfigs.map((config) => (
          <OptionsToggleItem
            key={config.id}
            label={config.label}
            checked={isMetricVisible(config.id)}
            onCheckedChange={() => toggleMetric(config.id)}
          />
        ))}
      </div>
    </OptionsDetailView>
  )
}
