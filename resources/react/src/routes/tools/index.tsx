import { createFileRoute, Navigate } from '@tanstack/react-router'

export const Route = createFileRoute('/tools/')({
  component: () => <Navigate to="/tools/system-info" replace />,
})
