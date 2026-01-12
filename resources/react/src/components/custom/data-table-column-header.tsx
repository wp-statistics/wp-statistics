import type { Column, Header, Table } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { ArrowDownWideNarrow, ArrowLeft, ArrowRight, ArrowUpNarrowWide, ChevronDown, EyeOff, MoveHorizontal } from 'lucide-react'

import { cn } from '@/lib/utils'

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuSub,
  DropdownMenuSubContent,
  DropdownMenuSubTrigger,
  DropdownMenuTrigger,
} from '../ui/dropdown-menu'

interface DataTableColumnHeaderProps<TData, TValue> extends React.HTMLAttributes<HTMLDivElement> {
  column: Column<TData, TValue>
  header?: Header<TData, TValue>
  table?: Table<TData>
  title: string
}

export function DataTableColumnHeader<TData, TValue>({
  column,
  header: _header,
  table,
  title,
  className,
}: DataTableColumnHeaderProps<TData, TValue>) {
  const isRTL = typeof document !== 'undefined' && (document.dir === 'rtl' || document.documentElement.dir === 'rtl')
  const isRightAlign = className?.includes('text-right')
  const canSort = column.getCanSort()
  const canHide = column.getCanHide()
  const isSorted = column.getIsSorted()

  // Get column position info for move actions
  const getColumnPositionInfo = () => {
    if (!table) return { isFirst: true, isLast: true, canMove: false }

    const visibleColumns = table.getAllLeafColumns().filter((c) => c.getIsVisible())
    const visibleColumnIds = visibleColumns.map((c) => c.id)
    const currentIndex = visibleColumnIds.indexOf(column.id)

    return {
      isFirst: currentIndex === 0,
      isLast: currentIndex === visibleColumnIds.length - 1,
      canMove: visibleColumns.length > 1,
    }
  }

  const { isFirst, isLast, canMove } = getColumnPositionInfo()

  // Check if this is the only visible column
  const visibleColumnsCount = table?.getAllLeafColumns().filter((c) => c.getIsVisible()).length ?? 1
  const canHideColumn = canHide && visibleColumnsCount > 1

  // Sort handlers
  const handleSortAsc = () => {
    column.toggleSorting(false) // false = ascending
  }

  const handleSortDesc = () => {
    column.toggleSorting(true) // true = descending
  }

  // Move column handlers
  const moveColumn = (direction: 'left' | 'right') => {
    if (!table) return

    const allColumns = table.getAllLeafColumns()
    const currentOrder = table.getState().columnOrder?.length
      ? table.getState().columnOrder
      : allColumns.map((c) => c.id)

    const currentIndex = currentOrder.indexOf(column.id)
    const targetIndex = direction === 'left' ? currentIndex - 1 : currentIndex + 1

    if (targetIndex < 0 || targetIndex >= currentOrder.length) return

    const newOrder = [...currentOrder]
    ;[newOrder[currentIndex], newOrder[targetIndex]] = [newOrder[targetIndex], newOrder[currentIndex]]
    table.setColumnOrder(newOrder)
  }

  const handleMoveLeft = () => moveColumn(isRTL ? 'right' : 'left')
  const handleMoveRight = () => moveColumn(isRTL ? 'left' : 'right')

  // Hide column handler
  const handleHide = () => {
    column.toggleVisibility(false)
  }

  // Determine if we should show the dropdown (only if there are actions available)
  const hasActions = canSort || canHideColumn || (table && canMove)

  // Render sort indicator - only show when actively sorted
  const renderSortIndicator = () => {
    if (!canSort || !isSorted) return null

    if (isSorted === 'desc') {
      return <ArrowDownWideNarrow className="h-3 w-3" />
    } else if (isSorted === 'asc') {
      return <ArrowUpNarrowWide className="h-3 w-3" />
    }
    return null
  }

  // If no actions available, just render the title
  if (!hasActions) {
    return <span className={cn('', className)}>{title}</span>
  }

  return (
    <div className={cn('group flex items-center gap-0.5', isRightAlign && 'justify-end', className)}>
      {/* Clickable title for inline sorting */}
      {canSort ? (
        <button
          type="button"
          className={cn('inline-flex items-center gap-1 hover:text-foreground transition-colors')}
          onClick={() => column.toggleSorting(isSorted === 'asc')}
          aria-label={`${__('Sort by', 'wp-statistics')} ${title}${isSorted ? (isSorted === 'asc' ? __(', currently ascending', 'wp-statistics') : __(', currently descending', 'wp-statistics')) : ''}`}
        >
          <span>{title}</span>
          {renderSortIndicator()}
        </button>
      ) : (
        <span>{title}</span>
      )}

      {/* Dropdown menu trigger - visible on hover */}
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <button
            type="button"
            className="opacity-0 group-hover:opacity-100 transition-opacity p-0.5 hover:bg-neutral-100 rounded"
            aria-label={__('Column options', 'wp-statistics')}
          >
            <ChevronDown className="h-3 w-3" />
          </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align={isRTL ? 'end' : 'start'} className="w-48">
          {/* Move column options - only if table is available */}
          {table && canMove && (
            <DropdownMenuSub>
              <DropdownMenuSubTrigger className="gap-2">
                <MoveHorizontal className="h-3.5 w-3.5" />
                {__('Move column', 'wp-statistics')}
              </DropdownMenuSubTrigger>
              <DropdownMenuSubContent>
                <DropdownMenuItem
                  onClick={handleMoveLeft}
                  disabled={isRTL ? isLast : isFirst}
                  className="gap-2"
                >
                  <ArrowLeft className="h-3.5 w-3.5" />
                  {__('Move left', 'wp-statistics')}
                </DropdownMenuItem>
                <DropdownMenuItem
                  onClick={handleMoveRight}
                  disabled={isRTL ? isFirst : isLast}
                  className="gap-2"
                >
                  <ArrowRight className="h-3.5 w-3.5" />
                  {__('Move right', 'wp-statistics')}
                </DropdownMenuItem>
              </DropdownMenuSubContent>
            </DropdownMenuSub>
          )}

          {/* Sort options */}
          {canSort && (
            <>
              {table && canMove && <DropdownMenuSeparator />}
              <DropdownMenuItem onClick={handleSortAsc} className="gap-2">
                <ArrowUpNarrowWide className="h-3.5 w-3.5" />
                {__('Sort ascending', 'wp-statistics')}
              </DropdownMenuItem>
              <DropdownMenuItem onClick={handleSortDesc} className="gap-2">
                <ArrowDownWideNarrow className="h-3.5 w-3.5" />
                {__('Sort descending', 'wp-statistics')}
              </DropdownMenuItem>
            </>
          )}

          {/* Hide column option */}
          {canHideColumn && (
            <>
              {(canSort || (table && canMove)) && <DropdownMenuSeparator />}
              <DropdownMenuItem onClick={handleHide} className="gap-2">
                <EyeOff className="h-3.5 w-3.5" />
                {__('Hide column', 'wp-statistics')}
              </DropdownMenuItem>
            </>
          )}
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  )
}
