import { createLazyFileRoute } from '@tanstack/react-router'

import { SettingsTabRenderer } from '@/components/settings-ui/settings-tab-renderer'

export const Route = createLazyFileRoute('/tools/$tabId')({
  component: ToolsTabPage,
})

function ToolsTabPage() {
  const { tabId } = Route.useParams()
  return <SettingsTabRenderer tabId={tabId} />
}
