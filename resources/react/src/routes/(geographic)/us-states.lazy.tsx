import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'

export const Route = createLazyFileRoute('/(geographic)/us-states')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('US States', 'wp-statistics')}</h1>
      </div>

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="us-states" />
        <Panel>
          <div className="p-6 text-center text-muted-foreground">
            {__('US States report coming soon.', 'wp-statistics')}
          </div>
        </Panel>
      </div>
    </div>
  )
}
