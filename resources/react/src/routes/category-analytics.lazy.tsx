import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/category-analytics')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/category-analytics"!</div>
}
