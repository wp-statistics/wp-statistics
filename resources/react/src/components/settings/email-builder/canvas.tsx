import {
  closestCenter,
  DndContext,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
} from '@dnd-kit/core'
import type { DragEndEvent } from '@dnd-kit/core'
import {
  SortableContext,
  sortableKeyboardCoordinates,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable'
import { Package } from 'lucide-react'

import { SortableBlock } from './sortable-block'
import type { EmailBlock } from './types'

interface CanvasProps {
  blocks: EmailBlock[]
  onReorder: (activeId: string, overId: string) => void
  onRemove: (id: string) => void
  onSettings?: (id: string) => void
}

export function Canvas({ blocks, onReorder, onRemove, onSettings }: CanvasProps) {
  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    })
  )

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event

    if (over && active.id !== over.id) {
      onReorder(active.id as string, over.id as string)
    }
  }

  if (blocks.length === 0) {
    return (
      <div className="flex h-full flex-col items-center justify-center rounded-lg border-2 border-dashed p-8 text-center">
        <Package className="h-10 w-10 text-muted-foreground/50" />
        <h3 className="mt-4 text-sm font-medium">No blocks added</h3>
        <p className="mt-2 text-xs text-muted-foreground">
          Click on blocks from the sidebar to add them to your email template.
        </p>
      </div>
    )
  }

  return (
    <DndContext
      sensors={sensors}
      collisionDetection={closestCenter}
      onDragEnd={handleDragEnd}
    >
      <SortableContext items={blocks.map((b) => b.id)} strategy={verticalListSortingStrategy}>
        <div className="space-y-2">
          {blocks.map((block) => (
            <SortableBlock
              key={block.id}
              block={block}
              onRemove={onRemove}
              onSettings={onSettings}
            />
          ))}
        </div>
      </SortableContext>
    </DndContext>
  )
}
