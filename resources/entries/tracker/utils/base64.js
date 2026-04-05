/**
 * Base64 encoding utility using TextEncoder for proper UTF-8 support.
 */
export function base64Encode(str) {
    var encoder = new TextEncoder();
    var data = encoder.encode(str);
    var binary = '';
    for (var i = 0; i < data.length; i++) {
        binary += String.fromCharCode(data[i]);
    }
    return btoa(binary);
}
