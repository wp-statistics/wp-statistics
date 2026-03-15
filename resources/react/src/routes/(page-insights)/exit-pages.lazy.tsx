import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(page-insights)/exit-pages')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="exit-pages"
      title={__('Exit Pages', 'wp-statistics')}
      description={__('See which pages visitors leave your site from. Understand your exit points and optimize them to keep visitors engaged longer.', 'wp-statistics')}
    />
  )
}
