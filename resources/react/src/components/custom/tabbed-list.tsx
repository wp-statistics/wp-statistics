import { Link } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { FileText, Loader2 } from 'lucide-react'

import { Panel, PanelAction, PanelContent, PanelFooter, PanelHeader, PanelTitle } from '@/components/ui/panel'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { cn } from '@/lib/utils'

/**
 * Parses an href string into path and search params for TanStack Router.
 * This ensures search params are properly handled with hash history,
 * avoiding WordPress query param interference on page reload.
 */
function parseHref(href: string): { path: string; search?: Record<string, string> } {
  const questionMarkIndex = href.indexOf('?')
  if (questionMarkIndex === -1) {
    return { path: href }
  }

  const path = href.substring(0, questionMarkIndex)
  const queryString = href.substring(questionMarkIndex + 1)
  const search: Record<string, string> = {}

  new URLSearchParams(queryString).forEach((value, key) => {
    search[key] = value
  })

  return Object.keys(search).length > 0 ? { path, search } : { path }
}

export interface TabbedListItem {
  id: string
  title: string
  subtitle?: string
  thumbnail?: string
  href?: string
}

export interface TabbedListTab {
  id: string
  label: string
  items: TabbedListItem[]
  link?: {
    title: string
    href?: string
    action?: () => void
  }
}

interface TabbedListProps {
  title: string
  tabs: TabbedListTab[]
  activeTab: string
  onTabChange: (tabId: string) => void
  loading?: boolean
  emptyMessage?: string
  className?: string
}

export function TabbedList({
  title,
  tabs,
  activeTab,
  onTabChange,
  loading = false,
  emptyMessage,
  className,
}: TabbedListProps) {
  const currentTab = tabs.find((tab) => tab.id === activeTab) || tabs[0]
  const items = currentTab?.items || []

  return (
    <Panel className={cn('h-full flex flex-col', className)}>
      <PanelHeader className="flex-wrap gap-2">
        <PanelTitle>{title}</PanelTitle>
        {tabs.length > 1 && (
          <Tabs value={activeTab} onValueChange={onTabChange} className="w-full sm:w-auto sm:ml-auto">
            <TabsList className="h-8 w-full sm:w-auto">
              {tabs.map((tab) => (
                <TabsTrigger key={tab.id} value={tab.id} className="text-xs px-3 h-7 flex-1 sm:flex-initial">
                  {tab.label}
                </TabsTrigger>
              ))}
            </TabsList>
          </Tabs>
        )}
      </PanelHeader>

      <PanelContent className="flex-1">
        {loading ? (
          <div className="flex h-32 flex-1 flex-col items-center justify-center">
            <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
          </div>
        ) : items.length === 0 ? (
          <div className="flex h-32 flex-1 flex-col items-center justify-center text-center">
            <p className="text-sm text-neutral-500">
              {emptyMessage || __('No data available for the selected period', 'wp-statistics')}
            </p>
          </div>
        ) : (
          <div className="flex flex-col gap-2">
            {items.map((item) => (
              <TabbedListItemRow key={item.id} item={item} />
            ))}
          </div>
        )}
      </PanelContent>

      {currentTab?.link && items.length > 0 && (
        <PanelFooter>
          <PanelAction onClick={currentTab.link.action} href={currentTab.link.href}>
            {currentTab.link.title}
          </PanelAction>
        </PanelFooter>
      )}
    </Panel>
  )
}

interface TabbedListItemRowProps {
  item: TabbedListItem
}

function TabbedListItemRow({ item }: TabbedListItemRowProps) {
  const content = (
    <div className="flex items-center gap-3 p-2 rounded-md hover:bg-muted/50 transition-colors">
      {/* Thumbnail */}
      <div className="flex-shrink-0 w-12 h-12 rounded-md overflow-hidden bg-muted flex items-center justify-center">
        {item.thumbnail ? (
          // eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions
          <img
            src={item.thumbnail}
            alt={item.title}
            className="w-full h-full object-cover"
            onError={(e) => {
              // Hide broken image and show placeholder
              e.currentTarget.style.display = 'none'
              e.currentTarget.nextElementSibling?.classList.remove('hidden')
            }}
          />
        ) : null}
        <FileText className={cn('w-5 h-5 text-muted-foreground', item.thumbnail ? 'hidden' : '')} />
      </div>

      {/* Content */}
      <div className="flex-1 min-w-0">
        <p className="text-sm font-medium text-neutral-800 truncate">{item.title}</p>
        {item.subtitle && <p className="text-xs text-muted-foreground truncate">{item.subtitle}</p>}
      </div>
    </div>
  )

  if (item.href) {
    const { path, search } = parseHref(item.href)
    return (
      <Link to={path} search={search} className="block no-underline">
        {content}
      </Link>
    )
  }

  return content
}
