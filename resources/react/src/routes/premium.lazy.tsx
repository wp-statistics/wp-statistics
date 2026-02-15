import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { Construction } from 'lucide-react'

export const Route = createLazyFileRoute('/premium')({
  component: PremiumPage,
})

// TODO: Implement full premium features management page
function PremiumPage() {
  return (
    <div className="flex flex-col items-center justify-center py-24 text-center">
      <Construction className="h-12 w-12 text-muted-foreground/50 mb-4" />
      <h3 className="text-lg font-medium mb-1">{__('Coming Soon', 'wp-statistics')}</h3>
      <p className="text-sm text-muted-foreground max-w-md">
        {__('Unlock advanced analytics features with WP Statistics Premium.', 'wp-statistics')}
      </p>
    </div>
  )
}
