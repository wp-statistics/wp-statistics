import { useNavigate, useSearch } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { type ReactNode, useCallback, useMemo } from 'react'

import type { PageFilterConfig } from '@/components/custom/options-drawer'
import { SearchTypeSelect } from '@/components/custom/search-type-select'
import { SocialTypeSelect } from '@/components/custom/social-type-select'
import { TaxonomySelect } from '@/components/custom/taxonomy-select'
import type { ReportConfig } from '@/components/report-page-renderer'
import { ReportPageRenderer } from '@/components/report-page-renderer'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
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
export function PhpReportRoute({ slug, fallbackTitle, headerActions }: { slug: string; fallbackTitle: string; headerActions?: () => ReactNode }) {
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
  if (headerFilter?.type === 'group-by-select') {
    return <WithGroupBySelectFilter config={report.config} headerFilter={headerFilter} />
  }

  return <ReportPageRenderer config={{ ...report.config, ...(headerActions && { headerActions }) }} />
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

function WithGroupBySelectFilter({
  config,
  headerFilter,
}: {
  config: ReportConfig
  headerFilter: PhpHeaderFilter
}) {
  const { setPage } = useGlobalFilters()
  const navigate = useNavigate()
  const search = useSearch({ strict: false }) as Record<string, unknown>

  const filterOptions = useMemo(() => headerFilter.options || [], [headerFilter.options])
  const urlParam = headerFilter.urlParam || 'group_by_type'
  const defaultValue = headerFilter.defaultValue || filterOptions[0]?.value || ''
  const value = (search[urlParam] as string) || defaultValue

  const groupBy = useMemo(() => {
    const selectedOption = filterOptions.find((o) => o.value === value)
    return selectedOption?.groupBy || [value]
  }, [filterOptions, value])

  const handleChange = useCallback(
    (newValue: string) => {
      navigate({
        search: (prev) => {
          const cleaned = Object.fromEntries(
            Object.entries(prev as Record<string, unknown>).filter(([key]) => key !== urlParam)
          )
          if (newValue !== defaultValue) {
            return { ...cleaned, [urlParam]: newValue }
          }
          return cleaned
        },
        replace: true,
      })
      setPage(1)
    },
    [navigate, urlParam, defaultValue, setPage]
  )

  const pageFilterConfig = useMemo<PageFilterConfig>(
    () => ({
      id: urlParam,
      label: headerFilter.filterLabel || __('Type', 'wp-statistics'),
      value,
      options: filterOptions.map((o) => ({ value: o.value, label: o.label })),
      onChange: handleChange,
    }),
    [urlParam, headerFilter.filterLabel, value, filterOptions, handleChange]
  )

  const queryOverrides = useMemo(() => ({ group_by: groupBy }), [groupBy])

  return (
    <ReportPageRenderer
      config={{
        ...config,
        pageFilters: [pageFilterConfig],
        headerActions: () => (
          <Select value={value} onValueChange={handleChange}>
            <SelectTrigger className="h-8 px-3 text-xs font-medium bg-background border border-neutral-200 rounded-md hover:bg-neutral-50">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {filterOptions.map((option) => (
                <SelectItem key={option.value} value={option.value}>
                  {option.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        ),
      }}
      queryOverrides={queryOverrides}
    />
  )
}
