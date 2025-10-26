import { Button } from '@/components/ui/button'
import { createFileRoute } from '@tanstack/react-router'
import React from 'react'

const Index = () => {
  const [state, setState] = React.useState(0)

  return (
    <div className="p-2">
      <h3>Welcome Home!</h3>
      <Button onClick={() => setState((prev) => prev + 1)}>Click Me {state}</Button>
    </div>
  )
}

export const Route = createFileRoute('/')({
  component: Index,
})
