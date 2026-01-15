import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'
import { queryKeys, createDateParams } from '@/lib/query-keys'
import { WordPress } from '@/lib/wordpress'

export interface NetworkSiteStats {
  blog_id: number
  name: string
  url: string
  admin_url: string
  visitors: number
  views: number
  sessions: number
  error?: string
}

export interface NetworkTotals {
  visitors: number
  views: number
  sessions: number
}

export interface NetworkStatsResponse {
  success: boolean
  totals?: NetworkTotals
  previous_totals?: NetworkTotals
  sites?: NetworkSiteStats[]
  period?: {
    from: string
    to: string
  }
  previous_period?: {
    from: string
    to: string
  }
  error?: string
}

export interface GetNetworkStatsParams {
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
}

export const getNetworkStatsQueryOptions = ({
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
}: GetNetworkStatsParams) => {
  return queryOptions({
    queryKey: queryKeys.network.stats(createDateParams(date_from, date_to, previous_date_from, previous_date_to)),
    queryFn: () =>
      clientRequest.post<NetworkStatsResponse>(
        '',
        {
          network: true,
          date_from,
          date_to,
          ...(previous_date_from && previous_date_to ? { previous_date_from, previous_date_to } : {}),
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
