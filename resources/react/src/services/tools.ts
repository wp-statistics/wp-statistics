import { WordPress } from '@/lib/wordpress'

const wp = WordPress.getInstance()
const AJAX_URL = wp.getAjaxUrl()

/**
 * Internal helper to make FormData AJAX requests.
 *
 * Tools endpoints use WordPress AJAX (FormData + wps_nonce) rather than
 * the JSON-based clientRequest used by analytics endpoints.
 */
async function ajaxPost(action: string, formData: FormData) {
  const response = await fetch(`${AJAX_URL}?action=${action}`, {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  })

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`)
  }

  return response.json()
}

function createFormData(subAction: string, params: Record<string, string> = {}): FormData {
  const formData = new FormData()
  formData.append('wps_nonce', wp.getNonce())
  formData.append('sub_action', subAction)
  Object.entries(params).forEach(([key, value]) => {
    formData.append(key, value)
  })
  return formData
}

/**
 * Call the wp_statistics_tools AJAX endpoint with a sub_action.
 */
export const callToolsApi = async (subAction: string, params: Record<string, string> = {}) => {
  return ajaxPost('wp_statistics_tools', createFormData(subAction, params))
}

/**
 * Call the wp_statistics_import_export AJAX endpoint with a sub_action.
 */
export const callImportExportApi = async (
  subAction: string,
  params: Record<string, string> = {},
  formData?: FormData
) => {
  const data = formData || new FormData()
  data.append('wps_nonce', wp.getNonce())
  data.append('sub_action', subAction)
  Object.entries(params).forEach(([key, value]) => {
    if (!data.has(key)) {
      data.append(key, value)
    }
  })

  return ajaxPost('wp_statistics_import_export', data)
}
