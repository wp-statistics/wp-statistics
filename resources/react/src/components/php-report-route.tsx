import { __ } from '@wordpress/i18n'

import { ReportPageRenderer } from '@/components/report-page-renderer'
import { NoticeContainer } from '@/components/ui/notice-container'
import { useContentRegistry } from '@/contexts/content-registry-context'

/**
 * Route component for PHP-configured reports.
 * Looks up the report by slug in the content registry and renders it
 * via ReportPageRenderer, or shows a fallback if not registered.
 */
export function PhpReportRoute({ slug, fallbackTitle }: { slug: string; fallbackTitle: string }) {
  const { getReport } = useContentRegistry()
  const report = getReport(slug)

  if (report?.config) {
    return <ReportPageRenderer config={report.config} />
  }

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3">
        <h1 className="text-2xl font-semibold text-neutral-800">{fallbackTitle}</h1>
      </div>
      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute={slug} />
        <p className="text-sm text-muted-foreground">
          {__('This report is not available.', 'wp-statistics')}
        </p>
      </div>
    </div>
  )
}
