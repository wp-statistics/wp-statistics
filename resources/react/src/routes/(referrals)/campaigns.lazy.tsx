import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import { Panel } from '@/components/ui/panel'

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
        <Panel className="p-8 text-center">
          <div className="max-w-md mx-auto space-y-4">
            <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="w-8 h-8 text-primary"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                strokeWidth={1.5}
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"
                />
              </svg>
            </div>
            <h2 className="text-lg font-semibold text-neutral-800">
              {__('Marketing Campaigns', 'wp-statistics')}
            </h2>
            <p className="text-sm text-muted-foreground">
              {__(
                'Track your marketing campaigns with detailed UTM reports. Monitor campaign performance, measure ROI, and optimize your marketing strategy.',
                'wp-statistics'
              )}
            </p>
            <p className="text-sm text-muted-foreground">
              {__('This feature requires the Marketing addon.', 'wp-statistics')}
            </p>
            <a
              href="https://wp-statistics.com/product/wp-statistics-marketing/?utm_source=plugin&utm_medium=link&utm_campaign=campaigns"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 transition-colors"
            >
              {__('Learn More', 'wp-statistics')}
            </a>
          </div>
        </Panel>
      </div>
    </div>
  )
}
