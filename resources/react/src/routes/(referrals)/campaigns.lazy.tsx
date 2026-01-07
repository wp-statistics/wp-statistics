import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { Megaphone } from 'lucide-react'

import { AddonPromo } from '@/components/custom/addon-promo'
import { NoticeContainer } from '@/components/ui/notice-container'

export const Route = createLazyFileRoute('/(referrals)/campaigns')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <div className="min-w-0">
      {/* Header row */}
      <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
        <h1 className="text-xl font-semibold text-neutral-800">{__('Campaigns', 'wp-statistics')}</h1>
      </div>

      <div className="p-2">
        <NoticeContainer className="mb-2" currentRoute="campaigns" />
        <AddonPromo
          title={__('Marketing Campaigns', 'wp-statistics')}
          description={__(
            'Track your marketing campaigns with detailed UTM reports. Monitor campaign performance, measure ROI, and optimize your marketing strategy.',
            'wp-statistics'
          )}
          addonName={__('Marketing', 'wp-statistics')}
          learnMoreUrl="https://wp-statistics.com/product/wp-statistics-marketing/?utm_source=plugin&utm_medium=link&utm_campaign=campaigns"
          icon={Megaphone}
        />
      </div>
    </div>
  )
}
