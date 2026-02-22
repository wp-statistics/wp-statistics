import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { Megaphone } from 'lucide-react'
import { useEffect, useState } from 'react'

import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { useContentRegistry } from '@/contexts/content-registry-context'

export const Route = createLazyFileRoute('/(referrals)/campaigns')({
  component: RouteComponent,
})

function RouteComponent() {
  const { getPageContent } = useContentRegistry()
  const [, forceUpdate] = useState(0)

  useEffect(() => {
    const handleContentRegistered = (event: CustomEvent) => {
      if (event.detail?.pageId === 'campaigns') {
        forceUpdate((n) => n + 1)
      }
    }

    window.addEventListener('wps:content-registered', handleContentRegistered as EventListener)
    return () => {
      window.removeEventListener('wps:content-registered', handleContentRegistered as EventListener)
    }
  }, [])

  const pageContent = getPageContent('campaigns')
  if (pageContent?.render) {
    const premiumContent = pageContent.render()
    if (premiumContent) {
      return <div className="min-w-0">{premiumContent}</div>
    }
  }

  return (
    <div className="min-w-0">
      {/* Header row */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Campaigns', 'wp-statistics')}</h1>
      </div>

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="campaigns" />
        <Panel className="p-8 text-center">
          <div className="max-w-md mx-auto space-y-4">
            <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
              <Megaphone className="w-8 h-8 text-primary" strokeWidth={1.5} />
            </div>
            <h2 className="text-lg font-semibold text-neutral-800">
              {__('Marketing Campaigns', 'wp-statistics')}
            </h2>
            <p className="text-sm text-muted-foreground">
              {__(
                'Track your marketing campaigns with detailed UTM reports. Monitor campaign performance, measure ROI, and optimize your marketing strategy.',
                'wp-statistics'
              )}
            </p>
            <p className="text-sm text-muted-foreground">
              {__('This feature requires WP Statistics Premium.', 'wp-statistics')}
            </p>
            <a
              href="https://wp-statistics.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=campaigns"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 transition-colors"
            >
              {__('Learn More', 'wp-statistics')}
            </a>
          </div>
        </Panel>
      </div>
    </div>
  )
}
