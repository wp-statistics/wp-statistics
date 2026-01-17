import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { LockIcon } from 'lucide-react'
import { useEffect, useState } from 'react'

import { useContentRegistry } from '@/contexts/content-registry-context'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'

export const Route = createLazyFileRoute('/(page-insights)/entry-pages')({
  component: RouteComponent,
})

function LockedState() {
  return (
    <Panel className="p-8 text-center">
      <div className="max-w-md mx-auto space-y-4">
        <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
          <LockIcon className="w-8 h-8 text-primary" strokeWidth={1.5} />
        </div>
        <h2 className="text-lg font-semibold text-neutral-800">
          {__('Entry Pages Report', 'wp-statistics')}
        </h2>
        <p className="text-sm text-muted-foreground">
          {__(
            'See which pages visitors land on when they first enter your site. Understand your top entry points and optimize them for better engagement.',
            'wp-statistics'
          )}
        </p>
        <p className="text-sm text-muted-foreground">
          {__('This feature requires the Premium addon.', 'wp-statistics')}
        </p>
        <a
          href="https://wp-statistics.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=entry-pages"
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 transition-colors"
        >
          {__('Upgrade to Premium', 'wp-statistics')}
        </a>
      </div>
    </Panel>
  )
}

function RouteComponent() {
  const { getPageContent } = useContentRegistry()
  const [, forceUpdate] = useState(0)

  // Listen for content registration events
  useEffect(() => {
    const handleContentRegistered = (event: CustomEvent) => {
      if (event.detail?.pageId === 'entry-pages') {
        forceUpdate((n) => n + 1)
      }
    }

    window.addEventListener('wps:content-registered', handleContentRegistered as EventListener)
    return () => {
      window.removeEventListener('wps:content-registered', handleContentRegistered as EventListener)
    }
  }, [])

  const pageContent = getPageContent('entry-pages')

  // If premium has registered content with a render function, use it
  if (pageContent?.render) {
    const premiumContent = pageContent.render()
    if (premiumContent) {
      return <div className="min-w-0">{premiumContent}</div>
    }
  }

  // Otherwise show locked state
  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Entry Pages', 'wp-statistics')}</h1>
      </div>
      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="entry-pages" />
        <LockedState />
      </div>
    </div>
  )
}
