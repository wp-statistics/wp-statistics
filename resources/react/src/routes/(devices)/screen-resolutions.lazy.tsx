import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { LockIcon } from 'lucide-react'
import { useEffect, useState } from 'react'

import { ReportPageRenderer } from '@/components/report-page-renderer'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { useContentRegistry } from '@/contexts/content-registry-context'

export const Route = createLazyFileRoute('/(devices)/screen-resolutions')({
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
          {__('Screen Resolutions Report', 'wp-statistics')}
        </h2>
        <p className="text-sm text-muted-foreground">
          {__(
            'See what screen resolutions your visitors use. Understand your audience\'s devices to optimize your site\'s layout and responsiveness.',
            'wp-statistics'
          )}
        </p>
        <p className="text-sm text-muted-foreground">
          {__('This feature requires the Premium addon.', 'wp-statistics')}
        </p>
        <a
          href="https://wp-statistics.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=screen-resolutions"
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
  const { getPageContent, getReport } = useContentRegistry()
  const [, forceUpdate] = useState(0)

  // Listen for content/report registration events
  useEffect(() => {
    const handleContentRegistered = (event: CustomEvent) => {
      if (event.detail?.pageId === 'screen-resolutions') {
        forceUpdate((n) => n + 1)
      }
    }
    const handleReportRegistered = (event: CustomEvent) => {
      if (event.detail?.pageId === 'screen-resolutions') {
        forceUpdate((n) => n + 1)
      }
    }

    window.addEventListener('wps:content-registered', handleContentRegistered as EventListener)
    window.addEventListener('wps:report-registered', handleReportRegistered as EventListener)
    return () => {
      window.removeEventListener('wps:content-registered', handleContentRegistered as EventListener)
      window.removeEventListener('wps:report-registered', handleReportRegistered as EventListener)
    }
  }, [])

  // Level 1 & 2: Check for registered report config
  const registeredReport = getReport('screen-resolutions')
  if (registeredReport?.config) {
    return <ReportPageRenderer config={registeredReport.config} />
  }

  // Level 3: Check for full custom page content
  const pageContent = getPageContent('screen-resolutions')
  if (pageContent?.render) {
    const premiumContent = pageContent.render()
    if (premiumContent) {
      return <div className="min-w-0">{premiumContent}</div>
    }
  }

  // Otherwise show locked state
  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Screen Resolutions', 'wp-statistics')}</h1>
      </div>
      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="screen-resolutions" />
        <LockedState />
      </div>
    </div>
  )
}
