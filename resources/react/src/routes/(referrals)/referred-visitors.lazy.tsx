import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/(referrals)/referred-visitors')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/(referrals)/referred-visitors"!</div>
}
