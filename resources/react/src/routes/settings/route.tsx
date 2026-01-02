import { createFileRoute, Outlet } from '@tanstack/react-router'

import { SettingsLayout } from '@/components/settings/settings-layout'

export const Route = createFileRoute('/settings')({
  component: SettingsPage,
})

function SettingsPage() {
  return (
    <SettingsLayout>
      <Outlet />
    </SettingsLayout>
  )
}
