import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/geographic')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/geographic"!</div>
}
