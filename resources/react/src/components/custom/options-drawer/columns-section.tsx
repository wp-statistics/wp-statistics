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
import { ColumnsIcon, GripVertical, RotateCcw } from 'lucide-react'
import { useEffect, useState } from 'react'

import { cn } from '@/lib/utils'

import { OptionsMenuItem,useOptionsDrawer } from './options-drawer'

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
      className={cn(
        'flex items-center gap-3 px-3 py-2.5',
        'hover:bg-neutral-50 rounded-md transition-colors',
        'border-b border-neutral-100 last:border-b-0',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-1',
        disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'
      )}
      onClick={handleRowClick}
      onKeyDown={handleKeyDown}
    >
      <div
        data-drag-handle
        {...attributes}
        {...listeners}
        className="cursor-grab active:cursor-grabbing text-neutral-300 hover:text-neutral-400 transition-colors"
      >
        <GripVertical className="h-4 w-4" />
      </div>

      {/* Visibility checkbox */}
      <div
        role="checkbox"
        aria-checked={item.isVisible}
        aria-label={`${__('Toggle visibility for', 'wp-statistics')} ${item.label}`}
        tabIndex={disabled ? -1 : 0}
        className={cn(
          'w-4 h-4 rounded border-2 flex items-center justify-center transition-colors shrink-0 cursor-pointer',
          'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-1',
          item.isVisible
            ? 'bg-primary border-primary'
            : 'bg-white border-neutral-300'
        )}
        onClick={(e) => {
          e.stopPropagation()
          if (!disabled) onToggle(item.id, !item.isVisible)
        }}
        onKeyDown={(e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault()
            e.stopPropagation()
            if (!disabled) onToggle(item.id, !item.isVisible)
          }
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
  table: Table<TData> | null
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

// Helper to get display label from column
const getColumnLabel = (
  column: { id: string; columnDef: { meta?: { title?: string; mobileLabel?: string } } }
): string => {
  const meta = column.columnDef.meta as { title?: string; mobileLabel?: string } | undefined
  return meta?.title || meta?.mobileLabel || column.id
}

interface ColumnsMenuEntryProps<TData> {
  table: Table<TData> | null
  defaultHiddenColumns?: string[]
  /** Persisted visibility state from useDataTablePreferences - used for reliable change detection after navigation */
  initialColumnVisibility?: VisibilityState
}

export function ColumnsMenuEntry<TData>({
  table,
  defaultHiddenColumns = [],
  initialColumnVisibility,
}: ColumnsMenuEntryProps<TData>) {
  const { currentView, setCurrentView } = useOptionsDrawer()

  // Handle null table (before DataTable renders)
  if (!table) {
    return null
  }

  const columns = table.getAllColumns().filter((column) => column.getCanHide())

  if (currentView !== 'main' || columns.length === 0) {
    return null
  }

  // Use persisted visibility if available (reliable after navigation), otherwise fall back to table state
  const hiddenColumnIds =
    initialColumnVisibility && Object.keys(initialColumnVisibility).length > 0
      ? Object.entries(initialColumnVisibility)
          .filter(([_, visible]) => !visible)
          .map(([id]) => id)
      : columns.filter((column) => !column.getIsVisible()).map((col) => col.id)

  // Only show "x hidden" if it differs from default
  const hasNonDefaultHiddenColumns =
    hiddenColumnIds.length !== defaultHiddenColumns.length ||
    hiddenColumnIds.some((id) => !defaultHiddenColumns.includes(id)) ||
    defaultHiddenColumns.some((id) => !hiddenColumnIds.includes(id))

  const summary = hasNonDefaultHiddenColumns
    ? `${hiddenColumnIds.length} ${__('hidden', 'wp-statistics')}`
    : undefined

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
  comparableColumns = [],
  comparisonColumns = [],
  defaultComparisonColumns = [],
  onColumnVisibilityChange,
  onColumnOrderChange,
  onComparisonColumnsChange,
  onReset,
}: ColumnsSectionProps<TData>) {
  const { currentView } = useOptionsDrawer()
  const [columnOrder, setColumnOrder] = useState<ColumnItem[]>([])

  // Build column list on mount or when table becomes available
  // Uses initialColumnOrder for stable ordering (definition order), not table state
  useEffect(() => {
    // Handle null table (before DataTable renders)
    if (!table) return

    const columns = table.getAllColumns().filter((column) => column.getCanHide())

    // Use initialColumnOrder for stable, predictable ordering
    // This ensures hidden columns stay in their definition position, not pushed to the end
    const baseOrder = initialColumnOrder || []

    const sortedColumns =
      baseOrder.length > 0
        ? [...columns].sort((a, b) => {
            const aIndex = baseOrder.indexOf(a.id)
            const bIndex = baseOrder.indexOf(b.id)
            // Columns not in baseOrder go to end (shouldn't happen normally)
            const aPos = aIndex === -1 ? baseOrder.length : aIndex
            const bPos = bIndex === -1 ? baseOrder.length : bIndex
            return aPos - bPos
          })
        : columns

    const items = sortedColumns.map((column) => ({
      id: column.id,
      label: getColumnLabel(column),
      isVisible: column.getIsVisible(),
      isComparable: comparableColumns.includes(column.id),
      showComparison: comparisonColumns.includes(column.id),
    }))

    setColumnOrder(items)
  }, [table, initialColumnOrder, comparableColumns, comparisonColumns])

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

  if (currentView !== 'columns' || columnOrder.length === 0 || !table) {
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
                onComparisonToggle={comparableColumns.length > 0 ? handleComparisonToggle : undefined}
                disabled={visibleCount === 1 && item.isVisible}
              />
            ))}
          </SortableContext>
        </DndContext>
      </div>

      {/* Footer */}
      <div className="flex items-center justify-end px-4 py-3 shrink-0">
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
