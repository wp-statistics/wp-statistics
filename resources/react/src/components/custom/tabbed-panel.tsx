import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'

import { EmptyState } from '@/components/ui/empty-state'
import { Panel, PanelAction, PanelContent, PanelFooter, PanelHeader, PanelTitle } from '@/components/ui/panel'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'

import { BarListHeader } from './bar-list-header'

export interface TabbedPanelTab {
  /** Unique identifier */
  id: string
  /** Tab button text */
  label: string
  /** Tab content - accepts any React node */
  content: React.ReactNode
  /** Optional footer link */
  link?: {
    /** Route with query params */
    href?: string
    /** Link text (default: "See all") */
    title?: string
  }
  /** Optional column headers for list content */
  columnHeaders?: {
    left: string
    right: string
  }
}

export interface TabbedPanelProps {
  /** Panel header title */
  title: string
  /** Tab configurations */
  tabs: TabbedPanelTab[]
  /** Default active tab ID */
  defaultTab?: string
  /** Loading state */
  loading?: boolean
}

/**
 * TabbedPanel - A composable tabbed panel component
 *
 * Displays a panel with tabs, where each tab renders custom React content.
 * This allows for flexible content rendering including HorizontalBar, lists, or any other component.
 *
 * @example
 * <TabbedPanel
 *   title="Top Content"
 *   tabs={[
 *     {
 *       id: 'popular',
 *       label: 'Most Popular',
 *       content: (
 *         <div className="flex flex-col gap-3">
 *           {items.map((item, i) => (
 *             <HorizontalBar key={i} label={item.label} value={item.value} />
 *           ))}
 *         </div>
 *       ),
 *       link: { href: '/top-pages?order_by=views' }
 *     }
 *   ]}
 * />
 */
export function TabbedPanel({ title, tabs, defaultTab, loading = false }: TabbedPanelProps) {
  const activeTab = defaultTab || tabs[0]?.id

  // Handle empty tabs array
  if (tabs.length === 0 && !loading) {
    return (
      <Panel className="h-full flex flex-col">
        <PanelHeader>
          <PanelTitle>{title}</PanelTitle>
        </PanelHeader>
        <PanelContent className="flex-1">
          <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
        </PanelContent>
      </Panel>
    )
  }

  return (
    <Panel className="h-full flex flex-col">
      <Tabs defaultValue={activeTab} className="flex flex-col h-full">
        <PanelHeader>
          <PanelTitle>{title}</PanelTitle>
          <TabsList className="h-7">
            {tabs.map((tab) => (
              <TabsTrigger key={tab.id} value={tab.id} className="text-xs px-2 py-1">
                {tab.label}
              </TabsTrigger>
            ))}
          </TabsList>
        </PanelHeader>

        <PanelContent className="flex-1">
          {loading ? (
            <div className="flex h-32 flex-1 flex-col items-center justify-center">
              <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
            </div>
          ) : (
            tabs.map((tab) => (
              <TabsContent key={tab.id} value={tab.id} className="mt-0">
                {tab.columnHeaders && (
                  <BarListHeader left={tab.columnHeaders.left} right={tab.columnHeaders.right} />
                )}
                {tab.content}
              </TabsContent>
            ))
          )}
        </PanelContent>

        {/* Render footer with link for the currently active tab */}
        {tabs.map((tab) => (
          <TabsContent key={`footer-${tab.id}`} value={tab.id} className="mt-0">
            {tab.link?.href && (
              <PanelFooter>
                <PanelAction href={tab.link.href}>{tab.link.title || __('See all', 'wp-statistics')}</PanelAction>
              </PanelFooter>
            )}
          </TabsContent>
        ))}
      </Tabs>
    </Panel>
  )
}
