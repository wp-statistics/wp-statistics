import { createLazyFileRoute } from '@tanstack/react-router'

import { GeneralSettings } from '@/components/settings/tabs/general-settings'

export const Route = createLazyFileRoute('/settings/general')({
  component: GeneralSettings,
})
