import { WordPress } from '@/lib/wordpress'

/**
 * Call the wp_statistics_tools AJAX endpoint with a sub_action.
 */
export const callToolsApi = async (subAction: string, params: Record<string, string> = {}) => {
  const wp = WordPress.getInstance()
  const formData = new FormData()
  formData.append('wps_nonce', wp.getNonce())
  formData.append('sub_action', subAction)
  Object.entries(params).forEach(([key, value]) => {
    formData.append(key, value)
  })

  const response = await fetch(`${wp.getAjaxUrl()}?action=wp_statistics_tools`, {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  })

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`)
  }

  return response.json()
}

/**
 * Call the wp_statistics_import_export AJAX endpoint with a sub_action.
 */
export const callImportExportApi = async (
  subAction: string,
  params: Record<string, string> = {},
  formData?: FormData
) => {
  const wp = WordPress.getInstance()
  const data = formData || new FormData()
  data.append('wps_nonce', wp.getNonce())
  data.append('sub_action', subAction)
  Object.entries(params).forEach(([key, value]) => {
    if (!data.has(key)) {
      data.append(key, value)
    }
  })

  const response = await fetch(`${wp.getAjaxUrl()}?action=wp_statistics_import_export`, {
    method: 'POST',
    body: data,
    credentials: 'same-origin',
  })

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`)
  }

  return response.json()
}
