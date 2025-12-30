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
}

interface SortableItemProps {
  item: ColumnItem
  onToggle: (id: string, checked: boolean) => void
  disabled?: boolean
}

function SortableItem({ item, onToggle, disabled }: SortableItemProps) {
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
    if (target.closest('[data-drag-handle]')) return
    onToggle(item.id, !item.isVisible)
  }

  return (
    <div
      ref={setNodeRef}
      style={style}
      className={`flex items-center gap-2 px-2 py-1.5 hover:bg-accent rounded-sm ${disabled ? 'cursor-not-allowed' : 'cursor-pointer'}`}
      onClick={handleRowClick}
    >
      <div
        data-drag-handle
        {...attributes}
        {...listeners}
        className="cursor-grab active:cursor-grabbing"
      >
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
        className={`flex-1 text-xs font-normal select-none ${disabled ? 'opacity-50' : ''}`}
      >
        {item.label}
      </span>
    </div>
  )
}

interface HiddenItemProps {
  item: ColumnItem
  onToggle: (id: string, checked: boolean) => void
}

function HiddenItem({ item, onToggle }: HiddenItemProps) {
  const handleRowClick = () => {
    onToggle(item.id, true)
  }

  return (
    <div
      className="flex items-center gap-2 px-2 py-1.5 hover:bg-accent rounded-sm cursor-pointer"
      onClick={handleRowClick}
    >
      <div className="w-3.5" /> {/* Spacer to align with visible items */}
      <Checkbox
        checked={false}
        onCheckedChange={() => onToggle(item.id, true)}
        onClick={(e) => e.stopPropagation()}
        className="h-3.5 w-3.5"
      />
      <span className="flex-1 text-xs font-normal select-none text-muted-foreground">
        {item.label}
      </span>
    </div>
  )
}

interface DataTableColumnToggleProps<TData> {
  table: Table<TData>
  initialColumnOrder?: string[]
  defaultHiddenColumns?: string[]
  onColumnVisibilityChange?: (visibility: VisibilityState) => void
  onColumnOrderChange?: (order: string[]) => void
  onReset?: () => void
}

export function DataTableColumnToggle<TData>({
  table,
  initialColumnOrder,
  defaultHiddenColumns = [],
  onColumnVisibilityChange,
  onColumnOrderChange,
  onReset,
}: DataTableColumnToggleProps<TData>) {
  const isRTL = document.dir === 'rtl' || document.documentElement.dir === 'rtl'

  const [columnOrder, setColumnOrder] = React.useState<ColumnItem[]>([])
  const [isOpen, setIsOpen] = React.useState(false)

  // Helper to get display label from column
  const getColumnLabel = React.useCallback((column: { id: string; columnDef: { meta?: { mobileLabel?: string } } }): string => {
    const meta = column.columnDef.meta as { mobileLabel?: string } | undefined
    return meta?.mobileLabel || column.id
  }, [])

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
              })
            }
          })
          columns.forEach((column) => {
            if (!initialColumnOrder.includes(column.id)) {
              items.push({
                id: column.id,
                label: getColumnLabel(column),
                isVisible: column.getIsVisible(),
              })
            }
          })
        } else {
          items = columns.map((column) => ({
            id: column.id,
            label: getColumnLabel(column),
            isVisible: column.getIsVisible(),
          }))
        }
        setColumnOrder(items)
      }
    },
    [table, initialColumnOrder, getColumnLabel]
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
        const visibleItems = items.filter((item) => item.isVisible)
        const hiddenItems = items.filter((item) => !item.isVisible)

        const oldIndex = visibleItems.findIndex((item) => item.id === active.id)
        const newIndex = visibleItems.findIndex((item) => item.id === over.id)

        const newVisibleOrder = arrayMove(visibleItems, oldIndex, newIndex)
        const newOrder = [...newVisibleOrder, ...hiddenItems]
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

  const handleReset = () => {
    const columns = table.getAllColumns().filter((column) => column.getCanHide())

    const defaultOrder = columns.map((column) => ({
      id: column.id,
      label: getColumnLabel(column),
      isVisible: !defaultHiddenColumns.includes(column.id),
    }))
    setColumnOrder(defaultOrder)

    table.setColumnOrder(defaultOrder.map((item) => item.id))

    columns.forEach((column) => {
      const shouldBeVisible = !defaultHiddenColumns.includes(column.id)
      column.toggleVisibility(shouldBeVisible)
    })

    if (onReset) {
      onReset()
    }
  }

  // Separate visible and hidden columns
  const visibleColumns = columnOrder.filter((item) => item.isVisible)
  const hiddenColumns = columnOrder.filter((item) => !item.isVisible)
  const visibleCount = visibleColumns.length
  const hiddenCount = hiddenColumns.length

  return (
    <Popover open={isOpen} onOpenChange={handleOpenChange}>
      <PopoverTrigger asChild>
        <Button variant="ghost" size="sm" className="h-7 px-2 text-xs text-muted-foreground hover:text-foreground">
          <Columns className="h-3.5 w-3.5 mr-1.5" />
          {__('Columns', 'wp-statistics')}
        </Button>
      </PopoverTrigger>
      <PopoverContent align={isRTL ? 'end' : 'start'} className="w-[260px] p-0">
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

        {/* Visible Section */}
        <div className="p-2">
          <span className="block px-2 pb-1.5 text-[10px] font-medium text-muted-foreground uppercase tracking-wider">
            {__('Visible', 'wp-statistics')} ({visibleCount})
          </span>
          <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
            <SortableContext items={visibleColumns.map((item) => item.id)} strategy={verticalListSortingStrategy}>
              {visibleColumns.map((item) => (
                <SortableItem
                  key={item.id}
                  item={item}
                  onToggle={handleToggle}
                  disabled={visibleCount === 1}
                />
              ))}
            </SortableContext>
          </DndContext>
        </div>

        {/* Hidden Section */}
        {hiddenCount > 0 && (
          <div className="p-2 border-t border-neutral-100 bg-neutral-50/30">
            <span className="block px-2 pb-1.5 text-[10px] font-medium text-muted-foreground uppercase tracking-wider">
              {__('Hidden', 'wp-statistics')} ({hiddenCount})
            </span>
            {hiddenColumns.map((item) => (
              <HiddenItem key={item.id} item={item} onToggle={handleToggle} />
            ))}
          </div>
        )}
      </PopoverContent>
    </Popover>
  )
}
