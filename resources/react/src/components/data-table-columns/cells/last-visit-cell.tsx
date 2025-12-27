/**
 * LastVisitCell - Displays date and time in stacked format
 */

interface LastVisitCellProps {
  date: Date
}

export function LastVisitCell({ date }: LastVisitCellProps) {
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
      <span className="whitespace-nowrap">{formattedDate}</span>
      <span className="text-[11px] text-muted-foreground">{formattedTime}</span>
    </div>
  )
}
