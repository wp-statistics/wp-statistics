import { createFileRoute, redirect } from '@tanstack/react-router'

export const Route = createFileRoute('/')({
  beforeLoad: () => {
    // Redirect root to overview (hash routes handle the rest)
    throw redirect({
      to: '/overview',
    })
  },
})
