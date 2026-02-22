import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { Construction } from 'lucide-react'

export const Route = createLazyFileRoute('/help')({
  component: HelpPage,
})

// TODO: Implement full help center page
function HelpPage() {
  return (
    <div className="flex flex-col items-center justify-center py-24 text-center">
      <Construction className="h-12 w-12 text-muted-foreground/50 mb-4" />
      <h3 className="text-lg font-medium mb-1">{__('Coming Soon', 'wp-statistics')}</h3>
      <p className="text-sm text-muted-foreground max-w-md">
        {__('Find documentation, troubleshooting guides, and support resources for WP Statistics.', 'wp-statistics')}
      </p>
    </div>
  )
}
