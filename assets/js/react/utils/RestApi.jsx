/**
 * Utility functions for handling WordPress REST API requests
 */

import { addQueryArgs } from '@wordpress/url';

const API_NAMESPACE = 'wp-statistics/v1';

/**
 * Create the full API URL for a given endpoint
 * @param {string} endpoint - The endpoint path
 * @returns {string} - The full API URL
 */
const getApiUrl = (endpoint) => {
    const wpApiUrl = window ? .wpApiSettings ? .root || '/wp-json/';
    return addQueryArgs(`${wpApiUrl}${API_NAMESPACE}/${endpoint.replace(/^\//, '')}`);
};

/**
 * Make a request to the WordPress REST API
 * @param {string} endpoint - The API endpoint
 * @param {Object} options - Request options
 * @returns {Promise} - Resolves with the response data or rejects with error
 */
const makeRequest = async(endpoint, options = {}) => {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window ? .wpApiSettings ? .nonce
        },
        credentials: 'same-origin'
    };

    try {
        const response = await fetch(
            getApiUrl(endpoint), {...defaultOptions, ...options }
        );

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('REST API request failed:', error);
        throw error;
    }
};

/**
 * GET request to the API
 * @param {string} endpoint - The API endpoint
 * @param {Object} queryParams - Query parameters to append to the URL
 * @returns {Promise} - Resolves with the response data
 */
export const get = (endpoint, queryParams = {}) => {
    const url = addQueryArgs(endpoint, queryParams);
    return makeRequest(url, { method: 'GET' });
};

/**
 * POST request to the API
 * @param {string} endpoint - The API endpoint
 * @param {Object} data - The data to send in the request body
 * @returns {Promise} - Resolves with the response data
 */
export const post = (endpoint, data = {}) => {
    return makeRequest(endpoint, {
        method: 'POST',
        body: JSON.stringify(data)
    });
};

/**
 * PUT request to the API
 * @param {string} endpoint - The API endpoint
 * @param {Object} data - The data to send in the request body
 * @returns {Promise} - Resolves with the response data
 */
export const put = (endpoint, data = {}) => {
    return makeRequest(endpoint, {
        method: 'PUT',
        body: JSON.stringify(data)
    });
};

/**
 * DELETE request to the API
 * @param {string} endpoint - The API endpoint
 * @returns {Promise} - Resolves with the response data
 */
export const del = (endpoint) => {
    return makeRequest(endpoint, { method: 'DELETE' });
};