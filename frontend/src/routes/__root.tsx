import { createRootRouteWithContext, Link, Outlet } from '@tanstack/react-router'

const RootLayout = () => (
  <div>
    <div className="p-2">
      <div className="flex gap-2">
        <Link to="/" className="[&.active]:font-bold">
          Home
        </Link>{' '}
        <Link to="/about" className="[&.active]:font-bold">
          About
        </Link>
      </div>
    </div>
    <Outlet />
  </div>
)

export const Route = createRootRouteWithContext<RouterContext>()({
  component: RootLayout,
})
