import { createLazyFileRoute } from '@tanstack/react-router'

import { BackgroundJobsPage } from '@/components/tools/tabs/background-jobs-page'

export const Route = createLazyFileRoute('/tools/background-jobs')({
  component: BackgroundJobsPage,
})
