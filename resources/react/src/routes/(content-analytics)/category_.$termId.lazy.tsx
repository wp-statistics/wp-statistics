import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { PhpOverviewRoute } from '@/components/php-overview-route'

export const Route = createLazyFileRoute('/(content-analytics)/category_/$termId')({
  component: RouteComponent,
})

function RouteComponent() {
  const { termId } = Route.useParams()

  // taxonomy_type filter ensures queries are scoped to the right taxonomy
  const apiFilters = useMemo(
    () => ({ taxonomy_type: { is: 'category' } }),
    []
  )

  return (
    <PhpOverviewRoute
      slug="single-category"
      fallbackTitle={__('Category', 'wp-statistics')}
      routeParams={{ termId }}
      apiFilters={apiFilters}
    />
  )
}
