/**
 * DurationCell - Displays duration in smart time format (mm:ss or h:mm:ss)
 */

interface DurationCellProps {
  seconds: number
}

/**
 * Format seconds to time string
 * Shows hours only when > 0: "5:30" or "1:05:30"
 */
function formatDuration(seconds: number): string {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60

  if (hours > 0) {
    return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
  }
  return `${minutes}:${String(secs).padStart(2, '0')}`
}

export function DurationCell({ seconds }: DurationCellProps) {
  return (
    <div className="text-right">
      <span className="tabular-nums font-medium text-neutral-700">{formatDuration(seconds)}</span>
    </div>
  )
}
