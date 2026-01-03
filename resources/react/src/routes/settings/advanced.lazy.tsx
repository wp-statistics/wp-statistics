import { createLazyFileRoute } from '@tanstack/react-router'

import { AdvancedSettings } from '@/components/settings/tabs/advanced-settings'

export const Route = createLazyFileRoute('/settings/advanced')({
  component: AdvancedSettings,
})
