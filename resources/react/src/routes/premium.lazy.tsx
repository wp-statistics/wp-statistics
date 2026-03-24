import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { Construction } from 'lucide-react'
import { useEffect } from 'react'

import { WordPress } from '@/lib/wordpress'

export const Route = createLazyFileRoute('/premium')({
  component: PremiumPage,
})

function PremiumPage() {
  const navigate = useNavigate()

  useEffect(() => {
    const premium = WordPress.getInstance().getData<{ active?: boolean }>('premium')
    if (premium?.active) {
      navigate({ to: '/license', replace: true })
    }
  }, [navigate])

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
