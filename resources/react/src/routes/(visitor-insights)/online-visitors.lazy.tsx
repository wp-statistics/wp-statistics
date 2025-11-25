import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/(visitor-insights)/online-visitors')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/(visitor-insights)/online-visitors"!</div>
}