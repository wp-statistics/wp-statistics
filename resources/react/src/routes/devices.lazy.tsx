import { createLazyFileRoute,Navigate } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/devices')({
  component: RouteComponent,
})

function RouteComponent() {
  return <Navigate to="/devices-overview" replace />
}
