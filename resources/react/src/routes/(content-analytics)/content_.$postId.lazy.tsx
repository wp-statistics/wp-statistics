import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { PhpOverviewRoute } from '@/components/php-overview-route'
import { WordPress } from '@/lib/wordpress'

export const Route = createLazyFileRoute('/(content-analytics)/content_/$postId')({
  component: RouteComponent,
})

function RouteComponent() {
  const { postId } = Route.useParams()

  // Scope queries to content resources (post, page, etc.) by post type
  const apiFilters = useMemo(() => {
    const queryablePostTypes = WordPress.getInstance().getQueryablePostTypes()
    return { post_type: { in: queryablePostTypes } }
  }, [])

  return (
    <PhpOverviewRoute
      slug="single-content"
      fallbackTitle={__('Content', 'wp-statistics')}
      routeParams={{ postId }}
      apiFilters={apiFilters}
    />
  )
}
