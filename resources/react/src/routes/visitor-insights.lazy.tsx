import { useSuspenseQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'

import { getVisitorCountQueryOptions } from '@/services/get-visitor-count'
import { Card, CardHeader, CardTitle } from '@/components/ui/card'

export const Route = createLazyFileRoute('/visitor-insights')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    data: { data: result },
  } = useSuspenseQuery(getVisitorCountQueryOptions())
  console.log(result)

  return (
    <div className="p-2 grid gap-6">
      <h1 className="text-2xl font-medium text-neutral-700">Visitor insights</h1>
      <div className="grid gap-3 grid-cols-12">
        <div className="col-span-12">Statistics Section</div>

        <Card className="col-span-12">
          <CardHeader>
            <CardTitle>Traffic Trends</CardTitle>
          </CardHeader>
        </Card>

        <Card className="col-span-6">
          <CardHeader>
            <CardTitle>Top Entry Pages</CardTitle>
          </CardHeader>
        </Card>

        <Card className="col-span-6">
          <CardHeader>
            <CardTitle>Top Referrers</CardTitle>
          </CardHeader>
        </Card>

        <Card className="col-span-4">
          <CardHeader>
            <CardTitle>Top Countries</CardTitle>
          </CardHeader>
        </Card>

        <Card className="col-span-4">
          <CardHeader>
            <CardTitle>Device Type</CardTitle>
          </CardHeader>
        </Card>

        <Card className="col-span-4">
          <CardHeader>
            <CardTitle>Operating Systems</CardTitle>
          </CardHeader>
        </Card>

        <Card className="col-span-12">
          <CardHeader>
            <CardTitle>Top Visitors</CardTitle>
          </CardHeader>
        </Card>

        <Card className="col-span-6">
          <CardHeader>
            <CardTitle>Traffic by Hour</CardTitle>
          </CardHeader>
        </Card>

        <Card className="col-span-6">
          <CardHeader>
            <CardTitle>Global Visitor Distribution</CardTitle>
          </CardHeader>
        </Card>
      </div>
    </div>
  )
}
