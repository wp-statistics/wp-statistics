/**
 * TooltipWrapper - Simplified tooltip component
 * Reduces boilerplate from 8+ lines to 1 line for common tooltip usage
 */

import type { ReactNode } from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

export interface TooltipWrapperProps {
  /**
   * Content to show in the tooltip
   */
  content: ReactNode
  /**
   * The element that triggers the tooltip
   */
  children: ReactNode
  /**
   * Position of the tooltip relative to trigger
   */
  side?: 'top' | 'right' | 'bottom' | 'left'
  /**
   * Whether to show the tooltip arrow
   */
  showArrow?: boolean
  /**
   * Additional className for the tooltip content
   */
  contentClassName?: string
}

/**
 * Simplified tooltip wrapper that reduces boilerplate
 *
 * @example
 * // Before (8 lines):
 * <TooltipProvider>
 *   <Tooltip>
 *     <TooltipTrigger asChild>
 *       <button>Hover me</button>
 *     </TooltipTrigger>
 *     <TooltipContent>
 *       <p>Tooltip text</p>
 *     </TooltipContent>
 *   </Tooltip>
 * </TooltipProvider>
 *
 * // After (3 lines):
 * <TooltipWrapper content="Tooltip text">
 *   <button>Hover me</button>
 * </TooltipWrapper>
 */
export function TooltipWrapper({
  content,
  children,
  side = 'top',
  showArrow = true,
  contentClassName,
}: TooltipWrapperProps) {
  return (
    <Tooltip>
      <TooltipTrigger asChild>{children}</TooltipTrigger>
      <TooltipContent side={side} showArrow={showArrow} className={contentClassName}>
        {typeof content === 'string' ? <p>{content}</p> : content}
      </TooltipContent>
    </Tooltip>
  )
}
