interface BarListHeaderProps {
  left: string
  right: string
}

export function BarListHeader({ left, right }: BarListHeaderProps) {
  return (
    <div className="flex items-center justify-between text-xs text-neutral-500 pb-2 mb-1 border-b border-neutral-200">
      <span>{left}</span>
      <span>{right}</span>
    </div>
  )
}
