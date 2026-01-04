import { createLazyFileRoute } from '@tanstack/react-router'

import { AccessSettings } from '@/components/settings/tabs/access-settings'

export const Route = createLazyFileRoute('/settings/access')({
  component: AccessSettings,
})
