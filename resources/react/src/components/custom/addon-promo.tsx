import { __ } from '@wordpress/i18n'
import type { LucideIcon } from 'lucide-react'
import { Megaphone } from 'lucide-react'

import { Panel } from '@/components/ui/panel'
import { cn } from '@/lib/utils'

export interface AddonPromoProps {
  /** The title of the addon feature */
  title: string
  /** Description of what the addon provides */
  description: string
  /** Name of the required addon */
  addonName: string
  /** URL to learn more or purchase the addon */
  learnMoreUrl: string
  /** Optional icon component (defaults to Megaphone) */
  icon?: LucideIcon
  /** Optional additional CSS classes */
  className?: string
}

/**
 * AddonPromo - Promotional component for features requiring premium addons
 *
 * Displays a centered card with an icon, title, description, and CTA button
 * to encourage users to purchase or learn more about an addon.
 *
 * @example
 * <AddonPromo
 *   title={__('Marketing Campaigns', 'wp-statistics')}
 *   description={__('Track your marketing campaigns with detailed UTM reports.', 'wp-statistics')}
 *   addonName={__('Marketing', 'wp-statistics')}
 *   learnMoreUrl="https://wp-statistics.com/product/wp-statistics-marketing/"
 * />
 */
export function AddonPromo({
  title,
  description,
  addonName,
  learnMoreUrl,
  icon: Icon = Megaphone,
  className,
}: AddonPromoProps) {
  return (
    <Panel className={cn('p-8 text-center', className)}>
      <div className="max-w-md mx-auto space-y-4">
        <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
          <Icon className="w-8 h-8 text-primary" strokeWidth={1.5} />
        </div>
        <h2 className="text-lg font-semibold text-neutral-800">{title}</h2>
        <p className="text-sm text-muted-foreground">{description}</p>
        <p className="text-sm text-muted-foreground">
          {/* translators: %s: Name of the addon (e.g., "Marketing", "Data Plus") */}
          {__('This feature requires the %s addon.', 'wp-statistics').replace('%s', addonName)}
        </p>
        <a
          href={learnMoreUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 transition-colors"
        >
          {__('Learn More', 'wp-statistics')}
        </a>
      </div>
    </Panel>
  )
}
