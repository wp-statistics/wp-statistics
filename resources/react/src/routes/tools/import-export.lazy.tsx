import { createLazyFileRoute } from '@tanstack/react-router'

import { ImportExportPage } from '@/components/tools/tabs/import-export-page'

export const Route = createLazyFileRoute('/tools/import-export')({
  component: ImportExportPage,
})
