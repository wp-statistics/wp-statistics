import { Button } from '@components/ui/button'
import { Checkbox } from '@components/ui/checkbox'
import { Popover, PopoverContent, PopoverTrigger } from '@components/ui/popover'
import {
  closestCenter,
  DndContext,
  type DragEndEvent,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
} from '@dnd-kit/core'
import {
  arrayMove,
  SortableContext,
  sortableKeyboardCoordinates,
  useSortable,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable'
import { CSS } from '@dnd-kit/utilities'
import type { Table, VisibilityState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { Columns, GripVertical, RotateCcw } from 'lucide-react'
import * as React from 'react'

interface ColumnItem {
  id: string
  label: string
  isVisible: boolean
  showComparison?: boolean
  isComparable?: boolean
}

interface SortableItemProps {
  item: ColumnItem
  onToggle: (id: string, checked: boolean) => void
  onComparisonToggle?: (id: string, checked: boolean) => void
  disabled?: boolean
}

function SortableItem({ item, onToggle, onComparisonToggle, disabled }: SortableItemProps) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: item.id,
  })

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.5 : 1,
  }

  const handleRowClick = (e: React.MouseEvent) => {
    if (disabled) return
    const target = e.target as HTMLElement
    if (target.closest('[data-drag-handle]') || target.closest('[data-comparison-toggle]')) return
    onToggle(item.id, !item.isVisible)
  }

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (disabled) return
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault()
      onToggle(item.id, !item.isVisible)
    }
  }

  return (
    <div
      ref={setNodeRef}
      style={style}
      role="button"
      tabIndex={disabled ? -1 : 0}
      aria-label={`${item.label}, ${item.isVisible ? __('visible', 'wp-statistics') : __('hidden', 'wp-statistics')}`}
      className={`flex items-center gap-2 px-2 py-1.5 hover:bg-accent rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-1 ${disabled ? 'cursor-not-allowed' : 'cursor-pointer'}`}
      onClick={handleRowClick}
      onKeyDown={handleKeyDown}
    >
      <div data-drag-handle {...attributes} {...listeners} className="cursor-grab active:cursor-grabbing">
        <GripVertical className="h-3.5 w-3.5 text-muted-foreground" />
      </div>
      <Checkbox
        checked={item.isVisible}
        disabled={disabled}
        onCheckedChange={(checked) => onToggle(item.id, checked as boolean)}
        onClick={(e) => e.stopPropagation()}
        className="h-3.5 w-3.5"
      />
      <span
        className={`flex-1 text-xs font-normal select-none ${disabled ? 'opacity-50' : ''} ${!item.isVisible ? 'text-muted-foreground' : ''}`}
      >
        {item.label}
      </span>
    </div>
  )
}

interface DataTableColumnToggleProps<TData> {
  table: Table<TData>
  initialColumnOrder?: string[]
  defaultHiddenColumns?: string[]
  /** Columns that support comparison display */
  comparableColumns?: string[]
  /** Currently active comparison columns */
  comparisonColumns?: string[]
  /** Default comparison columns (used on reset) */
  defaultComparisonColumns?: string[]
  onColumnVisibilityChange?: (visibility: VisibilityState) => void
  onColumnOrderChange?: (order: string[]) => void
  onComparisonColumnsChange?: (columns: string[]) => void
  onReset?: () => void
}

export function DataTableColumnToggle<TData>({
  table,
  initialColumnOrder,
  defaultHiddenColumns = [],
  comparableColumns = [],
  comparisonColumns = [],
  defaultComparisonColumns = [],
  onColumnVisibilityChange,
  onColumnOrderChange,
  onComparisonColumnsChange,
  onReset,
}: DataTableColumnToggleProps<TData>) {
  const isRTL = document.dir === 'rtl' || document.documentElement.dir === 'rtl'

  const [columnOrder, setColumnOrder] = React.useState<ColumnItem[]>([])
  const [isOpen, setIsOpen] = React.useState(false)

  // Helper to get display label from column
  const getColumnLabel = React.useCallback(
    (column: { id: string; columnDef: { meta?: { title?: string; mobileLabel?: string } } }): string => {
      const meta = column.columnDef.meta as { title?: string; mobileLabel?: string } | undefined
      return meta?.title || meta?.mobileLabel || column.id
    },
    []
  )

  // Build column list when popover opens
  const handleOpenChange = React.useCallback(
    (open: boolean) => {
      setIsOpen(open)
      if (open) {
        const columns = table.getAllColumns().filter((column) => column.getCanHide())

        let items: ColumnItem[]
        if (initialColumnOrder && initialColumnOrder.length > 0) {
          items = []
          initialColumnOrder.forEach((id) => {
            const column = columns.find((c) => c.id === id)
            if (column) {
              items.push({
                id: column.id,
                label: getColumnLabel(column),
                isVisible: column.getIsVisible(),
                isComparable: comparableColumns.includes(column.id),
                showComparison: comparisonColumns.includes(column.id),
              })
            }
          })
          columns.forEach((column) => {
            if (!initialColumnOrder.includes(column.id)) {
              items.push({
                id: column.id,
                label: getColumnLabel(column),
                isVisible: column.getIsVisible(),
                isComparable: comparableColumns.includes(column.id),
                showComparison: comparisonColumns.includes(column.id),
              })
            }
          })
        } else {
          items = columns.map((column) => ({
            id: column.id,
            label: getColumnLabel(column),
            isVisible: column.getIsVisible(),
            isComparable: comparableColumns.includes(column.id),
            showComparison: comparisonColumns.includes(column.id),
          }))
        }
        setColumnOrder(items)
      }
    },
    [table, initialColumnOrder, getColumnLabel, comparableColumns, comparisonColumns]
  )

  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    })
  )

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event

    if (over && active.id !== over.id) {
      setColumnOrder((items) => {
        const oldIndex = items.findIndex((item) => item.id === active.id)
        const newIndex = items.findIndex((item) => item.id === over.id)

        const newOrder = arrayMove(items, oldIndex, newIndex)
        const newOrderIds = newOrder.map((item) => item.id)

        table.setColumnOrder(newOrderIds)

        if (onColumnOrderChange) {
          onColumnOrderChange(newOrderIds)
        }

        return newOrder
      })
    }
  }

  const handleToggle = (id: string, checked: boolean) => {
    const column = table.getColumn(id)
    if (column) {
      column.toggleVisibility(checked)
      setColumnOrder((items) => {
        const updatedItems = items.map((item) => (item.id === id ? { ...item, isVisible: checked } : item))

        if (onColumnVisibilityChange) {
          const newVisibility: VisibilityState = {}
          updatedItems.forEach((item) => {
            newVisibility[item.id] = item.isVisible
          })
          onColumnVisibilityChange(newVisibility)
        }

        return updatedItems
      })
    }
  }

  const handleComparisonToggle = (id: string, checked: boolean) => {
    setColumnOrder((items) => {
      const updatedItems = items.map((item) =>
        item.id === id ? { ...item, showComparison: checked } : item
      )

      if (onComparisonColumnsChange) {
        const newComparisonColumns = updatedItems
          .filter((item) => item.showComparison)
          .map((item) => item.id)
        onComparisonColumnsChange(newComparisonColumns)
      }

      return updatedItems
    })
  }

  const handleReset = () => {
    const columns = table.getAllColumns().filter((column) => column.getCanHide())

    const defaultOrder = columns.map((column) => ({
      id: column.id,
      label: getColumnLabel(column),
      isVisible: !defaultHiddenColumns.includes(column.id),
      isComparable: comparableColumns.includes(column.id),
      showComparison: defaultComparisonColumns.includes(column.id),
    }))
    setColumnOrder(defaultOrder)

    table.setColumnOrder(defaultOrder.map((item) => item.id))

    columns.forEach((column) => {
      const shouldBeVisible = !defaultHiddenColumns.includes(column.id)
      column.toggleVisibility(shouldBeVisible)
    })

    // Reset comparison columns to default
    if (onComparisonColumnsChange) {
      onComparisonColumnsChange(defaultComparisonColumns)
    }

    if (onReset) {
      onReset()
    }
  }

  const visibleCount = columnOrder.filter((item) => item.isVisible).length

  return (
    <Popover open={isOpen} onOpenChange={handleOpenChange}>
      <PopoverTrigger asChild>
        <Button variant="ghost" size="sm" className="h-7 px-2 text-xs text-muted-foreground hover:text-foreground">
          <Columns className="h-3.5 w-3.5 mr-1.5" />
          {__('Columns', 'wp-statistics')}
        </Button>
      </PopoverTrigger>
      <PopoverContent align={isRTL ? 'end' : 'start'} className="w-[220px] p-0">
        {/* Header */}
        <div className="flex items-center justify-between px-3 py-2 border-b border-neutral-100 bg-neutral-50/50">
          <span className="text-sm font-medium text-neutral-700">{__('Columns', 'wp-statistics')}</span>
          <Button
            variant="ghost"
            size="sm"
            className="h-7 px-2 text-xs text-muted-foreground hover:text-foreground"
            onClick={handleReset}
          >
            <RotateCcw className="h-3 w-3 mr-1" />
            {__('Reset', 'wp-statistics')}
          </Button>
        </div>

        {/* Single unified list */}
        <div className="p-2 max-h-[300px] overflow-y-auto">
          <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
            <SortableContext items={columnOrder.map((item) => item.id)} strategy={verticalListSortingStrategy}>
              {columnOrder.map((item) => (
                <SortableItem
                  key={item.id}
                  item={item}
                  onToggle={handleToggle}
                  onComparisonToggle={comparableColumns.length > 0 ? handleComparisonToggle : undefined}
                  disabled={visibleCount === 1 && item.isVisible}
                />
              ))}
            </SortableContext>
          </DndContext>
        </div>
      </PopoverContent>
    </Popover>
  )
}
