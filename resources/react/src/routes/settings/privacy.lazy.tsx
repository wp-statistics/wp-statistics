import { createLazyFileRoute } from '@tanstack/react-router'

import { PrivacySettings } from '@/components/settings/tabs/privacy-settings'

export const Route = createLazyFileRoute('/settings/privacy')({
  component: PrivacySettings,
})
