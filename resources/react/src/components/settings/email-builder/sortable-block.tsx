import { useSortable } from '@dnd-kit/sortable'
import { CSS } from '@dnd-kit/utilities'
import { GripVertical, Settings2, Trash2 } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

import { blockDefinitions } from './block-definitions'
import type { EmailBlock } from './types'

interface SortableBlockProps {
  block: EmailBlock
  onRemove: (id: string) => void
  onSettings?: (id: string) => void
}

export function SortableBlock({ block, onRemove, onSettings }: SortableBlockProps) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: block.id })

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  }

  const definition = blockDefinitions[block.type]

  return (
    <div
      ref={setNodeRef}
      style={style}
      className={cn(
        'flex items-center gap-2 rounded-lg border bg-background p-3',
        isDragging && 'opacity-50 shadow-lg'
      )}
    >
      <button
        className="cursor-grab touch-none text-muted-foreground hover:text-foreground"
        {...attributes}
        {...listeners}
      >
        <GripVertical className="h-4 w-4" />
      </button>

      <div className="flex flex-1 items-center gap-2">
        <div className="flex h-8 w-8 items-center justify-center rounded-md bg-muted">
          {definition.icon}
        </div>
        <div className="flex-1">
          <p className="text-sm font-medium">{definition.label}</p>
          <p className="text-xs text-muted-foreground">{definition.description}</p>
        </div>
      </div>

      <div className="flex items-center gap-1">
        {onSettings && (
          <Button
            variant="ghost"
            size="icon"
            className="h-7 w-7"
            onClick={() => onSettings(block.id)}
          >
            <Settings2 className="h-3.5 w-3.5" />
          </Button>
        )}
        <Button
          variant="ghost"
          size="icon"
          className="h-7 w-7 text-destructive hover:text-destructive"
          onClick={() => onRemove(block.id)}
        >
          <Trash2 className="h-3.5 w-3.5" />
        </Button>
      </div>
    </div>
  )
}
