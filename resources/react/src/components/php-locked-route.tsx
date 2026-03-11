/**
 * Route component for premium-locked pages.
 * Checks for PHP report config → premium page content → locked state fallback.
 */

import { __ } from '@wordpress/i18n'
import { LockIcon, type LucideIcon } from 'lucide-react'
import { type ReactNode, useEffect, useState } from 'react'

import { OverviewPageRenderer } from '@/components/overview-page-renderer'
import { ReportPageRenderer } from '@/components/report-page-renderer'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { useContentRegistry } from '@/contexts/content-registry-context'
import { WordPress } from '@/lib/wordpress'

interface PhpLockedRouteProps {
  slug: string
  title: string
  description: string
  icon?: LucideIcon
  buttonText?: string
  utmCampaign?: string
  premiumText?: string
  routeParams?: Record<string, string>
}

/**
 * Resolve premium page content from all available sources.
 * Returns the rendered content or null if nothing is registered.
 */
function usePremiumContent(
  slug: string,
  routeParams?: Record<string, string>,
): ReactNode | null {
  const { getPageContent, getReport } = useContentRegistry()
  const [, forceUpdate] = useState(0)

  useEffect(() => {
    const handler = (event: CustomEvent) => {
      if (event.detail?.pageId === slug) forceUpdate((n) => n + 1)
    }
    window.addEventListener('wps:content-registered', handler as EventListener)
    return () => {
      window.removeEventListener('wps:content-registered', handler as EventListener)
    }
  }, [slug])

  // 1. JS-registered report (table report via registerReport)
  const registeredReport = getReport(slug)
  if (registeredReport?.config) {
    return <ReportPageRenderer config={registeredReport.config} />
  }

  // 2. PHP-configured overview/detail page
  const reports = WordPress.getInstance().getData<Record<string, PhpReportDefinition | PhpOverviewDefinition | PhpDetailDefinition>>('reports')
  const phpConfig = reports?.[slug]
  if (phpConfig?.type === 'overview' || phpConfig?.type === 'detail') {
    return <OverviewPageRenderer config={phpConfig} routeParams={routeParams} />
  }

  // 3. Legacy page content (custom render function)
  const pageContent = getPageContent(slug)
  if (pageContent?.render) {
    const premiumContent = pageContent.render(routeParams)
    if (premiumContent) {
      return <div className="min-w-0">{premiumContent}</div>
    }
  }

  return null
}

export function PhpLockedRoute({
  slug,
  title,
  description,
  icon: Icon = LockIcon,
  buttonText = __('Upgrade to Premium', 'wp-statistics'),
  utmCampaign,
  premiumText = __('This feature requires the Premium addon.', 'wp-statistics'),
  routeParams,
}: PhpLockedRouteProps) {
  const premiumContent = usePremiumContent(slug, routeParams)
  if (premiumContent) return premiumContent

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3">
        <h1 className="text-2xl font-semibold text-neutral-800">{title}</h1>
      </div>
      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute={slug} />
        <Panel className="p-8 text-center">
          <div className="max-w-md mx-auto space-y-4">
            <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
              <Icon className="w-8 h-8 text-primary" strokeWidth={1.5} />
            </div>
            <h2 className="text-lg font-semibold text-neutral-800">{title}</h2>
            <p className="text-sm text-muted-foreground">{description}</p>
            <p className="text-sm text-muted-foreground">{premiumText}</p>
            <a
              href={`https://wp-statistics.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=${utmCampaign || slug}`}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 transition-colors"
            >
              {buttonText}
            </a>
          </div>
        </Panel>
      </div>
    </div>
  )
}
