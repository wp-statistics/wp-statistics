interface BarListHeaderProps {
  left: string
  right: string
}

export function BarListHeader({ left, right }: BarListHeaderProps) {
  return (
    <div className="flex items-center justify-between text-xs text-muted-foreground pb-2 mb-1 border-b border-border">
      <span>{left}</span>
      <span>{right}</span>
    </div>
  )
}
