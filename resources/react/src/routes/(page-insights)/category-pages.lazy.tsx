import { createLazyFileRoute } from '@tanstack/react-router'
import { useCallback, useMemo } from 'react'

import type { PageFilterConfig } from '@/components/custom/options-drawer'
import { TaxonomySelect } from '@/components/custom/taxonomy-select'
import { ReportPageRenderer } from '@/components/report-page-renderer'
import { useContentRegistry } from '@/contexts/content-registry-context'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useTaxonomyFilter } from '@/hooks/use-taxonomy-filter'

export const Route = createLazyFileRoute('/(page-insights)/category-pages')({
  component: RouteComponent,
})

function RouteComponent() {
  const { getReport } = useContentRegistry()
  const report = getReport('category-pages')
  const { setPage } = useGlobalFilters()

  const {
    value: taxonomyType,
    onChange: baseTaxonomyChange,
    pageFilterConfig: taxonomyFilterConfig,
  } = useTaxonomyFilter()

  const handleTaxonomyChange = useCallback(
    (value: string) => {
      baseTaxonomyChange(value)
      setPage(1)
    },
    [baseTaxonomyChange, setPage]
  )

  const pageFilters = useMemo<PageFilterConfig[]>(
    () => [{ ...taxonomyFilterConfig, onChange: handleTaxonomyChange }],
    [taxonomyFilterConfig, handleTaxonomyChange]
  )

  if (!report?.config) return null

  return (
    <ReportPageRenderer
      config={{
        ...report.config,
        pageFilters,
        headerActions: () => (
          <div className="hidden lg:flex">
            <TaxonomySelect value={taxonomyType} onValueChange={handleTaxonomyChange} />
          </div>
        ),
      }}
      apiFilters={{ post_type: { is: taxonomyType } }}
    />
  )
}
