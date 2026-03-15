import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { PhpLockedRoute } from '@/components/php-locked-route'

export const Route = createLazyFileRoute('/(page-insights)/entry-pages')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <PhpLockedRoute
      slug="entry-pages"
      title={__('Entry Pages', 'wp-statistics')}
      description={__('See which pages visitors land on when they first enter your site. Understand your top entry points and optimize them for better engagement.', 'wp-statistics')}
    />
  )
}
