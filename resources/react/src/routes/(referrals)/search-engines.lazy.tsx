import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/(referrals)/search-engines')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/(referrals)/search-engines"!</div>
}
