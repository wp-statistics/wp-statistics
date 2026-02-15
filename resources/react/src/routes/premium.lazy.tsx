import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { Crown } from 'lucide-react'

import { Panel } from '@/components/ui/panel'

export const Route = createLazyFileRoute('/premium')({
  component: PremiumPage,
})

// TODO: Implement full premium features management page
function PremiumPage() {
  return (
    <div className="p-6 max-w-4xl mx-auto">
      <Panel className="p-12 text-center">
        <div className="space-y-4">
          <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
            <Crown className="w-8 h-8 text-primary" strokeWidth={1.5} />
          </div>
          <h1 className="text-2xl font-semibold text-neutral-800">
            {__('Premium', 'wp-statistics')}
          </h1>
          <p className="text-muted-foreground max-w-md mx-auto">
            {__('Unlock advanced analytics features with WP Statistics Premium.', 'wp-statistics')}
          </p>
        </div>
      </Panel>
    </div>
  )
}
