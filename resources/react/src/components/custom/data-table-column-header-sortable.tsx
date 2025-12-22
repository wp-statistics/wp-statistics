import { Button } from '@components/ui/button'
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
    return <div className={cn(className)}>{title}</div>
  }

  return (
    <div className={cn('flex items-center space-x-2', isRightAlign && 'justify-end', className)}>
      <Button
        variant="ghost"
        size="sm"
        className={cn('-ml-3 h-8 hover:bg-inherit font-normal text-sm text-foreground', isRightAlign && 'ml-0 -mr-3')}
        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
      >
        <span>{title}</span>
        {column.getIsSorted() === 'desc' ? (
          <ChevronDown className="h-4 w-4" />
        ) : column.getIsSorted() === 'asc' ? (
          <ChevronUp className="h-4 w-4" />
        ) : (
          <ChevronsUpDown className="h-4 w-4" />
        )}
      </Button>
    </div>
  )
}
