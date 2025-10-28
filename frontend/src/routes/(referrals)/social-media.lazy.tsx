import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/(referrals)/social-media')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/(referrals)/social-media"!</div>
}
