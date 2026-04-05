import { __ } from '@wordpress/i18n'
import { BellOff, ExternalLink, X } from 'lucide-react'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { Button } from '@/components/ui/button'
import {
  Sheet,
  SheetClose,
  SheetContent,
  SheetHeader,
  SheetTitle,
} from '@/components/ui/sheet'
import { WordPress } from '@/lib/wordpress'
import {
  dismissAllNotifications,
  dismissNotification,
  markNotificationsViewed,
} from '@/services/notifications'

function timeAgo(dateStr: string): string {
  const seconds = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000)

  if (seconds < 60) return __('just now', 'wp-statistics')

  const minutes = Math.floor(seconds / 60)
  if (minutes < 60) return minutes === 1 ? __('1 minute ago', 'wp-statistics') : `${minutes} ${__('minutes ago', 'wp-statistics')}`

  const hours = Math.floor(minutes / 60)
  if (hours < 24) return hours === 1 ? __('1 hour ago', 'wp-statistics') : `${hours} ${__('hours ago', 'wp-statistics')}`

  const days = Math.floor(hours / 24)
  if (days < 30) return days === 1 ? __('1 day ago', 'wp-statistics') : `${days} ${__('days ago', 'wp-statistics')}`

  const months = Math.floor(days / 30)
  return months === 1 ? __('1 month ago', 'wp-statistics') : `${months} ${__('months ago', 'wp-statistics')}`
}

const TYPE_STYLES: Record<string, string> = {
  info: 'border-l-blue-500',
  warning: 'border-l-amber-500',
  success: 'border-l-emerald-500',
  update: 'border-l-violet-500',
  danger: 'border-l-red-500',
}

interface NotificationDrawerProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  onUnreadCountChange?: (count: number) => void
}

type Tab = 'inbox' | 'dismissed'

export function NotificationDrawer({ open, onOpenChange, onUnreadCountChange }: NotificationDrawerProps) {
  const wp = WordPress.getInstance()
  const allItems = wp.getNotificationItems()
  const initialDismissedIds = wp.getNotificationDismissedIds()

  const [dismissedIds, setDismissedIds] = useState<number[]>(initialDismissedIds)
  const [activeTab, setActiveTab] = useState<Tab>('inbox')
  const markedViewedIdsRef = useRef<Set<number>>(new Set())

  const dismissedSet = useMemo(() => new Set(dismissedIds), [dismissedIds])
  const inboxItems = useMemo(() => allItems.filter((item) => !dismissedSet.has(item.id)), [allItems, dismissedSet])
  const dismissedItems = useMemo(() => allItems.filter((item) => dismissedSet.has(item.id)), [allItems, dismissedSet])
  const visibleItems = activeTab === 'inbox' ? inboxItems : dismissedItems

  useEffect(() => {
    if (!open) {
      setActiveTab('inbox')
      return
    }

    if (inboxItems.length === 0) return

    const newIds = inboxItems.map((i) => i.id).filter((id) => !markedViewedIdsRef.current.has(id))
    if (newIds.length === 0) return

    newIds.forEach((id) => markedViewedIdsRef.current.add(id))
    markNotificationsViewed(newIds).catch(() => {})
    onUnreadCountChange?.(0)
  }, [open]) // eslint-disable-line react-hooks/exhaustive-deps

  const handleDismiss = useCallback(
    (id: number) => {
      setDismissedIds((prev) => [...prev, id])
      dismissNotification(id).catch(() => {
        setDismissedIds((prev) => prev.filter((d) => d !== id))
      })
    },
    []
  )

  const handleDismissAll = useCallback(() => {
    const ids = inboxItems.map((item) => item.id)
    setDismissedIds((prev) => [...prev, ...ids])
    dismissAllNotifications(ids).catch(() => {
      setDismissedIds((prev) => prev.filter((id) => !ids.includes(id)))
    })
  }, [inboxItems])

  return (
    <Sheet open={open} onOpenChange={onOpenChange}>
      <SheetContent className="flex flex-col sm:max-w-sm [&>[data-slot=sheet-close]]:hidden">
        <SheetHeader className="flex-row items-center justify-between gap-2 space-y-0">
          <SheetTitle>{__('Notifications', 'wp-statistics')}</SheetTitle>
          <SheetClose asChild>
            <button className="rounded-xs text-muted-foreground hover:text-foreground transition-opacity">
              <X className="size-4" />
              <span className="sr-only">{__('Close', 'wp-statistics')}</span>
            </button>
          </SheetClose>
        </SheetHeader>

        {/* Tabs */}
        <div className="flex items-center gap-1 px-4 border-b">
          <button
            className={`px-3 py-2 text-sm font-medium border-b-2 transition-colors ${
              activeTab === 'inbox'
                ? 'border-primary text-foreground'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            }`}
            onClick={() => setActiveTab('inbox')}
          >
            {__('Inbox', 'wp-statistics')}
            {inboxItems.length > 0 && (
              <span className="ml-1.5 text-xs text-muted-foreground">({inboxItems.length})</span>
            )}
          </button>
          <button
            className={`px-3 py-2 text-sm font-medium border-b-2 transition-colors ${
              activeTab === 'dismissed'
                ? 'border-primary text-foreground'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            }`}
            onClick={() => setActiveTab('dismissed')}
          >
            {__('Dismissed', 'wp-statistics')}
            {dismissedItems.length > 0 && (
              <span className="ml-1.5 text-xs text-muted-foreground">({dismissedItems.length})</span>
            )}
          </button>

          {activeTab === 'inbox' && inboxItems.length > 1 && (
            <Button
              variant="ghost"
              size="sm"
              className="ml-auto text-xs text-muted-foreground"
              onClick={handleDismissAll}
            >
              {__('Dismiss All', 'wp-statistics')}
            </Button>
          )}
        </div>

        {/* Notification list */}
        <div className="flex-1 overflow-y-auto px-4">
          {visibleItems.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-16 text-muted-foreground">
              <BellOff className="h-10 w-10 mb-3 opacity-40" />
              <p className="text-sm">
                {activeTab === 'inbox'
                  ? __('No notifications', 'wp-statistics')
                  : __('No dismissed notifications', 'wp-statistics')}
              </p>
            </div>
          ) : (
            <div className="flex flex-col gap-2 pt-3">
              {visibleItems.map((item) => (
                <div
                  key={item.id}
                  className={`group relative rounded-md border border-l-[3px] bg-card p-3 ${TYPE_STYLES[item.background_color ?? 'info'] ?? TYPE_STYLES.info}`}
                >
                  {activeTab === 'inbox' && (
                    <button
                      className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity text-muted-foreground hover:text-foreground"
                      onClick={() => handleDismiss(item.id)}
                      aria-label={__('Dismiss', 'wp-statistics')}
                    >
                      <X className="h-3.5 w-3.5" />
                    </button>
                  )}

                  <h4 className="text-sm font-medium pr-5 text-foreground">{item.title}</h4>
                  <p className="text-xs text-muted-foreground mt-1 leading-relaxed">{item.description}</p>

                  {(item.primary_button || item.secondary_button) && (
                    <div className="flex items-center gap-2 mt-2.5">
                      {item.primary_button && (
                        <a
                          href={item.primary_button.url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
                        >
                          {item.primary_button.title}
                          <ExternalLink className="h-3 w-3" />
                        </a>
                      )}
                      {item.secondary_button && (
                        <a
                          href={item.secondary_button.url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground hover:underline"
                        >
                          {item.secondary_button.title}
                        </a>
                      )}
                    </div>
                  )}

                  <span className="text-[11px] text-muted-foreground/70 mt-2 block">{timeAgo(item.activated_at)}</span>
                </div>
              ))}
            </div>
          )}
        </div>
      </SheetContent>
    </Sheet>
  )
}
