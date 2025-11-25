import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/(visitor-insights)/search-terms')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/(visitor-insights)/search-terms"!</div>
}
