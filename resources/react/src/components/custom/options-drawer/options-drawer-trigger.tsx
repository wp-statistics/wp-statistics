import { __ } from '@wordpress/i18n'
import { SlidersHorizontalIcon } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

interface OptionsDrawerTriggerProps {
  onClick: () => void
  isActive?: boolean
  className?: string
}

export function OptionsDrawerTrigger({
  onClick,
  isActive = false,
  className,
}: OptionsDrawerTriggerProps) {
  return (
    <Button
      variant="outline"
      size="sm"
      onClick={onClick}
      className={cn(
        'h-8 text-xs font-medium border-neutral-200 hover:bg-neutral-50',
        isActive && 'border-indigo-200 bg-indigo-50 text-primary',
        className
      )}
      aria-label={__('Open options', 'wp-statistics')}
    >
      <SlidersHorizontalIcon className={cn('h-3.5 w-3.5', !isActive && 'text-neutral-500')} />
      {__('Options', 'wp-statistics')}
    </Button>
  )
}
