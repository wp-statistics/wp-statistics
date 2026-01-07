import type { SortingState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { ArrowDownAZ, ArrowUpAZ, Check, Loader2 } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { cn } from '@/lib/utils'

interface SortableColumn {
  id: string
  label: string
}

interface DataTableMobileHeaderProps {
  title?: string
  sorting?: SortingState
  onSortingChange?: (sorting: SortingState) => void
  sortableColumns: SortableColumn[]
  isFetching?: boolean
}

export function DataTableMobileHeader({
  title,
  sorting,
  onSortingChange,
  sortableColumns,
  isFetching,
}: DataTableMobileHeaderProps) {
  const currentSort = sorting?.[0]

  const handleSort = (columnId: string) => {
    if (!onSortingChange) return

    // If already sorting by this column, toggle direction
    if (currentSort?.id === columnId) {
      onSortingChange([{ id: columnId, desc: !currentSort.desc }])
    } else {
      // Default to descending for new column
      onSortingChange([{ id: columnId, desc: true }])
    }
  }

  return (
    <div className="flex items-center justify-between px-3 py-3 border-b border-neutral-200 bg-white">
      <div className="flex items-center gap-2 min-w-0">
        {title && <h2 className="text-base font-semibold truncate">{title}</h2>}
        {isFetching && <Loader2 className="h-4 w-4 animate-spin text-neutral-400 shrink-0" />}
      </div>

      {/* Sort dropdown */}
      {sortableColumns.length > 0 && onSortingChange && (
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="ghost" size="sm" className="h-10 px-3 gap-1.5 shrink-0">
              {currentSort?.desc ? <ArrowDownAZ className="h-4 w-4" /> : <ArrowUpAZ className="h-4 w-4" />}
              <span className="text-xs">{__('Sort', 'wp-statistics')}</span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="w-48">
            {sortableColumns.map((col) => (
              <DropdownMenuItem
                key={col.id}
                onClick={() => handleSort(col.id)}
                className={cn('text-xs min-h-[44px] flex items-center justify-between')}
              >
                <span>{col.label}</span>
                {currentSort?.id === col.id && (
                  <div className="flex items-center gap-1">
                    <span className="text-[10px] text-neutral-500">
                      {currentSort.desc ? __('High→Low', 'wp-statistics') : __('Low→High', 'wp-statistics')}
                    </span>
                    <Check className="h-3.5 w-3.5" />
                  </div>
                )}
              </DropdownMenuItem>
            ))}
          </DropdownMenuContent>
        </DropdownMenu>
      )}
    </div>
  )
}
