import { __ } from '@wordpress/i18n'
import { LayoutGridIcon } from 'lucide-react'

import { usePageOptions } from '@/hooks/use-page-options'
import { usePremiumFeature } from '@/hooks/use-premium-feature'

import {
  LockedMenuItem,
  OptionsDetailView,
  OptionsMenuItem,
  OptionsToggleItem,
  useOptionsDrawer,
} from './options-drawer'

export function WidgetsMenuEntry() {
  const { widgetConfigs, getHiddenWidgetCount } = usePageOptions()
  const { currentView, setCurrentView } = useOptionsDrawer()
  const { isEnabled, isLoading } = usePremiumFeature('widget-customization')

  if (currentView !== 'main') {
    return null
  }

  // Show locked state if feature is not enabled
  if (!isEnabled && !isLoading) {
    return (
      <LockedMenuItem
        icon={<LayoutGridIcon className="h-4 w-4" />}
        label={__('Widgets', 'wp-statistics')}
      />
    )
  }

  // Don't show if no widget configs
  if (widgetConfigs.length === 0) {
    return null
  }

  const hiddenCount = getHiddenWidgetCount()
  const summary = hiddenCount > 0 ? `${hiddenCount} ${__('hidden', 'wp-statistics')}` : undefined

  return (
    <OptionsMenuItem
      icon={<LayoutGridIcon className="h-4 w-4" />}
      title={__('Widgets', 'wp-statistics')}
      summary={summary}
      onClick={() => setCurrentView('widgets')}
    />
  )
}

export function WidgetsDetailView() {
  const { widgetConfigs, isWidgetVisible, toggleWidget } = usePageOptions()
  const { currentView } = useOptionsDrawer()
  const { isEnabled } = usePremiumFeature('widget-customization')

  if (currentView !== 'widgets' || widgetConfigs.length === 0 || !isEnabled) {
    return null
  }

  return (
    <OptionsDetailView description={__('Show or hide widgets on this page', 'wp-statistics')}>
      <div>
        {widgetConfigs.map((config) => (
          <OptionsToggleItem
            key={config.id}
            label={config.label}
            checked={isWidgetVisible(config.id)}
            onCheckedChange={() => toggleWidget(config.id)}
          />
        ))}
      </div>
    </OptionsDetailView>
  )
}
