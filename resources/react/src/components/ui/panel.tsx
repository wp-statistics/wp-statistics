import { ChevronRight } from 'lucide-react'
import * as React from 'react'

import { cn } from '@/lib/utils'

/**
 * Panel - A unified widget container with compact p-4 (16px) padding
 *
 * Use Panel for dashboard widgets that need consistent, compact styling.
 * Provides optional header/content/footer structure with 16px padding.
 *
 * @example
 * // Simple container (content handles its own padding)
 * <Panel>
 *   <Metrics metrics={data} />
 * </Panel>
 *
 * // Structured widget with header and content
 * <Panel>
 *   <PanelHeader>
 *     <PanelTitle>Traffic Trends</PanelTitle>
 *   </PanelHeader>
 *   <PanelContent>
 *     <Chart />
 *   </PanelContent>
 * </Panel>
 *
 * // Full structure with footer action
 * <Panel>
 *   <PanelHeader>
 *     <PanelTitle>Top Countries</PanelTitle>
 *   </PanelHeader>
 *   <PanelContent>
 *     <BarList items={items} />
 *   </PanelContent>
 *   <PanelFooter>
 *     <PanelAction onClick={handleClick}>View all countries</PanelAction>
 *   </PanelFooter>
 * </Panel>
 */
function Panel({ className, ...props }: React.ComponentProps<'div'>) {
  return (
    <div
      data-slot="panel"
      className={cn('bg-card text-card-foreground rounded-lg border overflow-hidden', className)}
      {...props}
    />
  )
}

function PanelHeader({ className, ...props }: React.ComponentProps<'div'>) {
  return (
    <div
      data-slot="panel-header"
      className={cn('flex items-center justify-between px-4 pt-4 pb-3', className)}
      {...props}
    />
  )
}

function PanelTitle({ className, ...props }: React.ComponentProps<'h3'>) {
  return (
    <h3
      data-slot="panel-title"
      className={cn('text-sm font-semibold text-neutral-800 leading-none', className)}
      {...props}
    />
  )
}

function PanelActions({ className, ...props }: React.ComponentProps<'div'>) {
  return (
    <div
      data-slot="panel-actions"
      className={cn('flex items-center gap-2', className)}
      {...props}
    />
  )
}

function PanelContent({ className, ...props }: React.ComponentProps<'div'>) {
  return (
    <div
      data-slot="panel-content"
      className={cn('px-4 pb-4', className)}
      {...props}
    />
  )
}

function PanelFooter({ className, ...props }: React.ComponentProps<'div'>) {
  return (
    <div
      data-slot="panel-footer"
      className={cn('flex items-center justify-end px-4 py-3', className)}
      {...props}
    />
  )
}

/**
 * PanelAction - Standardized action link for widget footers
 *
 * Provides consistent styling for "View all", "View full report" type links.
 * Automatically includes a chevron icon.
 */
function PanelAction({ children, className, ...props }: React.ComponentProps<'button'>) {
  return (
    <button
      data-slot="panel-action"
      aria-label={typeof children === 'string' ? children : 'Panel action'}
      className={cn(
        'inline-flex items-center gap-1.5 cursor-pointer',
        'text-xs font-medium text-neutral-500',
        'hover:text-neutral-700 transition-colors',
        className
      )}
      {...props}
    >
      {children}
      <ChevronRight className="h-3.5 w-3.5" />
    </button>
  )
}

export { Panel, PanelAction,PanelActions, PanelContent, PanelFooter, PanelHeader, PanelTitle }
