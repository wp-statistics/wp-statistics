import { createFileRoute, Outlet } from '@tanstack/react-router'

import { ToolsLayout } from '@/components/tools/tools-layout'

export const Route = createFileRoute('/tools')({
  component: ToolsPage,
})

function ToolsPage() {
  return (
    <ToolsLayout>
      <Outlet />
    </ToolsLayout>
  )
}
