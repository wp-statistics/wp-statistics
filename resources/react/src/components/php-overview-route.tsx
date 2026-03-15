/**
 * Route component for PHP-configured overview and detail pages.
 * Reads the config by slug from window.wps_react.reports
 * and renders via OverviewPageRenderer.
 */

import { __ } from '@wordpress/i18n'

import type { PageFilterConfig } from '@/components/custom/options-drawer'
import { OverviewPageRenderer } from '@/components/overview-page-renderer'
import { NoticeContainer } from '@/components/ui/notice-container'
import { WordPress } from '@/lib/wordpress'

export function PhpOverviewRoute({
  slug,
  fallbackTitle,
  title: titleOverride,
  routeParams,
  apiFilters,
  headerActions,
  pageFilters,
}: {
  slug: string
  fallbackTitle: string
  /** Override the PHP config title (e.g., dynamic taxonomy label) */
  title?: string
  /** Route params for detail pages (e.g., { countryCode: 'US' }) */
  routeParams?: Record<string, string>
  /** Additional API filters (e.g., from PostTypeSelect) */
  apiFilters?: Record<string, Record<string, string | string[]>>
  /** Extra elements rendered in the detail page header */
  headerActions?: React.ReactNode
  /** Page-specific filter configs for the Options drawer */
  pageFilters?: PageFilterConfig[]
}) {
  const reports = WordPress.getInstance().getData<Record<string, PhpReportDefinition | PhpOverviewDefinition | PhpDetailDefinition>>(
    'reports'
  )
  const config = reports?.[slug]

  if (config?.type === 'overview' || config?.type === 'detail') {
    return <OverviewPageRenderer config={config} title={titleOverride} routeParams={routeParams} apiFilters={apiFilters} headerActions={headerActions} pageFilters={pageFilters} />
  }

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3">
        <h1 className="text-2xl font-semibold text-neutral-800">{fallbackTitle}</h1>
      </div>
      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute={slug} />
        <p className="text-sm text-muted-foreground">
          {__('This page is not available.', 'wp-statistics')}
        </p>
      </div>
    </div>
  )
}
