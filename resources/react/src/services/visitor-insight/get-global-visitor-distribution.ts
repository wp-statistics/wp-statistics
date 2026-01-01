import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

export const getVisitorInsightGlobalVisitorDistributionQueryOptions = () => {
  return queryOptions({
    queryKey: ['vi-global-visitor-distribution'],
    queryFn: () =>
      clientRequest.get<GlobalVisitorDistributionResponse>('', {
        params: {
          action: 'wp_statistics_root_visitor_insight_get_global_distribution',
        },
      }),
  })
}
