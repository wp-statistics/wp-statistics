import { createLazyFileRoute } from '@tanstack/react-router'

import { ExclusionSettings } from '@/components/settings/tabs/exclusion-settings'

export const Route = createLazyFileRoute('/settings/exclusions')({
  component: ExclusionSettings,
})
