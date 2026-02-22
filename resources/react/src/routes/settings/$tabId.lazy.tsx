import { createLazyFileRoute } from '@tanstack/react-router'

import { SettingsTabRenderer } from '@/components/settings-ui/settings-tab-renderer'

export const Route = createLazyFileRoute('/settings/$tabId')({
  component: SettingsTabPage,
})

function SettingsTabPage() {
  const { tabId } = Route.useParams()
  return <SettingsTabRenderer tabId={tabId} />
}
