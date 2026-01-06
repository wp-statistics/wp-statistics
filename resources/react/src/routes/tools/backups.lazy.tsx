import { createLazyFileRoute } from '@tanstack/react-router'

import { BackupsPage } from '@/components/tools/tabs/backups-page'

export const Route = createLazyFileRoute('/tools/backups')({
  component: BackupsPage,
})
