<?php

/**
 * Class WP_Statistics_Suggestions
 */
class WP_Statistics_Suggestions {
	/**
	 * WP_Statistics_Suggestions constructor.
	 */
	public function __construct() {
		global $WP_Statistics;

		// Check the suggestion is enabled.
		if ( ! $WP_Statistics->get_option( 'disable_suggestion_nag', false ) ) {
			$include_language = array(
				'en_US',
				'en_AU',
				'en_CA',
				'en_NZ',
				'zh_SG',
				'ko_KR',
				'ja',
				'hu_HU',
				'nl_BE',
				'da_DK',
				'fi',
				'sv_SE',
				'fr_FR',
				'de_DE',
				'is_IS',
				'ga',
				'it_IT',
				'lb_LU',
				'fy',
				'pap',
				'nn_NO',
				'nb_NO',
				'nn_NO',
				'pl_PL',
				'pt_PT',
				'rup_MK',
				'es_ES',
				'fr-ch'
			);

			if ( array_search( get_locale(), $include_language ) !== false ) {
				add_action( 'wp_statistics_after_title', array( $this, 'travod_widget' ) );
			}
		}
	}

	public function travod_widget() {
		if ( isset( $_POST['full_name'] ) and isset( $_POST['email'] ) ) {
			global $WP_Statistics;
			$languages = array();

			foreach ( $this->get_suggestion() as $item ) {
				$languages[] = $item['language'];
			}

			// Build the request parameter
			$args = array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => json_encode( array(
						'website'    => get_bloginfo( 'url' ),
						'full_name'  => $_POST['full_name'],
						'email'      => $_POST['email'],
						'languages'  => implode( $languages, ', ' ),
						'ip_address' => $WP_Statistics->get_IP(),
						'timestamp'  => time(),
					)
				)
			);

			// Send data to url
			$response = wp_remote_post( 'https://hooks.zapier.com/hooks/catch/3049993/aqqp46/', $args );

			if ( ! is_wp_error( $response ) ) {
				// Disable the suggestion
				$WP_Statistics->update_option( 'disable_suggestion_nag', true );
				$WP_Statistics->update_option( 'admin_notices', false );

				$link = "<script>window.location = 'https://www.travod.com/thanks/';</script>";
				echo $link;
			}
		}

		$base_url = $this->get_base_url( get_bloginfo( 'url' ) );
		$current_user_fullname = $this->get_current_user_fullname();

		include( WP_Statistics::$reg['plugin-dir'] . "includes/templates/suggestions/travod.php" );
	}

	public function get_base_url( $url ) {
		if ( substr( $url, 0, 8 ) == 'https://' ) {
			$url = substr( $url, 8 );
		}
		if ( substr( $url, 0, 7 ) == 'http://' ) {
			$url = substr( $url, 7 );
		}
		if ( substr( $url, 0, 4 ) == 'www.' ) {
			$url = substr( $url, 4 );
		}
		if ( strpos( $url, '/' ) !== false ) {
			$explode = explode( '/', $url );
			$url     = $explode['0'];
		}

		return $url;
	}

	public function get_current_user_fullname() {
		$user = wp_get_current_user();

		if ( isset( $user->user_firstname ) and isset( $user->user_lastname ) ) {
			return $user->user_firstname . ' ' . $user->user_lastname;
		} else {
			return $user->data->display_name;
		}
	}

	private function get_domain_info( $domain_name ) {
		$domains = array(
			'google.me'     => array( 'country' => 'Montenegro', 'language' => 'Albanian', 'code' => 'sq' ),
			'google.al'     => array( 'country' => 'Albania', 'language' => 'Albanian', 'code' => 'sq' ),
			'google.com.et' => array( 'country' => 'Ethiopia', 'language' => 'Amharic', 'code' => 'am' ),
			'google.ae'     => array( 'country' => 'United Arab Emirates', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.co.ma'  => array( 'country' => 'Morocco', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.com.sa' => array( 'country' => 'Saudi Arabia', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.so'     => array( 'country' => 'Somalia', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.tn'     => array( 'country' => 'Tunisia', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.com.bh' => array( 'country' => 'Bahrain', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.dj'     => array( 'country' => 'Djibouti', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.dz'     => array( 'country' => 'Algeria', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.com.eg' => array( 'country' => 'Egypt', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.iq'     => array( 'country' => 'Iraq', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.jo'     => array( 'country' => 'Jordan', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.com.kw' => array( 'country' => 'Kuwait', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.com.lb' => array( 'country' => 'Lebanon', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.com.ly' => array( 'country' => 'Libya', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.com.om' => array( 'country' => 'Oman', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.ps'     => array( 'country' => 'Palestine', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.com.qa' => array( 'country' => 'Qatar', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.td'     => array( 'country' => 'Chad', 'language' => 'Arabic', 'code' => 'ar' ),
			'google.am'     => array( 'country' => 'Armenia', 'language' => 'Armenian', 'code' => 'hy' ),
			'google.az'     => array( 'country' => 'Azerbaijan', 'language' => 'Azerbaijani', 'code' => 'az' ),
			'google.by'     => array( 'country' => 'Belarus', 'language' => 'Belarusian', 'code' => 'be' ),
			'google.com.bd' => array( 'country' => 'Bangladesh', 'language' => 'Bengali', 'code' => 'bn' ),
			'google.ba'     => array( 'country' => 'Bosnia and Herzegovina', 'language' => 'Bosnian', 'code' => 'bs' ),
			'google.bg'     => array( 'country' => 'Bulgaria', 'language' => 'Bulgarian', 'code' => 'bg' ),
			'google.mk'     => array( 'country' => 'Macedonia', 'language' => 'Bulgarian', 'code' => 'bg' ),
			'google.com.mm' => array( 'country' => 'Myanmar', 'language' => 'Burmese', 'code' => 'my' ),
			'google.com.hk' => array( 'country' => 'Hong Kong', 'language' => 'Cantonese', 'code' => 'zh' ),
			'google.ad'     => array( 'country' => 'Andorra', 'language' => 'Catalan', 'code' => 'ca' ),
			'google.cat'    => array( 'country' => 'Catalan Countries', 'language' => 'Catalan', 'code' => 'ca' ),
			'google.hr'     => array( 'country' => 'Croatia', 'language' => 'Croatian', 'code' => 'hr' ),
			'google.cz'     => array( 'country' => 'Czech Republic', 'language' => 'Czech', 'code' => 'cs' ),
			'google.dk'     => array( 'country' => 'Denmark', 'language' => 'Danish', 'code' => 'da' ),
			'google.mv'     => array( 'country' => 'Maldives', 'language' => 'Dhivehi', 'code' => 'dv' ),
			'google.sr'     => array( 'country' => 'Suriname', 'language' => 'Dutch', 'code' => 'nl' ),
			'google.be'     => array( 'country' => 'Belgium', 'language' => 'Dutch', 'code' => 'nl' ),
			'google.nl'     => array( 'country' => 'Netherlands', 'language' => 'Dutch', 'code' => 'nl' ),
			'google.bt'     => array( 'country' => 'Bhutan', 'language' => 'Dzongkha', 'code' => 'dz' ),
			'google.com.ag' => array( 'country' => 'Antigua and Barbuda', 'language' => 'English', 'code' => 'en' ),
			'google.fm'     => array(
				'country'  => 'Federated States of Micronesia',
				'language' => 'English',
				'code'     => 'en'
			),
			'google.com.lc' => array( 'country' => 'Saint Lucia', 'language' => 'English', 'code' => 'en' ),
			'google.com.ng' => array( 'country' => 'Nigeria', 'language' => 'English', 'code' => 'en' ),
			'google.com.sb' => array( 'country' => 'Solomon Islands', 'language' => 'English', 'code' => 'en' ),
			'google.sc'     => array( 'country' => 'Seychelles', 'language' => 'English', 'code' => 'en' ),
			'google.com.sg' => array( 'country' => 'Singapore', 'language' => 'English', 'code' => 'en' ),
			'google.to'     => array( 'country' => 'Tonga', 'language' => 'English', 'code' => 'en' ),
			'google.tt'     => array( 'country' => 'Trinidad and Tobago', 'language' => 'English', 'code' => 'en' ),
			'google.co.ug'  => array( 'country' => 'Uganda', 'language' => 'English', 'code' => 'en' ),
			'google.co.uk'  => array( 'country' => 'United Kingdom', 'language' => 'English', 'code' => 'en' ),
			'google.com'    => array( 'country' => 'Worldwide', 'language' => 'English', 'code' => 'en' ),
			'google.vu'     => array( 'country' => 'Vanuatu', 'language' => 'English', 'code' => 'en' ),
			'google.co.zm'  => array( 'country' => 'Zambia', 'language' => 'English', 'code' => 'en' ),
			'google.co.zw'  => array( 'country' => 'Zimbabwe', 'language' => 'English', 'code' => 'en' ),
			'google.ac'     => array( 'country' => 'Ascension Island', 'language' => 'English', 'code' => 'en' ),
			'google.com.ai' => array( 'country' => 'Anguilla', 'language' => 'English', 'code' => 'en' ),
			'google.as'     => array( 'country' => 'American Samoa', 'language' => 'English', 'code' => 'en' ),
			'google.com.au' => array( 'country' => 'Australia', 'language' => 'English', 'code' => 'en' ),
			'google.bs'     => array( 'country' => 'Bahamas', 'language' => 'English', 'code' => 'en' ),
			'google.co.bw'  => array( 'country' => 'Botswana', 'language' => 'English', 'code' => 'en' ),
			'google.com.bz' => array( 'country' => 'Belize', 'language' => 'English', 'code' => 'en' ),
			'google.cc'     => array( 'country' => 'Cocos (Keeling) Islands', 'language' => 'English', 'code' => 'en' ),
			'google.co.ck'  => array( 'country' => 'Cook Islands', 'language' => 'English', 'code' => 'en' ),
			'google.cm'     => array( 'country' => 'Cameroon', 'language' => 'English', 'code' => 'en' ),
			'google.dm'     => array( 'country' => 'Dominica', 'language' => 'English', 'code' => 'en' ),
			'google.com.fj' => array( 'country' => 'Fiji', 'language' => 'English', 'code' => 'en' ),
			'google.com.gh' => array( 'country' => 'Ghana', 'language' => 'English', 'code' => 'en' ),
			'google.com.gi' => array( 'country' => 'Gibraltar', 'language' => 'English', 'code' => 'en' ),
			'google.gm'     => array( 'country' => 'Gambia', 'language' => 'English', 'code' => 'en' ),
			'google.gy'     => array( 'country' => 'Guyana', 'language' => 'English', 'code' => 'en' ),
			'google.ie'     => array( 'country' => 'Ireland', 'language' => 'English', 'code' => 'en' ),
			'google.io'     => array(
				'country'  => 'British Indian Ocean Territory',
				'language' => 'English',
				'code'     => 'en'
			),
			'google.com.jm' => array( 'country' => 'Jamaica', 'language' => 'English', 'code' => 'en' ),
			'google.co.ke'  => array( 'country' => 'Kenya', 'language' => 'English', 'code' => 'en' ),
			'google.ki'     => array( 'country' => 'Kiribati', 'language' => 'English', 'code' => 'en' ),
			'google.co.ls'  => array( 'country' => 'Lesotho', 'language' => 'English', 'code' => 'en' ),
			'google.ms'     => array( 'country' => 'Montserrat', 'language' => 'English', 'code' => 'en' ),
			'google.com.mt' => array( 'country' => 'Malta', 'language' => 'English', 'code' => 'en' ),
			'google.mu'     => array( 'country' => 'Mauritius', 'language' => 'English', 'code' => 'en' ),
			'google.mw'     => array( 'country' => 'Malawi', 'language' => 'English', 'code' => 'en' ),
			'google.com.my' => array( 'country' => 'Malaysia', 'language' => 'English', 'code' => 'en' ),
			'google.com.na' => array( 'country' => 'Namibia', 'language' => 'English', 'code' => 'en' ),
			'google.com.nf' => array( 'country' => 'Norfolk Island', 'language' => 'English', 'code' => 'en' ),
			'google.nr'     => array( 'country' => 'Nauru', 'language' => 'English', 'code' => 'en' ),
			'google.co.nz'  => array( 'country' => 'New Zealand', 'language' => 'English', 'code' => 'en' ),
			'google.com.pg' => array( 'country' => 'Papua New Guinea', 'language' => 'English', 'code' => 'en' ),
			'google.pn'     => array( 'country' => 'Pitcairn Islands', 'language' => 'English', 'code' => 'en' ),
			'google.rw'     => array( 'country' => 'Rwanda', 'language' => 'English', 'code' => 'en' ),
			'google.sh'     => array(
				'country'  => 'Saint Helena, Ascension and Tristan da Cunha',
				'language' => 'English',
				'code'     => 'en'
			),
			'google.com.sl' => array( 'country' => 'Sierra Leone', 'language' => 'English', 'code' => 'en' ),
			'google.com.vc' => array(
				'country'  => 'Saint Vincent and the Grenadines',
				'language' => 'English',
				'code'     => 'en'
			),
			'google.vg'     => array( 'country' => 'British Virgin Islands', 'language' => 'English', 'code' => 'en' ),
			'google.co.vi'  => array(
				'country'  => 'United States Virgin Islands',
				'language' => 'English',
				'code'     => 'en'
			),
			'google.ws'     => array( 'country' => 'Samoa', 'language' => 'English', 'code' => 'en' ),
			'google.co.za'  => array( 'country' => 'South Africa', 'language' => 'English', 'code' => 'en' ),
			'google.com.ph' => array( 'country' => 'Philippines', 'language' => 'Filipino', 'code' => 'fl' ),
			'google.fi'     => array( 'country' => 'Finland', 'language' => 'Finnish', 'code' => 'fi' ),
			'google.bf'     => array( 'country' => 'Burkina Faso', 'language' => 'French', 'code' => 'fr' ),
			'google.cd'     => array(
				'country'  => 'Democratic Republic of the Congo',
				'language' => 'French',
				'code'     => 'fr'
			),
			'google.cg'     => array( 'country' => 'Republic of the Congo', 'language' => 'French', 'code' => 'fr' ),
			'google.ci'     => array( 'country' => 'Ivory Coast', 'language' => 'French', 'code' => 'fr' ),
			'google.ne'     => array( 'country' => 'Niger', 'language' => 'French', 'code' => 'fr' ),
			'google.tg'     => array( 'country' => 'Togo', 'language' => 'French', 'code' => 'fr' ),
			'google.bj'     => array( 'country' => 'Benin', 'language' => 'French', 'code' => 'fr' ),
			'google.ca'     => array( 'country' => 'Canada', 'language' => 'French', 'code' => 'fr' ),
			'google.cf'     => array( 'country' => 'Central African Republic', 'language' => 'French', 'code' => 'fr' ),
			'google.fr'     => array( 'country' => 'France', 'language' => 'French', 'code' => 'fr' ),
			'google.ga'     => array( 'country' => 'Gabon', 'language' => 'French', 'code' => 'fr' ),
			'google.gf'     => array( 'country' => 'French Guiana', 'language' => 'French', 'code' => 'fr' ),
			'google.gg'     => array( 'country' => 'Guernsey', 'language' => 'French', 'code' => 'fr' ),
			'google.gp'     => array( 'country' => 'Guadeloupe', 'language' => 'French', 'code' => 'fr' ),
			'google.ht'     => array( 'country' => 'Haiti', 'language' => 'French', 'code' => 'fr' ),
			'google.je'     => array( 'country' => 'Jersey', 'language' => 'French', 'code' => 'fr' ),
			'google.lu'     => array( 'country' => 'Luxembourg', 'language' => 'French', 'code' => 'fr' ),
			'google.mg'     => array( 'country' => 'Madagascar', 'language' => 'French', 'code' => 'fr' ),
			'google.ml'     => array( 'country' => 'Mali', 'language' => 'French', 'code' => 'fr' ),
			'google.sn'     => array( 'country' => 'Senegal', 'language' => 'French', 'code' => 'fr' ),
			'google.ge'     => array( 'country' => 'Georgia', 'language' => 'Georgian', 'code' => 'ka' ),
			'google.ch'     => array( 'country' => 'Switzerland', 'language' => 'German', 'code' => 'de' ),
			'google.de'     => array( 'country' => 'Germany', 'language' => 'German', 'code' => 'de' ),
			'google.at'     => array( 'country' => 'Austria', 'language' => 'German', 'code' => 'de' ),
			'google.li'     => array( 'country' => 'Liechtenstein', 'language' => 'German', 'code' => 'de' ),
			'google.com.cy' => array( 'country' => 'Cyprus', 'language' => 'Greek', 'code' => 'el' ),
			'google.gr'     => array( 'country' => 'Greece', 'language' => 'Greek', 'code' => 'el' ),
			'google.gl'     => array( 'country' => 'Greenland', 'language' => 'Greenlandic', 'code' => 'kal' ),
			'google.co.il'  => array( 'country' => 'Israel', 'language' => 'Hebrew', 'code' => 'he' ),
			'google.co.in'  => array( 'country' => 'India', 'language' => 'Hindi', 'code' => 'hi' ),
			'google.hu'     => array( 'country' => 'Hungary', 'language' => 'Hungarian', 'code' => 'hu' ),
			'google.is'     => array( 'country' => 'Iceland', 'language' => 'Icelandic', 'code' => 'is' ),
			'google.co.id'  => array( 'country' => 'Indonesia', 'language' => 'Indonesian', 'code' => 'id' ),
			'google.it'     => array( 'country' => 'Italy', 'language' => 'Italian', 'code' => 'it' ),
			'google.sm'     => array( 'country' => 'San Marino', 'language' => 'Italian', 'code' => 'it' ),
			'google.co.jp'  => array( 'country' => 'Japan', 'language' => 'Japanese', 'code' => 'ja' ),
			'google.com.kh' => array( 'country' => 'Cambodia', 'language' => 'Khmer', 'code' => 'km' ),
			'google.bi'     => array( 'country' => 'Burundi', 'language' => 'Kirundi', 'code' => 'rn' ),
			'google.co.kr'  => array( 'country' => 'South Korea', 'language' => 'Korean', 'code' => 'ko' ),
			'google.la'     => array( 'country' => 'Laos', 'language' => 'Lao', 'code' => 'lo' ),
			'google.lv'     => array( 'country' => 'Latvia', 'language' => 'Latvian', 'code' => 'lv' ),
			'google.lt'     => array( 'country' => 'Lithuania', 'language' => 'Lithuanian', 'code' => 'lt' ),
			'google.com.np' => array( 'country' => 'Nepal', 'language' => 'Maithili', 'code' => 'ne' ),
			'google.com.bn' => array( 'country' => 'Brunei', 'language' => 'Malay', 'code' => 'ms' ),
			'google.cn'     => array( 'country' => 'China', 'language' => 'Mandarin', 'code' => 'zh' ),
			'google.com.tw' => array( 'country' => 'Taiwan', 'language' => 'Mandarin', 'code' => 'zh' ),
			'google.co.tz'  => array( 'country' => 'Tanzania', 'language' => 'Mandarin', 'code' => 'tz' ),
			'google.im'     => array( 'country' => 'Isle of Man', 'language' => 'Manx', 'code' => 'gv' ),
			'google.mn'     => array( 'country' => 'Mongolia', 'language' => 'Mongolian', 'code' => 'mn' ),
			'google.nu'     => array( 'country' => 'Niue', 'language' => 'Niuean', 'code' => 'ne' ),
			'google.no'     => array( 'country' => 'Norway', 'language' => 'Norwegian', 'code' => 'no' ),
			'google.com.af' => array( 'country' => 'Afghanistan', 'language' => 'Pashto', 'code' => 'ps' ),
			'google.pl'     => array( 'country' => 'Poland', 'language' => 'Polish', 'code' => 'pl' ),
			'google.co.ao'  => array( 'country' => 'Angola', 'language' => 'Portuguese', 'code' => 'pt' ),
			'google.com.br' => array( 'country' => 'Brazil', 'language' => 'Portuguese', 'code' => 'pt' ),
			'google.cv'     => array( 'country' => 'Cape Verde', 'language' => 'Portuguese', 'code' => 'pt' ),
			'google.co.mz'  => array( 'country' => 'Mozambique', 'language' => 'Portuguese', 'code' => 'pt' ),
			'google.pt'     => array( 'country' => 'Portugal', 'language' => 'Portuguese', 'code' => 'pt' ),
			'google.st'     => array(
				'country'  => 'São Tomé and Príncipe',
				'language' => 'Portuguese',
				'code'     => 'pt'
			),
			'google.tl'     => array( 'country' => 'Timor-Leste', 'language' => 'Portuguese', 'code' => 'pt' ),
			'google.md'     => array( 'country' => 'Moldova', 'language' => 'Romanian', 'code' => 'ro' ),
			'google.ro'     => array( 'country' => 'Romania', 'language' => 'Romanian', 'code' => 'ro' ),
			'google.kg'     => array( 'country' => 'Kyrgyzstan', 'language' => 'Russian', 'code' => 'ru' ),
			'google.kz'     => array( 'country' => 'Kazakhstan', 'language' => 'Russian', 'code' => 'ru' ),
			'google.ru'     => array( 'country' => 'Russia', 'language' => 'Russian', 'code' => 'ru' ),
			'google.rs'     => array( 'country' => 'Serbia', 'language' => 'Serbian', 'code' => 'sr' ),
			'google.lk'     => array( 'country' => 'Sri Lanka', 'language' => 'Sinhala', 'code' => 'si' ),
			'google.sk'     => array( 'country' => 'Slovakia', 'language' => 'Slovak', 'code' => 'sk' ),
			'google.si'     => array( 'country' => 'Slovenia', 'language' => 'Slovene', 'code' => 'sl' ),
			'google.es'     => array( 'country' => 'Spain', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.ni' => array( 'country' => 'Nicaragua', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.pa' => array( 'country' => 'Panama', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.pe' => array( 'country' => 'Peru', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.uy' => array( 'country' => 'Uruguay', 'language' => 'Spanish', 'code' => 'es' ),
			'google.co.ve'  => array( 'country' => 'Venezuela', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.ar' => array( 'country' => 'Argentina', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.bo' => array( 'country' => 'Bolivia', 'language' => 'Spanish', 'code' => 'es' ),
			'google.cl'     => array( 'country' => 'Chile', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.co' => array( 'country' => 'Colombia', 'language' => 'Spanish', 'code' => 'es' ),
			'google.co.cr'  => array( 'country' => 'Costa Rica', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.cu' => array( 'country' => 'Cuba', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.do' => array( 'country' => 'Dominican Republic', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.ec' => array( 'country' => 'Ecuador', 'language' => 'Spanish', 'code' => 'es' ),
			'google.ee'     => array( 'country' => 'Estonia', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.gt' => array( 'country' => 'Guatemala', 'language' => 'Spanish', 'code' => 'es' ),
			'google.hn'     => array( 'country' => 'Honduras', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.mx' => array( 'country' => 'Mexico', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.pr' => array( 'country' => 'Puerto Rico', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.py' => array( 'country' => 'Paraguay', 'language' => 'Spanish', 'code' => 'es' ),
			'google.com.sv' => array( 'country' => 'El Salvador', 'language' => 'Spanish', 'code' => 'es' ),
			'google.se'     => array( 'country' => 'Sweden', 'language' => 'Swedish', 'code' => 'sv' ),
			'google.com.tj' => array( 'country' => 'Tajikistan', 'language' => 'Tajiki', 'code' => 'tj' ),
			'google.co.th'  => array( 'country' => 'Thailand', 'language' => 'Thai', 'code' => 'th' ),
			'google.tk'     => array( 'country' => 'Tokelau', 'language' => 'Tokelauan', 'code' => 'tkl' ),
			'google.com.tr' => array( 'country' => 'Turkey', 'language' => 'Turkish', 'code' => 'tr' ),
			'google.tm'     => array( 'country' => 'Turkmenistan', 'language' => 'Turkmen', 'code' => 'tk' ),
			'google.com.ua' => array( 'country' => 'Ukraine', 'language' => 'Ukrainian', 'code' => 'uk' ),
			'google.com.pk' => array( 'country' => 'Pakistan', 'language' => 'Urdu', 'code' => 'ur' ),
			'google.co.uz'  => array( 'country' => 'Uzbekistan', 'language' => 'Uzbek', 'code' => 'uz' ),
			'google.com.vn' => array( 'country' => 'Vietnam', 'language' => 'Vietnamese', 'code' => 'vi' ),
		);

		if ( isset( $domains[ $domain_name ] ) ) {
			return $domains[ $domain_name ];
		}
	}

	public function get_countries() {
		global $wpdb, $WP_Statistics;

		$result = $wpdb->get_results( "SELECT referred, SUM(hits) as hits, COUNT(*) as visitors FROM {$wpdb->prefix}statistics_visitor WHERE referred != '' AND referred LIKE '%google.%' and referred NOT LIKE '%google.com%' AND referred REGEXP \"^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\" AND `last_counter` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', -365 )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}' GROUP BY referred HAVING visitors > '1' ORDER BY `visitors` DESC" );

		return $result;
	}

	private function get_default_languages( $excludes = array() ) {
		$data = array(
			array(
				'language'                  => 'Spanish',
				'code'                      => 'es',
				'potential_traffic'         => '1706',
				'potential_traffic_percent' => '239%',
				'potential_leads'           => '67',
				'potential_leads_percent'   => '346%',
			),
			array(
				'language'                  => 'German',
				'code'                      => 'de',
				'potential_traffic'         => '1600',
				'potential_traffic_percent' => '218%',
				'potential_leads'           => '52',
				'potential_leads_percent'   => '246%',
			),
			array(
				'language'                  => 'Italian',
				'code'                      => 'it',
				'potential_traffic'         => '1383',
				'potential_traffic_percent' => '179%',
				'potential_leads'           => '37',
				'potential_leads_percent'   => '146%',
			),
			array(
				'language'                  => 'French',
				'code'                      => 'fr',
				'potential_traffic'         => '906',
				'potential_traffic_percent' => '100%',
				'potential_leads'           => '20',
				'potential_leads_percent'   => '53%',
			)
		);

		if ( ! $excludes ) {
			return $data;
		}

		foreach ( $data as $key => $value ) {
			if ( array_search( $value['language'], $excludes ) !== false ) {
				unset( $data[ $key ] );
			}
		}

		return $data;
	}

	public function get_suggestion() {
		$countries  = $this->get_countries();
		$languages  = array();
		$_languages = array();
		$data       = array();

		// Remove first item
		if ( is_array( $countries ) ) {
			unset( $countries[0] );
		}

		foreach ( $countries as $value ) {
			$domain_info = $this->get_domain_info( $this->get_base_url( $value->referred ) );

			if ( isset( $languages[ $domain_info['language'] ] ) ) {

				$hits     = $languages[ $domain_info['language'] ]['hits'];
				$visitors = $languages[ $domain_info['language'] ]['visitors'];

				$languages[ $domain_info['language'] ]['hits']     = $value->hits + $hits;
				$languages[ $domain_info['language'] ]['visitors'] = $value->visitors + $visitors;

			} else {
				$languages[ $domain_info['language'] ] = array(
					'hits'     => $value->hits,
					'visitors' => $value->visitors,
					'language' => $domain_info['language'],
					'code'     => $domain_info['code'],
				);
			}
		}

		// Return default language
		if ( ! $languages ) {
			return $this->get_default_languages();
		}

		// Remove current language
		$current_language_code = $this->get_current_lang_code();
		foreach ( $languages as $key => $value ) {
			if ( $current_language_code and $value['code'] == $current_language_code ) {
				unset( $languages[ $key ] );
			}
		}

		// Slice array
		$languages = array_slice( $languages, 0, 4 );

		$i            = 0;
		$data_rate    = array( 2.4, 2.2, 1.8, 0.8 );
		$traffic_rate = array( 3.4, 3.2, 2.8, 2.0 );
		$leads_rate   = array( 4.5, 3.5, 2.5, 1.5 );

		foreach ( $languages as $key => $value ) {
			$visitor      = (int) ( $value['visitors'] * $data_rate[ $i ] );
			$leads        = $this->percentage( $visitor, 3 ) * $leads_rate[ $i ];
			$_languages[] = $key;

			$data[] = array(
				'language'                  => $key,
				'visitors'                  => $visitor,
				'potential_traffic'         => $visitor * $traffic_rate[ $i ],
				'potential_traffic_percent' => $this->percentage_increase( $visitor, $visitor * $traffic_rate[ $i ] ) . '%',
				'potential_leads'           => $leads,
				'potential_leads_percent'   => $this->percentage_increase( $this->percentage( $visitor, 3 ), $leads ) . '%',
				'hits'                      => $value['hits'],
			);

			$i ++;
		}

		// Add default languages to current languages
		if ( count( $data ) < 2 ) {
			$default_languages = array_slice( $this->get_default_languages( $_languages ), 0, 3 );

			// Remove current language
			foreach ( $default_languages as $key => $value ) {
				if ( $current_language_code and $value['code'] == $current_language_code ) {
					unset( $default_languages[ $key ] );
				}
			}

			$data = array_merge( $data, $default_languages );
		}

		return $data;
	}

	private function get_current_lang_code() {
		$lang = explode( '-', get_bloginfo( "language" ) );

		if ( isset( $lang[0] ) ) {
			return $lang[0];
		}
	}

	private function percentage_increase( $x1, $x2 ) {
		$diff = ( $x2 - $x1 ) / $x1;

		return (int) round( $diff * 100, 2 );
	}

	private function percentage( $x1, $x2 ) {
		$diff = ( $x1 * $x2 ) / 100;

		if ( $diff < 1 ) {
			$diff = 1;
		}

		return (int) round( $diff, 2 );
	}
}