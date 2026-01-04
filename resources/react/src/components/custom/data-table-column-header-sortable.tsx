import { cn } from '@lib/utils'
import type { Column } from '@tanstack/react-table'
import { ChevronDown, ChevronsUpDown, ChevronUp } from 'lucide-react'

interface DataTableColumnHeaderSortableProps<TData, TValue> extends React.HTMLAttributes<HTMLDivElement> {
  column: Column<TData, TValue>
  title: string
}

export function DataTableColumnHeaderSortable<TData, TValue>({
  column,
  title,
  className,
}: DataTableColumnHeaderSortableProps<TData, TValue>) {
  const isRightAlign = className?.includes('text-right')

  if (!column.getCanSort()) {
    return <span className={cn('uppercase', className)}>{title}</span>
  }

  return (
    <button
      type="button"
      className={cn('inline-flex items-center gap-1 uppercase hover:text-foreground transition-colors', isRightAlign && 'w-full justify-end', className)}
      onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
      aria-label={`Sort by ${title}${column.getIsSorted() ? (column.getIsSorted() === 'asc' ? ', currently ascending' : ', currently descending') : ''}`}
    >
      <span>{title}</span>
      {column.getIsSorted() === 'desc' ? (
        <ChevronDown className="h-3 w-3" />
      ) : column.getIsSorted() === 'asc' ? (
        <ChevronUp className="h-3 w-3" />
      ) : (
        <ChevronsUpDown className="h-3 w-3 opacity-50" />
      )}
    </button>
  )
}
