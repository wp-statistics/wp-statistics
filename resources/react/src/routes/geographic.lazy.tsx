import { createLazyFileRoute,Navigate } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/geographic')({
  component: RouteComponent,
})

function RouteComponent() {
  return <Navigate to="/geographic-overview" replace />
}
