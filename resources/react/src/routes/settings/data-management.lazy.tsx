import { createLazyFileRoute } from '@tanstack/react-router'

import { DataManagementSettings } from '@/components/settings/tabs/data-management-settings'

export const Route = createLazyFileRoute('/settings/data-management')({
  component: DataManagementSettings,
})
