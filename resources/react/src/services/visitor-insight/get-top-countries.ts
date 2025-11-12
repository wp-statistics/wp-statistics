import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

export const getVisitorInsightTopCountriesQueryOptions = () => {
  return queryOptions({
    queryKey: ['top-countries'],
    queryFn: () =>
      clientRequest.get<TopCountriesResponse>('', {
        params: {
          action: 'wp_statistics_root_visitor_insight_get_top_countries',
        },
      }),
  })
}
