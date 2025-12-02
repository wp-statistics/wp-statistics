import * as React from 'react'
import type { Table } from '@tanstack/react-table'
import {
  DndContext,
  closestCenter,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
  type DragEndEvent,
} from '@dnd-kit/core'
import {
  arrayMove,
  SortableContext,
  sortableKeyboardCoordinates,
  useSortable,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable'
import { CSS } from '@dnd-kit/utilities'
import { GripVertical, MoreVertical } from 'lucide-react'

import { Button } from '@components/ui/button'
import { Checkbox } from '@components/ui/checkbox'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@components/ui/dropdown-menu'

interface ColumnItem {
  id: string
  label: string
  isVisible: boolean
}

interface SortableItemProps {
  item: ColumnItem
  onToggle: (id: string, checked: boolean) => void
}

function SortableItem({ item, onToggle }: SortableItemProps) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({ id: item.id })

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.5 : 1,
  }

  return (
    <div
      ref={setNodeRef}
      style={style}
      className="flex items-center gap-2 px-2 py-2 hover:bg-accent rounded-sm cursor-default"
    >
      <div {...attributes} {...listeners} className="cursor-grab active:cursor-grabbing">
        <GripVertical className="h-4 w-4 text-muted-foreground" />
      </div>
      <Checkbox
        id={item.id}
        checked={item.isVisible}
        onCheckedChange={(checked) => onToggle(item.id, checked as boolean)}
      />
      <label
        htmlFor={item.id}
        className="flex-1 text-sm font-normal capitalize cursor-pointer select-none"
        onClick={() => onToggle(item.id, !item.isVisible)}
      >
        {item.label}
      </label>
    </div>
  )
}

interface DataTableColumnToggleProps<TData> {
  table: Table<TData>
}

export function DataTableColumnToggle<TData>({ table }: DataTableColumnToggleProps<TData>) {
  const columns = table.getAllColumns().filter((column) => column.getCanHide())
  const isRTL = document.dir === 'rtl' || document.documentElement.dir === 'rtl'

  const [columnOrder, setColumnOrder] = React.useState<ColumnItem[]>(
    columns.map((column) => ({
      id: column.id,
      label: column.id,
      isVisible: column.getIsVisible(),
    }))
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

        // Update table column order
        table.setColumnOrder(newOrder.map((item) => item.id))

        return newOrder
      })
    }
  }

  const handleToggle = (id: string, checked: boolean) => {
    const column = table.getColumn(id)
    if (column) {
      column.toggleVisibility(checked)
      setColumnOrder((items) => items.map((item) => (item.id === id ? { ...item, isVisible: checked } : item)))
    }
  }

  // Sync visibility state when columns change
  React.useEffect(() => {
    setColumnOrder((items) =>
      items.map((item) => {
        const column = table.getColumn(item.id)
        return column ? { ...item, isVisible: column.getIsVisible() } : item
      })
    )
  }, [table])

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
          <MoreVertical className="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align={isRTL ? 'start' : 'end'} className="w-[240px]">
        <div className="px-1 py-1">
          <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
            <SortableContext items={columnOrder.map((item) => item.id)} strategy={verticalListSortingStrategy}>
              {columnOrder.map((item) => (
                <SortableItem key={item.id} item={item} onToggle={handleToggle} />
              ))}
            </SortableContext>
          </DndContext>
        </div>
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
