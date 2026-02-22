import * as React from 'react'

type ToastVariant = 'default' | 'destructive'

interface Toast {
  id: string
  title?: string
  description?: string
  variant?: ToastVariant
}

interface ToastState {
  toasts: Toast[]
}

type ToastAction =
  | { type: 'ADD_TOAST'; toast: Toast }
  | { type: 'REMOVE_TOAST'; id: string }
  | { type: 'DISMISS_TOAST'; id: string }

const TOAST_REMOVE_DELAY = 5000

const toastReducer = (state: ToastState, action: ToastAction): ToastState => {
  switch (action.type) {
    case 'ADD_TOAST':
      return {
        ...state,
        toasts: [...state.toasts, action.toast],
      }
    case 'REMOVE_TOAST':
      return {
        ...state,
        toasts: state.toasts.filter((t) => t.id !== action.id),
      }
    case 'DISMISS_TOAST':
      return {
        ...state,
        toasts: state.toasts.filter((t) => t.id !== action.id),
      }
    default:
      return state
  }
}

let toastCount = 0

function generateId() {
  return `toast-${++toastCount}-${Date.now()}`
}

// Simple global state for toasts
const listeners: Array<(state: ToastState) => void> = []
let memoryState: ToastState = { toasts: [] }

function dispatch(action: ToastAction) {
  memoryState = toastReducer(memoryState, action)
  listeners.forEach((listener) => listener(memoryState))
}

interface ToastOptions {
  title?: string
  description?: string
  variant?: ToastVariant
}

function toast(options: ToastOptions) {
  const id = generateId()

  dispatch({
    type: 'ADD_TOAST',
    toast: {
      id,
      ...options,
    },
  })

  // Auto dismiss after delay
  setTimeout(() => {
    dispatch({ type: 'REMOVE_TOAST', id })
  }, TOAST_REMOVE_DELAY)

  return id
}

function useToast() {
  const [state, setState] = React.useState<ToastState>(memoryState)

  React.useEffect(() => {
    listeners.push(setState)
    return () => {
      const index = listeners.indexOf(setState)
      if (index > -1) {
        listeners.splice(index, 1)
      }
    }
  }, [])

  return {
    ...state,
    toast,
    dismiss: (id: string) => dispatch({ type: 'DISMISS_TOAST', id }),
  }
}

export { toast, useToast }
export type { Toast, ToastOptions, ToastVariant }
