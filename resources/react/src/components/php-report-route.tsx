import { __ } from '@wordpress/i18n'
import { useCallback, useMemo } from 'react'

import type { PageFilterConfig } from '@/components/custom/options-drawer'
import { SearchTypeSelect } from '@/components/custom/search-type-select'
import { SocialTypeSelect } from '@/components/custom/social-type-select'
import { TaxonomySelect } from '@/components/custom/taxonomy-select'
import type { ReportConfig } from '@/components/report-page-renderer'
import { ReportPageRenderer } from '@/components/report-page-renderer'
import { NoticeContainer } from '@/components/ui/notice-container'
import { useContentRegistry } from '@/contexts/content-registry-context'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useSearchTypeFilter } from '@/hooks/use-search-type-filter'
import { useSocialTypeFilter } from '@/hooks/use-social-type-filter'
import { useTaxonomyFilter } from '@/hooks/use-taxonomy-filter'

/**
 * Route component for PHP-configured reports.
 * Looks up the report by slug in the content registry and renders it
 * via ReportPageRenderer, or shows a fallback if not registered.
 *
 * When the config includes a headerFilter, delegates to a filter-aware
 * wrapper that injects the appropriate select component and API filters.
 */
export function PhpReportRoute({ slug, fallbackTitle }: { slug: string; fallbackTitle: string }) {
  const { getReport } = useContentRegistry()
  const report = getReport(slug)

  if (!report?.config) {
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

  const { headerFilter } = report.config

  if (headerFilter?.type === 'search-type') {
    return <WithSearchTypeFilter config={report.config} />
  }
  if (headerFilter?.type === 'social-type') {
    return <WithSocialTypeFilter config={report.config} />
  }
  if (headerFilter?.type === 'taxonomy') {
    return <WithTaxonomyFilter config={report.config} headerFilter={headerFilter} />
  }

  return <ReportPageRenderer config={report.config} />
}

function WithSearchTypeFilter({ config }: { config: ReportConfig }) {
  const { setPage } = useGlobalFilters()
  const { value, onChange, options, getApiFilter, pageFilterConfig } = useSearchTypeFilter()

  const handleChange = useCallback(
    (v: string) => {
      onChange(v)
      setPage(1)
    },
    [onChange, setPage]
  )

  return (
    <ReportPageRenderer
      config={{
        ...config,
        pageFilters: [{ ...pageFilterConfig, onChange: handleChange }],
        headerActions: () => (
          <SearchTypeSelect value={value} onValueChange={handleChange} options={options} />
        ),
      }}
      apiFilters={getApiFilter()}
    />
  )
}

function WithSocialTypeFilter({ config }: { config: ReportConfig }) {
  const { setPage } = useGlobalFilters()
  const { value, onChange, options, getApiFilter, pageFilterConfig } = useSocialTypeFilter()

  const handleChange = useCallback(
    (v: string) => {
      onChange(v)
      setPage(1)
    },
    [onChange, setPage]
  )

  return (
    <ReportPageRenderer
      config={{
        ...config,
        pageFilters: [{ ...pageFilterConfig, onChange: handleChange }],
        headerActions: () => (
          <SocialTypeSelect value={value} onValueChange={handleChange} options={options} />
        ),
      }}
      apiFilters={getApiFilter()}
    />
  )
}

function WithTaxonomyFilter({
  config,
  headerFilter,
}: {
  config: ReportConfig
  headerFilter: PhpHeaderFilter
}) {
  const { setPage } = useGlobalFilters()
  const {
    value,
    onChange: baseTaxonomyChange,
    pageFilterConfig: taxonomyFilterConfig,
  } = useTaxonomyFilter({ premiumOnly: headerFilter.premiumOnly })

  const apiFilterField = headerFilter.apiFilterField || 'taxonomy_type'

  const handleChange = useCallback(
    (v: string) => {
      baseTaxonomyChange(v)
      setPage(1)
    },
    [baseTaxonomyChange, setPage]
  )

  const pageFilters = useMemo<PageFilterConfig[]>(
    () => [{ ...taxonomyFilterConfig, onChange: handleChange }],
    [taxonomyFilterConfig, handleChange]
  )

  return (
    <ReportPageRenderer
      config={{
        ...config,
        pageFilters,
        headerActions: () => <TaxonomySelect value={value} onValueChange={handleChange} />,
      }}
      apiFilters={{ [apiFilterField]: { is: value } }}
    />
  )
}
