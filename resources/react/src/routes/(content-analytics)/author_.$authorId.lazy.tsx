import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { LockIcon } from 'lucide-react'
import { useMemo } from 'react'

import type { PageFilterConfig } from '@/components/custom/options-drawer'
import { PostTypeSelect } from '@/components/custom/post-type-select'
import { PhpOverviewRoute } from '@/components/php-overview-route'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { usePostTypeFilter } from '@/hooks/use-post-type-filter'
import { WordPress } from '@/lib/wordpress'

export const Route = createLazyFileRoute('/(content-analytics)/author_/$authorId')({
  component: RouteComponent,
})

function LockedState() {
  return (
    <Panel className="p-8 text-center">
      <div className="max-w-md mx-auto space-y-4">
        <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
          <LockIcon className="w-8 h-8 text-primary" strokeWidth={1.5} />
        </div>
        <h2 className="text-lg font-semibold text-neutral-800">
          {__('Single Author Report', 'wp-statistics')}
        </h2>
        <p className="text-sm text-muted-foreground">
          {__(
            'Get detailed analytics for individual authors including published content, traffic trends, top performing posts, and engagement metrics.',
            'wp-statistics'
          )}
        </p>
        <p className="text-sm text-muted-foreground">
          {__('This feature requires the Premium addon.', 'wp-statistics')}
        </p>
        <a
          href="https://wp-statistics.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=single-author"
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 transition-colors"
        >
          {__('Upgrade to Premium', 'wp-statistics')}
        </a>
      </div>
    </Panel>
  )
}

function RouteComponent() {
  const { authorId } = Route.useParams()
  const reports = WordPress.getInstance().getData<Record<string, { type?: string }>>('reports')

  const {
    value: postType,
    onChange: handlePostTypeChange,
    pageFilterConfig: postTypeFilterConfig,
  } = usePostTypeFilter({ defaultValue: 'post' })

  const apiFilters = useMemo(
    () => (postType && postType !== 'all' ? { post_type: { is: postType } } : undefined),
    [postType]
  )

  const pageFilters = useMemo<PageFilterConfig[]>(
    () => [postTypeFilterConfig],
    [postTypeFilterConfig]
  )

  // Premium: PHP config registered by SingleAuthor module
  if (reports?.['single-author']?.type === 'detail') {
    return (
      <PhpOverviewRoute
        slug="single-author"
        fallbackTitle={__('Author Report', 'wp-statistics')}
        routeParams={{ authorId }}
        apiFilters={apiFilters}
        pageFilters={pageFilters}
        headerActions={
          <PostTypeSelect
            value={postType}
            onValueChange={handlePostTypeChange}
            showAll={false}
          />
        }
      />
    )
  }

  // Free: show locked state
  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3">
        <h1 className="text-2xl font-semibold text-neutral-800">
          {__('Single Author Report', 'wp-statistics')}
        </h1>
      </div>
      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="single-author" />
        <LockedState />
      </div>
    </div>
  )
}
