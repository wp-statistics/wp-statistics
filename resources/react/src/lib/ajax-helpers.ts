export interface AjaxResponse<T> {
  success: boolean
  data: T | { message: string }
}

export function assertAjaxSuccess<T>(resp: { data: AjaxResponse<T> }): T {
  if (!resp?.data?.success) {
    const msg = (resp?.data?.data as { message?: string })?.message || 'Request failed'
    throw new Error(msg)
  }
  return resp.data.data as T
}
