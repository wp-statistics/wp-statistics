import { Plus } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { ScrollArea } from '@/components/ui/scroll-area'

import { availableBlockTypes, blockDefinitions } from './block-definitions'
import type { EmailBlockType } from './types'

interface BlockListProps {
  onAddBlock: (type: EmailBlockType) => void
  disabledBlocks?: EmailBlockType[]
}

export function BlockList({ onAddBlock, disabledBlocks = [] }: BlockListProps) {
  return (
    <ScrollArea className="h-full">
      <div className="space-y-1 p-2">
        <h4 className="mb-2 px-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
          Available Blocks
        </h4>
        {availableBlockTypes.map((type) => {
          const definition = blockDefinitions[type]
          const isDisabled = disabledBlocks.includes(type)

          return (
            <Button
              key={type}
              variant="ghost"
              className="w-full justify-start gap-2 px-2"
              disabled={isDisabled}
              onClick={() => onAddBlock(type)}
            >
              <div className="flex h-7 w-7 items-center justify-center rounded-md bg-muted">
                {definition.icon}
              </div>
              <div className="flex-1 text-left">
                <p className="text-sm font-medium">{definition.label}</p>
              </div>
              <Plus className="h-4 w-4 text-muted-foreground" />
            </Button>
          )
        })}
      </div>
    </ScrollArea>
  )
}
