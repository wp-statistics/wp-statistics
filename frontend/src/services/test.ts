import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

interface TestResponse {
  success: boolean
  data: any
}

export const testQueryOptions = () => {
  return queryOptions({
    queryKey: ['test'],
    queryFn: () =>
      clientRequest.get<TestResponse>('', {
        params: {
          action: 'wp_statistics_root_overview_get_visitors_count',
        },
      }),
  })
}
