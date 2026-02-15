import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

export const getVisitorInsightTrafficTrendsQueryOptions = ({ range, hasPreviousData = true }: TrafficTrendsParams) => {
  return queryOptions({
    queryKey: ['vi-traffic-trends', range, hasPreviousData],
    queryFn: () =>
      clientRequest.get<TrafficTrendsResponse>('', {
        params: {
          action: 'wp_statistics_root_visitor_insight_get_traffic_trends',
          range,
          hasPreviousData,
        },
      }),
  })
}
