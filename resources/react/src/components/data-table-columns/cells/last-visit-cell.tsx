/**
 * LastVisitCell - Displays date and time in stacked format
 */

import { memo } from 'react'

interface LastVisitCellProps {
  date: Date
}

export const LastVisitCell = memo(function LastVisitCell({ date }: LastVisitCellProps) {
  const formattedDate = date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  })
  const formattedTime = date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
  })

  return (
    <div className="flex flex-col">
      <span className="whitespace-nowrap text-xs text-foreground">{formattedDate}</span>
      <span className="whitespace-nowrap text-xs text-muted-foreground">{formattedTime}</span>
    </div>
  )
})
