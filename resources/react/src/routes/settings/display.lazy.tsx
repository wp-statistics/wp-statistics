import { createLazyFileRoute } from '@tanstack/react-router'

import { DisplaySettings } from '@/components/settings/tabs/display-settings'

export const Route = createLazyFileRoute('/settings/display')({
  component: DisplaySettings,
})
