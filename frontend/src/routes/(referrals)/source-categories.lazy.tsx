import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/(referrals)/source-categories')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/(referrals)/source-categories"!</div>
}
