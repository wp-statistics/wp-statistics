import { createLazyFileRoute } from '@tanstack/react-router'

import { DiagnosticsPage } from '@/components/tools/tabs/diagnostics-page'

export const Route = createLazyFileRoute('/tools/diagnostics')({
  component: DiagnosticsPage,
})
