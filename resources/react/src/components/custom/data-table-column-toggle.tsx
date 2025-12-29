import { Button } from '@components/ui/button'
import { Checkbox } from '@components/ui/checkbox'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@components/ui/dropdown-menu'
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
import { GripVertical, MoreVertical, RotateCcw } from 'lucide-react'
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
  isDraggable?: boolean
}

function SortableItem({ item, onToggle, disabled, isDraggable = true }: SortableItemProps) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: item.id,
    disabled: !isDraggable,
  })

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.5 : 1,
  }

  const handleRowClick = (e: React.MouseEvent) => {
    // Don't toggle if clicking on the drag handle or if disabled
    if (disabled) return
    const target = e.target as HTMLElement
    if (target.closest('[data-drag-handle]')) return
    onToggle(item.id, !item.isVisible)
  }

  return (
    <div
      ref={setNodeRef}
      style={style}
      className={`flex items-center gap-2 px-2 py-2 hover:bg-accent rounded-sm ${disabled ? 'cursor-not-allowed' : 'cursor-pointer'}`}
      onClick={handleRowClick}
    >
      <div
        data-drag-handle
        {...(isDraggable ? attributes : {})}
        {...(isDraggable ? listeners : {})}
        className={isDraggable ? 'cursor-grab active:cursor-grabbing' : 'cursor-not-allowed opacity-30'}
      >
        <GripVertical className="h-4 w-4 text-muted-foreground" />
      </div>
      <Checkbox
        checked={item.isVisible}
        disabled={disabled}
        onCheckedChange={(checked) => onToggle(item.id, checked as boolean)}
        onClick={(e) => e.stopPropagation()}
      />
      <span
        className={`flex-1 text-sm font-normal capitalize select-none ${disabled ? 'opacity-50' : ''}`}
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

  // Build column items from table on dropdown open (not on every render)
  const [columnOrder, setColumnOrder] = React.useState<ColumnItem[]>([])
  const [isOpen, setIsOpen] = React.useState(false)

  // Build column list when dropdown opens
  const handleOpenChange = React.useCallback(
    (open: boolean) => {
      setIsOpen(open)
      if (open) {
        const columns = table.getAllColumns().filter((column) => column.getCanHide())

        let items: ColumnItem[]
        if (initialColumnOrder && initialColumnOrder.length > 0) {
          // Use provided order
          items = []
          initialColumnOrder.forEach((id) => {
            const column = columns.find((c) => c.id === id)
            if (column) {
              items.push({
                id: column.id,
                label: column.id,
                isVisible: column.getIsVisible(),
              })
            }
          })
          // Add any columns not in the order at the end
          columns.forEach((column) => {
            if (!initialColumnOrder.includes(column.id)) {
              items.push({
                id: column.id,
                label: column.id,
                isVisible: column.getIsVisible(),
              })
            }
          })
        } else {
          // Default order from columns
          items = columns.map((column) => ({
            id: column.id,
            label: column.id,
            isVisible: column.getIsVisible(),
          }))
        }
        setColumnOrder(items)
      }
    },
    [table, initialColumnOrder]
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

        // Update table column order (this will trigger the table's onColumnOrderChange handler)
        table.setColumnOrder(newOrderIds)

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

        // Call persistence callback with updated visibility state
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

    // Reset to default column order
    const defaultOrder = columns.map((column) => ({
      id: column.id,
      label: column.id,
      isVisible: !defaultHiddenColumns.includes(column.id),
    }))
    setColumnOrder(defaultOrder)

    // Reset table column order
    table.setColumnOrder(defaultOrder.map((item) => item.id))

    // Reset visibility - show all except defaultHiddenColumns
    columns.forEach((column) => {
      const shouldBeVisible = !defaultHiddenColumns.includes(column.id)
      column.toggleVisibility(shouldBeVisible)
    })

    // Call reset callback (handles backend reset separately)
    if (onReset) {
      onReset()
    }
  }

  return (
    <DropdownMenu open={isOpen} onOpenChange={handleOpenChange}>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
          <MoreVertical className="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align={isRTL ? 'start' : 'end'} className="w-[240px]">
        <div className="px-1 py-1">
          <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
            <SortableContext items={columnOrder.map((item) => item.id)} strategy={verticalListSortingStrategy}>
              {(() => {
                const visibleCount = columnOrder.filter((item) => item.isVisible).length
                return columnOrder.map((item) => (
                  <SortableItem
                    key={item.id}
                    item={item}
                    onToggle={handleToggle}
                    disabled={item.isVisible && visibleCount === 1}
                    isDraggable={item.isVisible}
                  />
                ))
              })()}
            </SortableContext>
          </DndContext>
          <div className="border-t mt-2 pt-2">
            <Button
              variant="ghost"
              size="sm"
              className="w-full justify-start text-muted-foreground hover:text-foreground"
              onClick={handleReset}
            >
              <RotateCcw className="h-4 w-4 mr-2" />
              {__('Reset to Default', 'wp-statistics')}
            </Button>
          </div>
        </div>
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
