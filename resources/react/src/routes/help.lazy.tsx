import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { CircleHelp } from 'lucide-react'

import { Panel } from '@/components/ui/panel'

export const Route = createLazyFileRoute('/help')({
  component: HelpPage,
})

// TODO: Implement full help center page
function HelpPage() {
  return (
    <div className="p-6 max-w-4xl mx-auto">
      <Panel className="p-12 text-center">
        <div className="space-y-4">
          <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
            <CircleHelp className="w-8 h-8 text-primary" strokeWidth={1.5} />
          </div>
          <h1 className="text-2xl font-semibold text-neutral-800">
            {__('Help Center', 'wp-statistics')}
          </h1>
          <p className="text-muted-foreground max-w-md mx-auto">
            {__('Find documentation, troubleshooting guides, and support resources for WP Statistics.', 'wp-statistics')}
          </p>
        </div>
      </Panel>
    </div>
  )
}
