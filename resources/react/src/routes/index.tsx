import { createFileRoute, redirect } from '@tanstack/react-router'

import { WordPress } from '@/lib/wordpress'

export const Route = createFileRoute('/')({
  beforeLoad: () => {
    const wp = WordPress.getInstance()

    // In network admin, redirect to network overview
    if (wp.isNetworkAdmin()) {
      throw redirect({
        to: '/network-overview',
      })
    }

    // Otherwise redirect to regular overview
    throw redirect({
      to: '/overview',
    })
  },
})
