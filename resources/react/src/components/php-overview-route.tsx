/**
 * Route component for PHP-configured overview pages.
 * Reads the overview config by slug from window.wps_react.reports
 * and renders via OverviewPageRenderer.
 */

import { __ } from '@wordpress/i18n'

import { OverviewPageRenderer } from '@/components/overview-page-renderer'
import { NoticeContainer } from '@/components/ui/notice-container'
import { WordPress } from '@/lib/wordpress'

export function PhpOverviewRoute({ slug, fallbackTitle }: { slug: string; fallbackTitle: string }) {
  const reports = WordPress.getInstance().getData<Record<string, PhpReportDefinition | PhpOverviewDefinition>>(
    'reports'
  )
  const config = reports?.[slug]

  if (config?.type === 'overview') {
    return <OverviewPageRenderer config={config} />
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
