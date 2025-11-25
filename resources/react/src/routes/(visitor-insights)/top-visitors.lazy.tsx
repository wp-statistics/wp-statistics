import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/(visitor-insights)/top-visitors')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/(visitor-insights)/top-visitors"!</div>
}