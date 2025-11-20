<?php

namespace WP_Statistics\Components;

use DateTimeZone;

/**
 * Manages country-related data.
 *
 * Provides access to localized country names, flag URLs, and validation utilities
 * based on ISO country codes. Also supports country inference via WordPress timezone settings.
 *
 * @package WP_Statistics\Components
 * @since 15.0.0
 */
class Country
{
    /**
     * The code for an unknown country.
     *
     * @var string
     */
    public static $unknownCountryCode = '000';

    /**
     * Cached country list.
     *
     * @var array|null
     */
    private static $countryListCache = null;

    /**
     * Get all country codes mapped to their localized names.
     *
     * @return array
     */
    public static function getAll()
    {
        if (self::$countryListCache !== null) {
            return self::$countryListCache;
        }

        self::$countryListCache = [
            '000' => _x('(not set)', 'Country', 'wp-statistics'),
            'AF'  => __('Afghanistan', 'wp-statistics'),
            'AX'  => __('Åland Islands', 'wp-statistics'),
            'AL'  => __('Albania', 'wp-statistics'),
            'DZ'  => __('Algeria', 'wp-statistics'),
            'AS'  => __('American Samoa', 'wp-statistics'),
            'AD'  => __('Andorra', 'wp-statistics'),
            'AO'  => __('Angola', 'wp-statistics'),
            'AI'  => __('Anguilla', 'wp-statistics'),
            'AQ'  => __('Antarctica', 'wp-statistics'),
            'AG'  => __('Antigua and Barbuda', 'wp-statistics'),
            'AR'  => __('Argentina', 'wp-statistics'),
            'AM'  => __('Armenia', 'wp-statistics'),
            'AW'  => __('Aruba', 'wp-statistics'),
            'AU'  => __('Australia', 'wp-statistics'),
            'AT'  => __('Austria', 'wp-statistics'),
            'AZ'  => __('Azerbaijan', 'wp-statistics'),
            'BS'  => __('Bahamas', 'wp-statistics'),
            'BH'  => __('Bahrain', 'wp-statistics'),
            'BD'  => __('Bangladesh', 'wp-statistics'),
            'BB'  => __('Barbados', 'wp-statistics'),
            'BY'  => __('Belarus', 'wp-statistics'),
            'BE'  => __('Belgium', 'wp-statistics'),
            'BZ'  => __('Belize', 'wp-statistics'),
            'BJ'  => __('Benin', 'wp-statistics'),
            'BM'  => __('Bermuda', 'wp-statistics'),
            'BT'  => __('Bhutan', 'wp-statistics'),
            'BO'  => __('Bolivia, Plurinational State of', 'wp-statistics'),
            'BQ'  => __('Bonaire, Sint Eustatius and Saba', 'wp-statistics'),
            'BA'  => __('Bosnia and Herzegovina', 'wp-statistics'),
            'BW'  => __('Botswana', 'wp-statistics'),
            'BV'  => __('Bouvet Island', 'wp-statistics'),
            'BR'  => __('Brazil', 'wp-statistics'),
            'IO'  => __('British Indian Ocean Territory', 'wp-statistics'),
            'BN'  => __('Brunei Darussalam', 'wp-statistics'),
            'BG'  => __('Bulgaria', 'wp-statistics'),
            'BF'  => __('Burkina Faso', 'wp-statistics'),
            'BU'  => __('Burma', 'wp-statistics'),
            'BI'  => __('Burundi', 'wp-statistics'),
            'KH'  => __('Cambodia', 'wp-statistics'),
            'CM'  => __('Cameroon', 'wp-statistics'),
            'CA'  => __('Canada', 'wp-statistics'),
            'CT'  => __('Canton and Enderbury Islands', 'wp-statistics'),
            'CV'  => __('Cape Verde', 'wp-statistics'),
            'KY'  => __('Cayman Islands', 'wp-statistics'),
            'CF'  => __('Central African Republic', 'wp-statistics'),
            'TD'  => __('Chad', 'wp-statistics'),
            'CL'  => __('Chile', 'wp-statistics'),
            'CN'  => __('China', 'wp-statistics'),
            'CX'  => __('Christmas Island', 'wp-statistics'),
            'CC'  => __('Cocos (Keeling) Islands', 'wp-statistics'),
            'CO'  => __('Colombia', 'wp-statistics'),
            'KM'  => __('Comoros', 'wp-statistics'),
            'CG'  => __('Congo', 'wp-statistics'),
            'CD'  => __('Congo (the Democratic Republic of the)', 'wp-statistics'),
            'CK'  => __('Cook Islands', 'wp-statistics'),
            'CR'  => __('Costa Rica', 'wp-statistics'),
            'CI'  => __('Côte d\'Ivoire', 'wp-statistics'),
            'HR'  => __('Croatia', 'wp-statistics'),
            'CU'  => __('Cuba', 'wp-statistics'),
            'CW'  => __('Curaçao', 'wp-statistics'),
            'CY'  => __('Cyprus', 'wp-statistics'),
            'CZ'  => __('Czech Republic', 'wp-statistics'),
            'CS'  => __('Czechoslovakia', 'wp-statistics'),
            'DY'  => __('Dahomey', 'wp-statistics'),
            'DK'  => __('Denmark', 'wp-statistics'),
            'DJ'  => __('Djibouti', 'wp-statistics'),
            'DM'  => __('Dominica', 'wp-statistics'),
            'DO'  => __('Dominican Republic', 'wp-statistics'),
            'NQ'  => __('Dronning Maud Land', 'wp-statistics'),
            'TP'  => __('East Timor', 'wp-statistics'),
            'EC'  => __('Ecuador', 'wp-statistics'),
            'EG'  => __('Egypt', 'wp-statistics'),
            'SV'  => __('El Salvador', 'wp-statistics'),
            'GQ'  => __('Equatorial Guinea', 'wp-statistics'),
            'ER'  => __('Eritrea', 'wp-statistics'),
            'EE'  => __('Estonia', 'wp-statistics'),
            'ET'  => __('Ethiopia', 'wp-statistics'),
            'FK'  => __('Falkland Islands [Malvinas]', 'wp-statistics'),
            'FO'  => __('Faroe Islands', 'wp-statistics'),
            'FJ'  => __('Fiji', 'wp-statistics'),
            'FI'  => __('Finland', 'wp-statistics'),
            'FR'  => __('France', 'wp-statistics'),
            'FX'  => __('France, Metropolitan', 'wp-statistics'),
            'AI'  => __('French Afars and Issas', 'wp-statistics'),
            'GF'  => __('French Guiana', 'wp-statistics'),
            'PF'  => __('French Polynesia', 'wp-statistics'),
            'FQ'  => __('French Southern and Antarctic Territories', 'wp-statistics'),
            'TF'  => __('French Southern Territories', 'wp-statistics'),
            'GA'  => __('Gabon', 'wp-statistics'),
            'GM'  => __('Gambia', 'wp-statistics'),
            'GE'  => __('Georgia', 'wp-statistics'),
            'DD'  => __('German Democratic Republic', 'wp-statistics'),
            'DE'  => __('Germany', 'wp-statistics'),
            'GH'  => __('Ghana', 'wp-statistics'),
            'GI'  => __('Gibraltar', 'wp-statistics'),
            'GR'  => __('Greece', 'wp-statistics'),
            'GL'  => __('Greenland', 'wp-statistics'),
            'GD'  => __('Grenada', 'wp-statistics'),
            'GP'  => __('Guadeloupe', 'wp-statistics'),
            'GU'  => __('Guam', 'wp-statistics'),
            'GT'  => __('Guatemala', 'wp-statistics'),
            'GG'  => __('Guernsey', 'wp-statistics'),
            'GN'  => __('Guinea', 'wp-statistics'),
            'GW'  => __('Guinea-Bissau', 'wp-statistics'),
            'GY'  => __('Guyana', 'wp-statistics'),
            'HT'  => __('Haiti', 'wp-statistics'),
            'HM'  => __('Heard Island and McDonald Islands', 'wp-statistics'),
            'VA'  => __('Holy See [Vatican City State]', 'wp-statistics'),
            'HN'  => __('Honduras', 'wp-statistics'),
            'HK'  => __('Hong Kong', 'wp-statistics'),
            'HU'  => __('Hungary', 'wp-statistics'),
            'IS'  => __('Iceland', 'wp-statistics'),
            'IN'  => __('India', 'wp-statistics'),
            'ID'  => __('Indonesia', 'wp-statistics'),
            'IR'  => __('Iran', 'wp-statistics'),
            'IQ'  => __('Iraq', 'wp-statistics'),
            'IE'  => __('Ireland', 'wp-statistics'),
            'IM'  => __('Isle of Man', 'wp-statistics'),
            'IL'  => __('Israel', 'wp-statistics'),
            'IT'  => __('Italy', 'wp-statistics'),
            'JM'  => __('Jamaica', 'wp-statistics'),
            'JP'  => __('Japan', 'wp-statistics'),
            'JE'  => __('Jersey', 'wp-statistics'),
            'JT'  => __('Johnston Island', 'wp-statistics'),
            'JO'  => __('Jordan', 'wp-statistics'),
            'KZ'  => __('Kazakhstan', 'wp-statistics'),
            'KE'  => __('Kenya', 'wp-statistics'),
            'KI'  => __('Kiribati', 'wp-statistics'),
            'KP'  => __('Korea (the Democratic People\'s Republic of)', 'wp-statistics'),
            'KR'  => __('Korea (the Republic of)', 'wp-statistics'),
            'KW'  => __('Kuwait', 'wp-statistics'),
            'KG'  => __('Kyrgyzstan', 'wp-statistics'),
            'LA'  => __('Lao People\'s Democratic Republic', 'wp-statistics'),
            'LV'  => __('Latvia', 'wp-statistics'),
            'LB'  => __('Lebanon', 'wp-statistics'),
            'LS'  => __('Lesotho', 'wp-statistics'),
            'LR'  => __('Liberia', 'wp-statistics'),
            'LY'  => __('Libya', 'wp-statistics'),
            'LI'  => __('Liechtenstein', 'wp-statistics'),
            'LT'  => __('Lithuania', 'wp-statistics'),
            'LU'  => __('Luxembourg', 'wp-statistics'),
            'MO'  => __('Macao', 'wp-statistics'),
            'MK'  => __('Macedonia (the former Yugoslav Republic of)', 'wp-statistics'),
            'MG'  => __('Madagascar', 'wp-statistics'),
            'MW'  => __('Malawi', 'wp-statistics'),
            'MY'  => __('Malaysia', 'wp-statistics'),
            'MV'  => __('Maldives', 'wp-statistics'),
            'ML'  => __('Mali', 'wp-statistics'),
            'MT'  => __('Malta', 'wp-statistics'),
            'MH'  => __('Marshall Islands', 'wp-statistics'),
            'MQ'  => __('Martinique', 'wp-statistics'),
            'MR'  => __('Mauritania', 'wp-statistics'),
            'MU'  => __('Mauritius', 'wp-statistics'),
            'YT'  => __('Mayotte', 'wp-statistics'),
            'MX'  => __('Mexico', 'wp-statistics'),
            'FM'  => __('Micronesia (the Federated States of)', 'wp-statistics'),
            'MI'  => __('Midway Islands', 'wp-statistics'),
            'MD'  => __('Moldova (the Republic of)', 'wp-statistics'),
            'MC'  => __('Monaco', 'wp-statistics'),
            'MN'  => __('Mongolia', 'wp-statistics'),
            'ME'  => __('Montenegro', 'wp-statistics'),
            'MS'  => __('Montserrat', 'wp-statistics'),
            'MA'  => __('Morocco', 'wp-statistics'),
            'MZ'  => __('Mozambique', 'wp-statistics'),
            'MM'  => __('Myanmar', 'wp-statistics'),
            'NA'  => __('Namibia', 'wp-statistics'),
            'NR'  => __('Nauru', 'wp-statistics'),
            'NP'  => __('Nepal', 'wp-statistics'),
            'NL'  => __('Netherlands', 'wp-statistics'),
            'AN'  => __('Netherlands Antilles', 'wp-statistics'),
            'NT'  => __('Neutral Zone', 'wp-statistics'),
            'NC'  => __('New Caledonia', 'wp-statistics'),
            'NH'  => __('New Hebrides', 'wp-statistics'),
            'NZ'  => __('New Zealand', 'wp-statistics'),
            'NI'  => __('Nicaragua', 'wp-statistics'),
            'NE'  => __('Niger', 'wp-statistics'),
            'NG'  => __('Nigeria', 'wp-statistics'),
            'NU'  => __('Niue', 'wp-statistics'),
            'NF'  => __('Norfolk Island', 'wp-statistics'),
            'MP'  => __('Northern Mariana Islands', 'wp-statistics'),
            'NO'  => __('Norway', 'wp-statistics'),
            'OM'  => __('Oman', 'wp-statistics'),
            'PC'  => __('Pacific Islands (Trust Territory)', 'wp-statistics'),
            'PK'  => __('Pakistan', 'wp-statistics'),
            'PW'  => __('Palau', 'wp-statistics'),
            'PS'  => __('Palestine, State of', 'wp-statistics'),
            'PA'  => __('Panama', 'wp-statistics'),
            'PZ'  => __('Panama Canal Zone', 'wp-statistics'),
            'PG'  => __('Papua New Guinea', 'wp-statistics'),
            'PY'  => __('Paraguay', 'wp-statistics'),
            'PE'  => __('Peru', 'wp-statistics'),
            'PH'  => __('Philippines', 'wp-statistics'),
            'PN'  => __('Pitcairn', 'wp-statistics'),
            'PL'  => __('Poland', 'wp-statistics'),
            'PT'  => __('Portugal', 'wp-statistics'),
            'PR'  => __('Puerto Rico', 'wp-statistics'),
            'QA'  => __('Qatar', 'wp-statistics'),
            'RE'  => __('Réunion', 'wp-statistics'),
            'RO'  => __('Romania', 'wp-statistics'),
            'RU'  => __('Russian Federation', 'wp-statistics'),
            'RW'  => __('Rwanda', 'wp-statistics'),
            'BL'  => __('Saint Barthélemy', 'wp-statistics'),
            'SH'  => __('Saint Helena, Ascension and Tristan da Cunha', 'wp-statistics'),
            'KN'  => __('Saint Kitts and Nevis', 'wp-statistics'),
            'LC'  => __('Saint Lucia', 'wp-statistics'),
            'MF'  => __('Saint Martin (French part)', 'wp-statistics'),
            'PM'  => __('Saint Pierre and Miquelon', 'wp-statistics'),
            'VC'  => __('Saint Vincent and the Grenadines', 'wp-statistics'),
            'WS'  => __('Samoa', 'wp-statistics'),
            'SM'  => __('San Marino', 'wp-statistics'),
            'ST'  => __('Sao Tome and Principe', 'wp-statistics'),
            'SA'  => __('Saudi Arabia', 'wp-statistics'),
            'SN'  => __('Senegal', 'wp-statistics'),
            'RS'  => __('Serbia', 'wp-statistics'),
            'CS'  => __('Serbia and Montenegro', 'wp-statistics'),
            'SC'  => __('Seychelles', 'wp-statistics'),
            'SL'  => __('Sierra Leone', 'wp-statistics'),
            'SK'  => __('Slovakia', 'wp-statistics'),
            'SI'  => __('Slovenia', 'wp-statistics'),
            'SB'  => __('Solomon Islands', 'wp-statistics'),
            'SO'  => __('Somalia', 'wp-statistics'),
            'ZA'  => __('South Africa', 'wp-statistics'),
            'GS'  => __('South Georgia and the South Sandwich Islands', 'wp-statistics'),
            'SS'  => __('South Sudan ', 'wp-statistics'),
            'RH'  => __('Southern Rhodesia', 'wp-statistics'),
            'ES'  => __('Spain', 'wp-statistics'),
            'LK'  => __('Sri Lanka', 'wp-statistics'),
            'SD'  => __('Sudan', 'wp-statistics'),
            'SR'  => __('Suriname', 'wp-statistics'),
            'SJ'  => __('Svalbard and Jan Mayen', 'wp-statistics'),
            'SZ'  => __('Swaziland', 'wp-statistics'),
            'SE'  => __('Sweden', 'wp-statistics'),
            'CH'  => __('Switzerland', 'wp-statistics'),
            'SY'  => __('Syrian Arab Republic', 'wp-statistics'),
            'TW'  => __('Taiwan', 'wp-statistics'),
            'TJ'  => __('Tajikistan', 'wp-statistics'),
            'TZ'  => __('Tanzania, United Republic of', 'wp-statistics'),
            'TH'  => __('Thailand', 'wp-statistics'),
            'TL'  => __('Timor-Leste', 'wp-statistics'),
            'TG'  => __('Togo', 'wp-statistics'),
            'TK'  => __('Tokelau', 'wp-statistics'),
            'TO'  => __('Tonga', 'wp-statistics'),
            'TT'  => __('Trinidad and Tobago', 'wp-statistics'),
            'TN'  => __('Tunisia', 'wp-statistics'),
            'TR'  => __('Turkey', 'wp-statistics'),
            'TM'  => __('Turkmenistan', 'wp-statistics'),
            'TC'  => __('Turks and Caicos Islands', 'wp-statistics'),
            'TV'  => __('Tuvalu', 'wp-statistics'),
            'UG'  => __('Uganda', 'wp-statistics'),
            'UA'  => __('Ukraine', 'wp-statistics'),
            'AE'  => __('United Arab Emirates', 'wp-statistics'),
            'GB'  => __('United Kingdom', 'wp-statistics'),
            'US'  => __('United States', 'wp-statistics'),
            'UM'  => __('United States Minor Outlying Islands', 'wp-statistics'),
            'PU'  => __('United States Miscellaneous Pacific Islands', 'wp-statistics'),
            'HV'  => __('Upper Volta', 'wp-statistics'),
            'UY'  => __('Uruguay', 'wp-statistics'),
            'SU'  => __('USSR', 'wp-statistics'),
            'UZ'  => __('Uzbekistan', 'wp-statistics'),
            'VU'  => __('Vanuatu', 'wp-statistics'),
            'VE'  => __('Venezuela, Bolivarian Republic of ', 'wp-statistics'),
            'VN'  => __('Viet Nam', 'wp-statistics'),
            'VD'  => __('Viet-Nam, Democratic Republic of', 'wp-statistics'),
            'VG'  => __('Virgin Islands (British)', 'wp-statistics'),
            'VI'  => __('Virgin Islands (U.S.)', 'wp-statistics'),
            'WK'  => __('Wake Island', 'wp-statistics'),
            'WF'  => __('Wallis and Futuna', 'wp-statistics'),
            'EH'  => __('Western Sahara', 'wp-statistics'),
            'XK'  => __('Kosovo', 'wp-statistics'),
            'YE'  => __('Yemen', 'wp-statistics'),
            'YD'  => __('Yemen, Democratic', 'wp-statistics'),
            'YU'  => __('Yugoslavia', 'wp-statistics'),
            'ZR'  => __('Zaire', 'wp-statistics'),
            'ZM'  => __('Zambia', 'wp-statistics'),
            'ZW'  => __('Zimbabwe', 'wp-statistics'),
        ];

        return self::$countryListCache;
    }

    /**
     * Get the localized name of a country by its code.
     *
     * @param string $code
     * @return string
     */
    public static function getName($code)
    {
        $countryList = self::getAll();
        $code        = strtoupper($code);

        if (array_key_exists($code, $countryList)) {
            return $countryList[$code];
        }

        return $countryList[self::$unknownCountryCode];
    }

    /**
     * Get country flag URL by code.
     *
     * @param string $code
     * @return string
     */
    public static function getFlag($code)
    {
        $countryList = self::getAll();
        $code        = strtoupper($code);

        if (!array_key_exists($code, $countryList)) {
            $code = self::$unknownCountryCode;
        }

        return WP_STATISTICS_URL . 'public/images/flags/' . strtolower($code) . '.svg';
    }

    /**
     * Derive the site's country code from the configured timezone string.
     *
     * @return string country code or an empty string if the timezone cannot be mapped.
     */
    public static function getByTimeZone()
    {
        $countryCode = '';

        $timezone  = get_option('timezone_string');
        $timezones = timezone_identifiers_list();

        if (in_array($timezone, $timezones)) {
            $location    = timezone_location_get(new DateTimeZone($timezone));
            $countryCode = $location['country_code'];
        }

        return $countryCode;
    }

    /**
     * Check if a country code is valid.
     *
     * @param string $code
     * @return bool
     */
    public static function isValid($code)
    {
        $countryList = self::getAll();
        return array_key_exists(strtoupper($code), $countryList);
    }
}
