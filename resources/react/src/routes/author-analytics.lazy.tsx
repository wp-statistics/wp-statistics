import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/author-analytics')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/author-analytics"!</div>
}
