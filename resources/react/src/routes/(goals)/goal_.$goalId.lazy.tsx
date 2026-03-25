import { useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { Loader2, LockIcon } from 'lucide-react'

import { OverviewPageRenderer } from '@/components/overview-page-renderer'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export const Route = createLazyFileRoute('/(goals)/goal_/$goalId')({
  component: RouteComponent,
})

function RouteComponent() {
  const { goalId } = Route.useParams()
  const reports = WordPress.getInstance().getData<Record<string, { type?: string }>>('reports')
  const isPremium = !!reports?.['goals']

  if (!isPremium) {
    return (
      <div className="min-w-0">
        <div className="flex items-center justify-between px-4 py-3">
          <h1 className="text-2xl font-semibold text-neutral-800">
            {__('Goal Report', 'wp-statistics')}
          </h1>
        </div>
        <div className="p-3">
          <NoticeContainer className="mb-2" currentRoute="single-goal" />
          <Panel className="p-8 text-center">
            <div className="max-w-md mx-auto space-y-4">
              <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
                <LockIcon className="w-8 h-8 text-primary" strokeWidth={1.5} />
              </div>
              <h2 className="text-lg font-semibold text-neutral-800">
                {__('Goal Report', 'wp-statistics')}
              </h2>
              <p className="text-sm text-muted-foreground">
                {__('This feature requires the Premium addon.', 'wp-statistics')}
              </p>
            </div>
          </Panel>
        </div>
      </div>
    )
  }

  return <GoalDetailPage goalId={goalId} />
}

function GoalDetailPage({ goalId }: { goalId: string }) {
  const { apiDateParams, isInitialized } = useGlobalFilters()

  const { data, isLoading } = useQuery({
    queryKey: ['goal-detail-config', goalId, apiDateParams?.date_from, apiDateParams?.date_to],
    queryFn: () =>
      clientRequest.get<{ data: Record<string, unknown> }>('', {
        params: {
          action: 'wp_statistics_goal_detail_config',
          nonce: WordPress.getInstance().getNonce(),
          goal_id: goalId,
        },
      }),
    enabled: isInitialized,
  })

  const config = data?.data?.data as Record<string, unknown> | undefined

  if (isLoading || !config) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <Loader2 className="h-8 w-8 animate-spin text-neutral-400" />
      </div>
    )
  }

  return <OverviewPageRenderer config={config} routeParams={{ goalId }} />
}
