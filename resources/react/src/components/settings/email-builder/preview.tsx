import { Eye, Loader2, RefreshCw } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { ScrollArea } from '@/components/ui/scroll-area'

interface PreviewProps {
  html: string | null
  isLoading: boolean
  onRefresh: () => void
}

export function Preview({ html, isLoading, onRefresh }: PreviewProps) {
  if (isLoading) {
    return (
      <div className="flex h-full items-center justify-center">
        <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
      </div>
    )
  }

  if (!html) {
    return (
      <div className="flex h-full flex-col items-center justify-center text-center">
        <Eye className="h-10 w-10 text-muted-foreground/50" />
        <h3 className="mt-4 text-sm font-medium">No preview available</h3>
        <p className="mt-2 text-xs text-muted-foreground">
          Add blocks to see the email preview.
        </p>
      </div>
    )
  }

  return (
    <div className="flex h-full flex-col">
      <div className="flex items-center justify-between border-b px-3 py-2">
        <span className="text-xs font-medium text-muted-foreground">Email Preview</span>
        <Button
          variant="ghost"
          size="sm"
          className="h-7 gap-1 text-xs"
          onClick={onRefresh}
        >
          <RefreshCw className="h-3 w-3" />
          Refresh
        </Button>
      </div>
      <ScrollArea className="flex-1">
        <div className="bg-gray-100 p-4">
          <div
            className="mx-auto max-w-[600px] overflow-hidden rounded-lg bg-white shadow-sm"
            dangerouslySetInnerHTML={{ __html: html }}
          />
        </div>
      </ScrollArea>
    </div>
  )
}
