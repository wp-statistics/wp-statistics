import { getVisitorCountQueryOptions } from '@/services/get-visitor-count'
import { useSuspenseQuery } from '@tanstack/react-query'
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/')({
  loader: ({ context }) => context.queryClient.ensureQueryData(getVisitorCountQueryOptions()),
  component: Index,
})

function Index() {
  const {
    data: { data: result },
  } = useSuspenseQuery(getVisitorCountQueryOptions())

  return (
    <div className="p-2">
      <div className="text-3xl font-bold underline">Hello world!</div>
      <p>Current: {result.data.current}</p>
      <p>Percentage: {result.data.precentage}</p>
      <p>Previous: {result.data.previous}</p>
    </div>
  )
}
