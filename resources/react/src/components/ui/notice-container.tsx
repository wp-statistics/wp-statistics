import * as React from 'react'
import { useState, useCallback } from 'react'

import { NoticeBanner } from '@/components/ui/notice-banner'
import { WordPress } from '@/lib/wordpress'
import { cn } from '@/lib/utils'

export interface NoticeContainerProps {
  /**
   * Additional CSS classes
   */
  className?: string
}

/**
 * Container component that renders all active notices.
 *
 * Fetches notices from WordPress localized data and handles
 * dismissal via AJAX.
 *
 * @example
 * ```tsx
 * // In your layout component
 * <NoticeContainer className="mb-4" />
 * ```
 */
export function NoticeContainer({ className }: NoticeContainerProps) {
  const wp = WordPress.getInstance()
  const initialNotices = wp.getNoticeItems()

  const [dismissed, setDismissed] = useState<string[]>([])

  const handleDismiss = useCallback(
    async (id: string) => {
      // Optimistically update UI
      setDismissed((prev) => [...prev, id])

      // Send dismissal to server
      try {
        const formData = new FormData()
        formData.append('action', 'wp_statistics_dismiss_notice')
        formData.append('notice_id', id)
        formData.append('_wpnonce', wp.getNoticeDismissNonce())

        await fetch(wp.getNoticeDismissUrl(), {
          method: 'POST',
          body: formData,
        })
      } catch (error) {
        // Revert on error
        setDismissed((prev) => prev.filter((dismissedId) => dismissedId !== id))
        console.error('Failed to dismiss notice:', error)
      }
    },
    [wp]
  )

  // Filter out dismissed notices
  const activeNotices = initialNotices.filter((notice) => !dismissed.includes(notice.id))

  if (!activeNotices.length) {
    return null
  }

  return (
    <div className={cn('flex flex-col gap-3', className)}>
      {activeNotices.map((notice) => (
        <NoticeBanner
          key={notice.id}
          id={notice.id}
          message={notice.message}
          type={notice.type}
          actionUrl={notice.actionUrl}
          actionLabel={notice.actionLabel}
          helpUrl={notice.helpUrl}
          dismissible={notice.dismissible}
          onDismiss={handleDismiss}
        />
      ))}
    </div>
  )
}
