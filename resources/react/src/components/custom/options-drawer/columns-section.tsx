import { ColumnsIcon, GripVertical, RotateCcw } from 'lucide-react'
import { __ } from '@wordpress/i18n'
import { useEffect, useState } from 'react'
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

import { cn } from '@/lib/utils'

import { useOptionsDrawer, OptionsMenuItem } from './options-drawer'

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
      className={cn(
        'flex items-center gap-3 px-3 py-2.5',
        'hover:bg-neutral-50 rounded-md transition-colors',
        'border-b border-neutral-100 last:border-b-0',
        disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'
      )}
      onClick={handleRowClick}
    >
      <div
        data-drag-handle
        {...attributes}
        {...listeners}
        className="cursor-grab active:cursor-grabbing text-neutral-300 hover:text-neutral-400 transition-colors"
      >
        <GripVertical className="h-4 w-4" />
      </div>

      {/* Custom checkbox */}
      <div
        role="checkbox"
        aria-checked={item.isVisible}
        className={cn(
          'w-4 h-4 rounded border-2 flex items-center justify-center transition-colors shrink-0 cursor-pointer',
          item.isVisible
            ? 'bg-primary border-primary'
            : 'bg-white border-neutral-300'
        )}
        onClick={(e) => {
          e.stopPropagation()
          if (!disabled) onToggle(item.id, !item.isVisible)
        }}
      >
        {item.isVisible && (
          <svg className="w-3 h-3 text-white" viewBox="0 0 12 12" fill="none">
            <path
              d="M2.5 6L5 8.5L9.5 3.5"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            />
          </svg>
        )}
      </div>

      <span
        className={cn(
          'flex-1 text-sm select-none',
          item.isVisible ? 'text-neutral-700' : 'text-neutral-400'
        )}
      >
        {item.label}
      </span>
    </div>
  )
}

interface ColumnsSectionProps<TData> {
  table: Table<TData>
  initialColumnOrder?: string[]
  defaultHiddenColumns?: string[]
  onColumnVisibilityChange?: (visibility: VisibilityState) => void
  onColumnOrderChange?: (order: string[]) => void
  onReset?: () => void
}

// Helper to get display label from column
const getColumnLabel = (
  column: { id: string; columnDef: { meta?: { title?: string; mobileLabel?: string } } }
): string => {
  const meta = column.columnDef.meta as { title?: string; mobileLabel?: string } | undefined
  return meta?.title || meta?.mobileLabel || column.id
}

export function ColumnsMenuEntry<TData>({ table }: { table: Table<TData> }) {
  const { currentView, setCurrentView } = useOptionsDrawer()

  const columns = table.getAllColumns().filter((column) => column.getCanHide())
  const hiddenCount = columns.filter((column) => !column.getIsVisible()).length

  if (currentView !== 'main' || columns.length === 0) {
    return null
  }

  const summary = hiddenCount > 0 ? `${hiddenCount} ${__('hidden', 'wp-statistics')}` : undefined

  return (
    <OptionsMenuItem
      icon={<ColumnsIcon className="h-4 w-4" />}
      title={__('Show/hide columns', 'wp-statistics')}
      summary={summary}
      onClick={() => setCurrentView('columns')}
    />
  )
}

export function ColumnsDetailView<TData>({
  table,
  initialColumnOrder,
  defaultHiddenColumns = [],
  onColumnVisibilityChange,
  onColumnOrderChange,
  onReset,
}: ColumnsSectionProps<TData>) {
  const { currentView } = useOptionsDrawer()
  const [columnOrder, setColumnOrder] = useState<ColumnItem[]>([])

  // Build column list on mount
  useEffect(() => {
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
  }, [table, initialColumnOrder])

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

  if (currentView !== 'columns' || columnOrder.length === 0) {
    return null
  }

  const visibleCount = columnOrder.filter((item) => item.isVisible).length

  return (
    <div className="flex flex-col h-full">
      {/* Description */}
      <p className="text-xs text-neutral-500 px-4 py-3 border-b border-neutral-100 bg-neutral-50/30">
        {__('Drag to reorder, toggle to show/hide columns', 'wp-statistics')}
      </p>

      {/* Column list */}
      <div className="flex-1 overflow-y-auto px-4 py-2">
        <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
          <SortableContext items={columnOrder.map((item) => item.id)} strategy={verticalListSortingStrategy}>
            {columnOrder.map((item) => (
              <SortableItem
                key={item.id}
                item={item}
                onToggle={handleToggle}
                disabled={visibleCount === 1 && item.isVisible}
              />
            ))}
          </SortableContext>
        </DndContext>
      </div>

      {/* Footer */}
      <div className="flex items-center justify-end px-4 py-3 border-t border-neutral-100 bg-neutral-50/50 shrink-0">
        <button
          type="button"
          onClick={handleReset}
          className="flex items-center gap-1.5 text-xs text-neutral-500 hover:text-neutral-700 transition-colors cursor-pointer"
        >
          <RotateCcw className="h-3.5 w-3.5" />
          {__('Reset to default', 'wp-statistics')}
        </button>
      </div>
    </div>
  )
}

// Legacy export for backwards compatibility
export function ColumnsSection<TData>(props: ColumnsSectionProps<TData>) {
  return (
    <>
      <ColumnsMenuEntry table={props.table} />
      <ColumnsDetailView {...props} />
    </>
  )
}
