import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import type { PageFilterConfig } from '@/components/custom/options-drawer'
import { TaxonomySelect } from '@/components/custom/taxonomy-select'
import { PhpOverviewRoute } from '@/components/php-overview-route'
import { useTaxonomyFilter } from '@/hooks/use-taxonomy-filter'

export const Route = createLazyFileRoute('/(content-analytics)/categories')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    value: selectedTaxonomy,
    onChange: handleTaxonomyChange,
    selectedLabel: selectedTaxonomyLabel,
    pageFilterConfig: taxonomyFilterConfig,
  } = useTaxonomyFilter({ premiumOnly: true })

  const pageFilters = useMemo<PageFilterConfig[]>(
    () => [taxonomyFilterConfig],
    [taxonomyFilterConfig]
  )

  return (
    <PhpOverviewRoute
      slug="categories-overview"
      fallbackTitle={__('Categories', 'wp-statistics')}
      title={selectedTaxonomyLabel}
      apiFilters={{ taxonomy_type: { is: selectedTaxonomy } }}
      headerActions={<TaxonomySelect value={selectedTaxonomy} onValueChange={handleTaxonomyChange} />}
      pageFilters={pageFilters}
    />
  )
}
