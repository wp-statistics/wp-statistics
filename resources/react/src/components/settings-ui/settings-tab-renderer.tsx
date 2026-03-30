import { Suspense } from 'react'

import { SettingsCard, SettingsPage } from '@/components/settings-ui'
import { FieldRenderer } from '@/components/settings-ui/field-renderers'
import { evaluateVisibleWhen } from '@/components/settings-ui/visible-when'
import { useSettings } from '@/hooks/use-settings'
import {
  getSettingsCards,
  getSettingsComponent,
  getSettingsFields,
  useSettingsConfig,
} from '@/registry/settings-registry'

/**
 * Generic renderer for a settings tab.
 *
 * - If the tab has `component` set, renders that registered component directly.
 * - Otherwise, iterates cards and fields from the PHP config.
 */
export function SettingsTabRenderer({ tabId }: { tabId: string }) {
  const { config, isLoading: configLoading, error } = useSettingsConfig()

  if (configLoading || !config) {
    return null
  }

  if (error) {
    return <div className="p-4 text-destructive">{error}</div>
  }

  const tabConfig = config.tabs[tabId]
  if (!tabConfig) {
    return null
  }

  // Component-based tab: render the registered component directly.
  if (tabConfig.component) {
    const Component = getSettingsComponent(tabConfig.component)
    if (!Component) return null
    return (
      <Suspense>
        <Component />
      </Suspense>
    )
  }

  // Declarative tab: render cards + fields from config.
  return <DeclarativeTab tabId={tabId} />
}

/**
 * Renders a declarative tab: wraps in SettingsPage, iterates cards, renders fields.
 */
function DeclarativeTab({ tabId }: { tabId: string }) {
  const { config } = useSettingsConfig()
  const tabConfig = config!.tabs[tabId]
  const tabKey = tabConfig.tab_key ?? tabId
  const settings = useSettings({ tab: tabKey })
  const cards = getSettingsCards(config!, tabId)

  return (
    <SettingsPage settings={settings} saveDescription={tabConfig.save_description ?? ''}>
      {cards.map((card) => {
        // Card-level visible_when
        if (!evaluateVisibleWhen(card.visible_when, settings)) {
          return null
        }

        // Component-based card
        if (card.type === 'component' && card.component) {
          const CardComponent = getSettingsComponent(card.component)
          if (!CardComponent) return null
          return (
            <Suspense key={card.id}>
              <CardComponent settings={settings} />
            </Suspense>
          )
        }

        // Declarative card
        const fields = getSettingsFields(config!, tabId, card.id)

        return (
          <SettingsCard
            key={card.id}
            title={card.title}
            description={card.description}
            variant={card.variant}
          >
            {fields.map((field) => (
              <FieldRenderer key={field.id} field={field} settings={settings} />
            ))}
          </SettingsCard>
        )
      })}
    </SettingsPage>
  )
}
