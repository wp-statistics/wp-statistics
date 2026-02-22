import * as React from 'react'
import { useCallback,useState } from 'react'

import { NoticeBanner } from '@/components/ui/notice-banner'
import { cn } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'

export interface NoticeContainerProps {
  /**
   * Additional CSS classes
   */
  className?: string
  /**
   * Current route/page slug for filtering page-specific notices.
   * If not provided, only global notices (with empty pages array) will be shown.
   */
  currentRoute?: string
}

/**
 * Container component that renders all active notices.
 *
 * Fetches notices from WordPress localized data and handles
 * dismissal via AJAX. Supports page-specific notices via the
 * `currentRoute` prop.
 *
 * @example
 * ```tsx
 * // Show all notices for this page
 * <NoticeContainer className="mb-4" currentRoute="geographic" />
 *
 * // Show only global notices
 * <NoticeContainer className="mb-4" />
 * ```
 */
export function NoticeContainer({ className, currentRoute }: NoticeContainerProps) {
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

  // Filter notices based on dismissal status and page targeting
  const activeNotices = initialNotices.filter((notice) => {
    // Skip dismissed notices
    if (dismissed.includes(notice.id)) return false

    // If notice has no pages specified (empty array or undefined), show on all pages
    if (!notice.pages || notice.pages.length === 0) return true

    // Otherwise, check if current route matches one of the notice's target pages
    return currentRoute && notice.pages.includes(currentRoute)
  })

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
