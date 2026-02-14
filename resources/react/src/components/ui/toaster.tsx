import { __ } from '@wordpress/i18n'
import { XCircle } from 'lucide-react'
import * as React from 'react'

import type { Toast } from '@/hooks/use-toast'
import { useToast } from '@/hooks/use-toast'
import { cn } from '@/lib/utils'

function ToastItem({ toast, onDismiss }: { toast: Toast; onDismiss: (id: string) => void }) {
  return (
    <div
      className={cn(
        'pointer-events-auto relative flex w-full items-start gap-3 overflow-hidden rounded-lg border p-4 shadow-lg transition-all',
        toast.variant === 'destructive'
          ? 'border-destructive/50 bg-red-50 dark:bg-red-950'
          : 'border-border bg-white dark:bg-zinc-900'
      )}
    >
      <div className="flex-1 space-y-1">
        {toast.title && <p className="text-sm font-semibold leading-none">{toast.title}</p>}
        {toast.description && <p className="text-sm text-muted-foreground">{toast.description}</p>}
      </div>
      <button
        onClick={() => onDismiss(toast.id)}
        className="shrink-0 rounded-sm opacity-70 hover:opacity-100"
        aria-label={__('Dismiss', 'wp-statistics')}
      >
        <XCircle className="h-4 w-4" />
      </button>
    </div>
  )
}

export function Toaster() {
  const { toasts, dismiss } = useToast()

  if (toasts.length === 0) return null

  return (
    <div className="fixed bottom-4 right-4 z-[99999] flex flex-col gap-2 w-[360px] max-w-[calc(100vw-2rem)]">
      {toasts.map((t) => (
        <ToastItem key={t.id} toast={t} onDismiss={dismiss} />
      ))}
    </div>
  )
}
