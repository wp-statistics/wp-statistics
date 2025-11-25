import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/(visitor-insights)/logged-in-users')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/(visitor-insights)/logged-in-users"!</div>
}
