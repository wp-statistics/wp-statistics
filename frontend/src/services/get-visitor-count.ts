import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

export const getVisitorCountQueryOptions = () => {
  return queryOptions({
    queryKey: ['test'],
    queryFn: () =>
      clientRequest.get<VisitorCountResponse>('', {
        params: {
          action: 'wp_statistics_root_overview_get_visitors_count',
        },
      }),
  })
}
