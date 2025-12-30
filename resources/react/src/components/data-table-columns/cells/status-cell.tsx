/**
 * StatusCell - Displays visitor status (new/returning) with tooltip showing first visit date.
 * Shared between visitors-columns.tsx and top-visitors-columns.tsx
 */

import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'

interface StatusCellProps {
  status: 'new' | 'returning'
  firstVisit: Date
}

export function StatusCell({ status, firstVisit }: StatusCellProps) {
  const isNew = status === 'new'
  const dateStr = firstVisit.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })

  return (
    <TooltipProvider>
      <Tooltip>
        <TooltipTrigger asChild>
          <Badge variant={isNew ? 'default' : 'secondary'} className="text-xs font-normal capitalize">
            {status}
          </Badge>
        </TooltipTrigger>
        <TooltipContent>{isNew ? `First visit ${dateStr}` : `Since ${dateStr}`}</TooltipContent>
      </Tooltip>
    </TooltipProvider>
  )
}
