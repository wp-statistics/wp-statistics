import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

export const getVisitorInsightDevicesTypeQueryOptions = () => {
  return queryOptions({
    queryKey: ['vi-devices-type'],
    queryFn: () =>
      clientRequest.get<DevicesTypeResponse>('', {
        params: {
          action: 'wp_statistics_root_visitor_insight_get_top_devices',
        },
      }),
  })
}
