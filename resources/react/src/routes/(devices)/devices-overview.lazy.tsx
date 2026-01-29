import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

export const Route = createLazyFileRoute('/(devices)/devices-overview')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Devices Overview', 'wp-statistics')}</h1>
      </div>
      <div className="p-3">
        <p className="text-sm text-neutral-600">{__('Devices Overview content coming soon.', 'wp-statistics')}</p>
      </div>
    </div>
  )
}
