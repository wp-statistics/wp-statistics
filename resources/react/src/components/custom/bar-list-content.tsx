import { __ } from '@wordpress/i18n'

import { EmptyState } from '@/components/ui/empty-state'

export function BarListContent({ children, isEmpty }: { children: React.ReactNode; isEmpty?: boolean }) {
  if (isEmpty) return <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
  return <div className="flex flex-col gap-3">{children}</div>
}
