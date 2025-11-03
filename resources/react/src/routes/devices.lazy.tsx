import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/devices')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/devices"!</div>
}
