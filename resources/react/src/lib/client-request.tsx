import axios from 'axios'

import { WordPress } from './wordpress'

const dataService = WordPress.getInstance()

const BASE_URL = dataService.getAjaxUrl()
const HEADERS = dataService.getHeaders()

const instance = axios.create({
  baseURL: BASE_URL,
})

instance.interceptors.request.use(
  async (request) => {
    Object.assign(request.headers, HEADERS)

    if (request.params && Object.keys(request.params).length) {
      for (const key of Object.keys(request.params)) {
        if (request.params[key] === '' || request.params[key] === undefined) {
          delete request.params[key]
        }
      }
    }

    return request
  },
  (error) => {
    return Promise.reject(error)
  }
)

instance.interceptors.response.use(
  (response) => response,
  async (error) => {
    return Promise.reject(error)
  }
)

export { instance as clientRequest }
