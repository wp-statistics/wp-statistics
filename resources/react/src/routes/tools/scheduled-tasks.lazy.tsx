import { createLazyFileRoute } from '@tanstack/react-router'

import { ScheduledTasksPage } from '@/components/tools/tabs/scheduled-tasks-page'

export const Route = createLazyFileRoute('/tools/scheduled-tasks')({
  component: ScheduledTasksPage,
})
