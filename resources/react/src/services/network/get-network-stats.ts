import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'
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
  sites?: NetworkSiteStats[]
  period?: {
    from: string
    to: string
  }
  error?: string
}

export interface GetNetworkStatsParams {
  date_from: string
  date_to: string
}

export const getNetworkStatsQueryOptions = ({ date_from, date_to }: GetNetworkStatsParams) => {
  return queryOptions({
    queryKey: ['network-stats', date_from, date_to],
    queryFn: () =>
      clientRequest.post<NetworkStatsResponse>(
        '',
        {
          network: true,
          date_from,
          date_to,
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
