import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

export const getVisitorInsightOSSQueryOptions = () => {
  return queryOptions({
    queryKey: ['vi-oss'],
    queryFn: () =>
      clientRequest.get<OSSResponse>('', {
        params: {
          action: 'wp_statistics_root_visitor_insight_get_top_oss',
        },
      }),
  })
}
