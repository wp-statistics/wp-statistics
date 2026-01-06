import { createLazyFileRoute } from '@tanstack/react-router'

import { SystemInfoPage } from '@/components/tools/tabs/system-info-page'

export const Route = createLazyFileRoute('/tools/system-info')({
  component: SystemInfoPage,
})
