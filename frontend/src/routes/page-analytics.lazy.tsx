import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/page-analytics')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/page-analytics"!</div>
}
