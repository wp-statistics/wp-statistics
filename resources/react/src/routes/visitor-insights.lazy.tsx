import { useSuspenseQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'

import { getVisitorCountQueryOptions } from '@/services/get-visitor-count'

export const Route = createLazyFileRoute('/visitor-insights')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    data: { data: result },
  } = useSuspenseQuery(getVisitorCountQueryOptions())

  return (
    <div className="p-2">
      <div className="text-xl font-bold ">Visitor Count</div>
      <p>Current: {result.data.current}</p>
      <p>Percentage: {result.data.precentage}</p>
      <p>Previous: {result.data.previous}</p>
    </div>
  )
}
