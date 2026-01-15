/**
 * UriCell - Displays a URI path in monospace font with optional truncation
 *
 * Reusable component for displaying raw URL paths (without domain).
 * Can be used in 404 pages, entry pages, exit pages, or any URI-based report.
 */

import { memo } from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

interface UriCellProps {
  uri: string
  maxLength?: number
}

export const UriCell = memo(function UriCell({ uri, maxLength = 50 }: UriCellProps) {
  const displayUri = uri || '/'
  const shouldTruncate = maxLength > 0 && displayUri.length > maxLength
  const truncatedUri = shouldTruncate ? `${displayUri.slice(0, maxLength)}...` : displayUri

  if (shouldTruncate) {
    return (
      <Tooltip>
        <TooltipTrigger asChild>
          <span className="text-xs font-mono text-neutral-700 cursor-default">{truncatedUri}</span>
        </TooltipTrigger>
        <TooltipContent side="top" className="max-w-md break-all">
          {displayUri}
        </TooltipContent>
      </Tooltip>
    )
  }

  return <span className="text-xs font-mono text-neutral-700">{displayUri}</span>
})
