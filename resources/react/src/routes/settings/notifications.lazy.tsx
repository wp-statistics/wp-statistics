import { createLazyFileRoute } from '@tanstack/react-router'

import { NotificationSettings } from '@/components/settings/tabs/notification-settings'

export const Route = createLazyFileRoute('/settings/notifications')({
  component: NotificationSettings,
})
