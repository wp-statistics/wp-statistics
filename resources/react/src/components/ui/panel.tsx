import * as React from 'react'

import { cn } from '@/lib/utils'

/**
 * Panel - A simple visual container component
 *
 * Use Panel when you need a boxed container with consistent styling
 * (shadow, border, rounded corners, background) without imposed internal structure.
 *
 * For structured content with header/body/footer, use Card instead.
 *
 * @example
 * // Simple container
 * <Panel>
 *   <Metrics metrics={data} />
 * </Panel>
 *
 * // With custom padding
 * <Panel className="p-4">
 *   <CustomContent />
 * </Panel>
 */
function Panel({ className, ...props }: React.ComponentProps<'div'>) {
  return (
    <div
      data-slot="panel"
      className={cn('bg-card text-card-foreground rounded-xl border shadow-sm overflow-hidden', className)}
      {...props}
    />
  )
}

export { Panel }
