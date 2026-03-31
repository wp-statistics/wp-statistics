import { WordPress } from '@/lib/wordpress'

async function notificationAjax(action: string, payload: Record<string, string | string[]>): Promise<void> {
  const wp = WordPress.getInstance()
  const formData = new FormData()
  formData.append('action', action)

  for (const [key, val] of Object.entries(payload)) {
    if (Array.isArray(val)) {
      val.forEach((v) => formData.append(key, v))
    } else {
      formData.append(key, val)
    }
  }

  formData.append('_wpnonce', wp.getNotificationNonce())
  await fetch(wp.getAjaxUrl(), { method: 'POST', body: formData })
}

export function dismissNotification(id: number): Promise<void> {
  return notificationAjax('wp_statistics_dismiss_notification', { notification_id: String(id) })
}

export function dismissAllNotifications(ids: number[]): Promise<void> {
  return notificationAjax('wp_statistics_dismiss_all_notifications', { 'ids[]': ids.map(String) })
}

export function markNotificationsViewed(ids: number[]): Promise<void> {
  if (!ids.length) return Promise.resolve()
  return notificationAjax('wp_statistics_mark_notifications_viewed', { 'ids[]': ids.map(String) })
}
