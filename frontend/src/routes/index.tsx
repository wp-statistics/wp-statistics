import { useSuspenseQuery } from '@tanstack/react-query'
import { createFileRoute } from '@tanstack/react-router'

import { testQueryOptions } from '@/services/test'

export const Route = createFileRoute('/')({
  loader: ({ context: { queryClient } }) => {
    return queryClient.ensureQueryData(testQueryOptions())
  },
  component: Index,
})

function Index() {
  const { data } = useSuspenseQuery(testQueryOptions())

  console.log(data)

  return (
    <div className="p-2">
      <h3>Welcome Home!</h3>
    </div>
  )
}
