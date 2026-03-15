/**
 * Single Visitor Report Page
 *
 * Route: /visitor/{type}/{id}
 * - type: 'user' | 'ip' | 'hash'
 * - id: WordPress user ID, IP address, or visitor hash
 */

import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpOverviewRoute } from '@/components/php-overview-route'
import { Panel } from '@/components/ui/panel'
import { VisitorProfileCard } from '@/components/visitor-profile-card'
import { registerWidget } from '@/registration'

export const Route = createLazyFileRoute('/(visitor-insights)/visitor_/$type/$id')({
  component: RouteComponent,
})

const VALID_TYPES = ['user', 'ip', 'hash'] as const
type VisitorType = (typeof VALID_TYPES)[number]

function isValidVisitorType(value: string | undefined): value is VisitorType {
  return !!value && VALID_TYPES.includes(value as VisitorType)
}

// Register visitor profile card as a widget (runs once when this lazy module loads)
registerWidget('single-visitor', {
  id: 'visitor-profile',
  label: __('Visitor Profile', 'wp-statistics'),
  defaultVisible: true,
  queryId: 'visitor_info',
  render: ({ data, routeParams }) => {
    const type: VisitorType = isValidVisitorType(routeParams?.type) ? routeParams.type : 'hash'
    return <VisitorProfileCard type={type} visitorInfo={data?.[0] as Record<string, unknown> | undefined} />
  },
})

function RouteComponent() {
  const { type, id } = Route.useParams()

  if (!isValidVisitorType(type)) {
    return (
      <div className="min-w-0">
        <div className="flex items-center justify-between px-4 py-3">
          <h1 className="text-2xl font-semibold text-neutral-800">
            {__('Invalid Visitor Type', 'wp-statistics')}
          </h1>
        </div>
        <div className="p-3">
          <Panel className="p-8 text-center">
            <p className="text-sm text-muted-foreground">
              {__('The visitor type must be "user", "ip", or "hash".', 'wp-statistics')}
            </p>
          </Panel>
        </div>
      </div>
    )
  }

  return (
    <PhpOverviewRoute
      slug="single-visitor"
      fallbackTitle={__('Visitor Report', 'wp-statistics')}
      routeParams={{ type, id }}
    />
  )
}
