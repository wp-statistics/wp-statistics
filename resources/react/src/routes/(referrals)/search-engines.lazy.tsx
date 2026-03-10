import { createLazyFileRoute } from '@tanstack/react-router'

import { SearchTypeSelect } from '@/components/custom/search-type-select'
import { ReportPageRenderer } from '@/components/report-page-renderer'
import { useContentRegistry } from '@/contexts/content-registry-context'
import { useSearchTypeFilter } from '@/hooks/use-search-type-filter'

export const Route = createLazyFileRoute('/(referrals)/search-engines')({
  component: RouteComponent,
})

function RouteComponent() {
  const { getReport } = useContentRegistry()
  const report = getReport('search-engines')

  const {
    value: searchType,
    onChange: onSearchTypeChange,
    options: searchTypeOptions,
    getApiFilter,
    pageFilterConfig,
  } = useSearchTypeFilter()

  if (!report?.config) return null

  return (
    <ReportPageRenderer
      config={{
        ...report.config,
        pageFilters: [pageFilterConfig],
        headerActions: () => (
          <div className="hidden lg:flex">
            <SearchTypeSelect
              value={searchType}
              onValueChange={onSearchTypeChange}
              options={searchTypeOptions}
            />
          </div>
        ),
      }}
      apiFilters={getApiFilter()}
    />
  )
}
