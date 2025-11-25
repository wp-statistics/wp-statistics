import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/(visitor-insights)/views')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/(visitor-insights)/views"!</div>
}