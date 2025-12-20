/**
 * Country center coordinates for map zoom functionality
 *
 * This file provides geographic center points for countries to enable
 * smooth zoom transitions when clicking on a country in the global map.
 *
 * Format: 'CountryCode': [longitude, latitude]
 */

interface CountryCentersMap {
  [key: string]: [number, number]
}

export const COUNTRY_CENTERS: CountryCentersMap = {
  // North America
  US: [-95.7129, 37.0902], // United States
  CA: [-106.3468, 56.1304], // Canada
  MX: [-102.5528, 23.6345], // Mexico

  // Europe - Western
  GB: [-3.435973, 55.378051], // United Kingdom
  IE: [-8.2439, 53.4129], // Ireland
  FR: [2.2137, 46.2276], // France
  ES: [-3.7492, 40.4637], // Spain
  PT: [-8.2245, 39.3999], // Portugal
  BE: [4.4699, 50.5039], // Belgium
  NL: [5.2913, 52.1326], // Netherlands
  LU: [6.1296, 49.8153], // Luxembourg
  CH: [8.2275, 46.8182], // Switzerland
  AT: [14.5501, 47.5162], // Austria
  LI: [9.5554, 47.166], // Liechtenstein

  // Europe - Northern
  NO: [8.4689, 60.472], // Norway
  SE: [18.6435, 60.1282], // Sweden
  FI: [25.7482, 61.9241], // Finland
  DK: [9.5018, 56.2639], // Denmark
  IS: [-19.0208, 64.9631], // Iceland

  // Europe - Central
  DE: [10.4515, 51.1657], // Germany
  PL: [19.1451, 51.9194], // Poland
  CZ: [15.473, 49.8175], // Czech Republic
  SK: [19.699, 48.669], // Slovakia
  HU: [19.5033, 47.1625], // Hungary

  // Europe - Southern
  IT: [12.5674, 41.8719], // Italy
  GR: [21.8243, 39.0742], // Greece
  HR: [15.2, 45.1], // Croatia
  SI: [14.9955, 46.1512], // Slovenia
  AL: [20.1683, 41.1533], // Albania
  MK: [21.7453, 41.6086], // North Macedonia
  RS: [21.0059, 44.0165], // Serbia
  BA: [17.6791, 43.9159], // Bosnia and Herzegovina
  ME: [19.3744, 42.7087], // Montenegro

  // Europe - Eastern
  RU: [105.3188, 61.524], // Russia
  UA: [31.1656, 48.3794], // Ukraine
  BY: [27.9534, 53.7098], // Belarus
  MD: [28.3699, 47.4116], // Moldova
  RO: [24.9668, 45.9432], // Romania
  BG: [25.4858, 42.7339], // Bulgaria

  // Europe - Baltic
  EE: [25.0136, 58.5953], // Estonia
  LV: [24.6032, 56.8796], // Latvia
  LT: [23.8813, 55.1694], // Lithuania

  // Asia - East
  CN: [104.1954, 35.8617], // China
  JP: [138.2529, 36.2048], // Japan
  KR: [127.7669, 35.9078], // South Korea
  KP: [127.5101, 40.3399], // North Korea
  MN: [103.8467, 46.8625], // Mongolia
  TW: [120.9605, 23.6978], // Taiwan
  HK: [114.1095, 22.3964], // Hong Kong
  MO: [113.5439, 22.1987], // Macau

  // Asia - Southeast
  TH: [100.9925, 15.87], // Thailand
  VN: [108.2772, 14.0583], // Vietnam
  MY: [101.9758, 4.2105], // Malaysia
  SG: [103.8198, 1.3521], // Singapore
  ID: [113.9213, -0.7893], // Indonesia
  PH: [121.774, 12.8797], // Philippines
  MM: [96.5844, 21.9162], // Myanmar
  KH: [104.991, 12.5657], // Cambodia
  LA: [102.4955, 19.8563], // Laos
  BN: [114.7277, 4.5353], // Brunei
  TL: [125.7276, -8.8742], // Timor-Leste

  // Asia - South
  IN: [78.9629, 20.5937], // India
  PK: [69.3451, 30.3753], // Pakistan
  BD: [90.3563, 23.685], // Bangladesh
  LK: [80.7718, 7.8731], // Sri Lanka
  NP: [84.124, 28.3949], // Nepal
  BT: [90.4336, 27.5142], // Bhutan
  MV: [73.2207, 3.2028], // Maldives
  AF: [67.7099, 33.9391], // Afghanistan

  // Asia - Central
  KZ: [66.9237, 48.0196], // Kazakhstan
  UZ: [64.5853, 41.3775], // Uzbekistan
  TM: [59.5563, 38.9697], // Turkmenistan
  KG: [74.7661, 41.2044], // Kyrgyzstan
  TJ: [71.2761, 38.861], // Tajikistan

  // Middle East
  TR: [35.2433, 38.9637], // Turkey
  SA: [45.0792, 23.8859], // Saudi Arabia
  AE: [53.8478, 23.4241], // United Arab Emirates
  IL: [34.8516, 31.0461], // Israel
  PS: [35.2332, 31.9522], // Palestine
  IQ: [43.6793, 33.2232], // Iraq
  IR: [53.688, 32.4279], // Iran
  SY: [38.9968, 34.8021], // Syria
  LB: [35.8623, 33.8547], // Lebanon
  JO: [36.2384, 30.5852], // Jordan
  YE: [48.5164, 15.5527], // Yemen
  OM: [55.9233, 21.4735], // Oman
  KW: [47.4818, 29.3117], // Kuwait
  QA: [51.1839, 25.3548], // Qatar
  BH: [50.5577, 26.0667], // Bahrain

  // Africa - North
  EG: [30.8025, 26.8206], // Egypt
  LY: [17.2283, 26.3351], // Libya
  TN: [9.5375, 33.8869], // Tunisia
  DZ: [1.6596, 28.0339], // Algeria
  MA: [-7.0926, 31.7917], // Morocco
  SD: [30.2176, 12.8628], // Sudan
  SS: [31.3069, 6.877], // South Sudan

  // Africa - West
  NG: [8.6753, 9.082], // Nigeria
  GH: [-1.0232, 7.9465], // Ghana
  SN: [-14.4524, 14.4974], // Senegal
  CI: [-5.5471, 7.54], // Côte d'Ivoire
  ML: [-3.9962, 17.5707], // Mali
  NE: [8.0817, 17.6078], // Niger
  BF: [-1.5616, 12.2383], // Burkina Faso
  CM: [12.3547, 7.3697], // Cameroon

  // Africa - East
  KE: [37.9062, -0.0236], // Kenya
  TZ: [34.8888, -6.369], // Tanzania
  UG: [32.2903, 1.3733], // Uganda
  ET: [40.4897, 9.145], // Ethiopia
  SO: [46.1996, 5.1521], // Somalia

  // Africa - Southern
  ZA: [22.9375, -30.5595], // South Africa
  ZW: [29.1549, -19.0154], // Zimbabwe
  BW: [24.6849, -22.3285], // Botswana
  NA: [18.4904, -22.9576], // Namibia
  MZ: [35.5296, -18.6657], // Mozambique
  ZM: [27.8493, -13.1339], // Zambia
  MW: [34.3015, -13.2543], // Malawi
  AO: [17.8739, -11.2027], // Angola

  // South America
  BR: [-51.9253, -14.235], // Brazil
  AR: [-63.6167, -38.4161], // Argentina
  CL: [-71.543, -35.6751], // Chile
  CO: [-74.2973, 4.5709], // Colombia
  PE: [-75.0152, -9.19], // Peru
  VE: [-66.5897, 6.4238], // Venezuela
  EC: [-78.1834, -1.8312], // Ecuador
  BO: [-63.5887, -16.2902], // Bolivia
  PY: [-58.4438, -23.4425], // Paraguay
  UY: [-55.7658, -32.5228], // Uruguay
  GY: [-58.9302, 4.8604], // Guyana
  SR: [-56.0278, 3.9193], // Suriname

  // Oceania
  AU: [133.7751, -25.2744], // Australia
  NZ: [174.886, -40.9006], // New Zealand
  PG: [143.9555, -6.315], // Papua New Guinea
  FJ: [179.4144, -17.7134], // Fiji
  NC: [165.618, -20.9043], // New Caledonia
  PF: [-149.4068, -17.6797], // French Polynesia

  // Caribbean
  CU: [-77.7812, 21.5218], // Cuba
  JM: [-77.2975, 18.1096], // Jamaica
  HT: [-72.2852, 18.9712], // Haiti
  DO: [-70.1627, 18.7357], // Dominican Republic
  PR: [-66.5901, 18.2208], // Puerto Rico
  TT: [-61.2225, 10.6918], // Trinidad and Tobago
  BS: [-77.3963, 25.0343], // Bahamas
  BB: [-59.5432, 13.1939], // Barbados
}

/**
 * Get the geographic center coordinates for a country
 * @param countryCode - The ISO 2-letter country code
 * @returns [longitude, latitude] or [0, 0] if not found
 */
export const getCountryCenter = (countryCode: string): [number, number] => {
  const code = countryCode.toUpperCase()
  return COUNTRY_CENTERS[code] || [0, 0]
}

/**
 * Get the recommended zoom level for a country based on its size
 * @param countryCode - The ISO 2-letter country code
 * @returns Recommended zoom level (1-8)
 */
export const getCountryZoomLevel = (countryCode: string): number => {
  const code = countryCode.toUpperCase()

  // Very large countries - lower zoom
  const largecountries = ['RU', 'CA', 'US', 'CN', 'BR', 'AU', 'IN', 'AR', 'KZ']
  if (largecountries.includes(code)) return 4

  // Medium countries - medium zoom
  const mediumCountries = ['MX', 'SA', 'DZ', 'CD', 'LY', 'IR', 'MN', 'PE', 'TD', 'NG']
  if (mediumCountries.includes(code)) return 6

  // Small countries - higher zoom
  const smallCountries = ['GB', 'DE', 'FR', 'IT', 'ES', 'PL', 'UA', 'TR', 'TH', 'JP', 'VN']
  if (smallCountries.includes(code)) return 8

  // Very small countries - highest zoom
  const verySmallCountries = ['SG', 'HK', 'LU', 'MT', 'MV', 'VA', 'MC', 'LI']
  if (verySmallCountries.includes(code)) return 12

  // Default zoom for countries not explicitly listed
  return 6
}

/**
 * Check if coordinates exist for a country
 * @param countryCode - The ISO 2-letter country code
 * @returns true if coordinates exist
 */
export const hasCountryCenter = (countryCode: string): boolean => {
  const code = countryCode.toUpperCase()
  return code in COUNTRY_CENTERS
}
