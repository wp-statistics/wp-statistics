import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/(referrals)/referrers')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/(referrals)/referrers"!</div>
}
