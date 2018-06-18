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
			add_action( 'wp_statistics_after_title', array( $this, 'travod_widget' ) );
		}
	}

	public function travod_widget() {
        if ( isset( $_POST['name'] ) and isset( $_POST['email'] ) ) {
		    global $WP_Statistics;
            $languages = array();

            foreach ($this->get_suggestion() as $item) {
                $languages[] = $item['country'];
            }

		    $message = 'Website: ' . get_bloginfo('url') . PHP_EOL;
		    $message .= 'Full Name: ' . $_POST['name'] . PHP_EOL;
		    $message .= 'Email: ' . $_POST['email'] . PHP_EOL;
		    $message .= 'The 4 Languages: ' . implode($languages, ', ') . PHP_EOL;
		    $message .= 'IP Address: ' . $WP_Statistics->get_IP() . PHP_EOL;
		    $message .= 'Timestamp: ' . time() . PHP_EOL;

            $result = wp_mail( 'victor.b@travod.com', 'New Quote from WP-Statistics!', $message );

            // Build the request parameter
            $args = array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                ),
                'body'    => json_encode( array(
                        'website' => get_bloginfo('url'),
                        'full_name' => $_POST['name'],
                        'email' => $_POST['email'],
                        'languages' => implode($languages, ', '),
                        'ip_address' => $WP_Statistics->get_IP(),
                        'timestamp' => time(),
                    )
                )
            );

            // Send data to url
            wp_remote_post( 'https://hooks.zapier.com/hooks/catch/3049993/aqqp46/', $args );

            if($result) {
                // Disable the suggestion
                $WP_Statistics->update_option( 'disable_suggestion_nag', true );

                $link = "<script>window.location = 'https://www.travod.com/thanks/';</script>";
                echo $link;
            }
		}

		$base_url = $this->get_base_url( get_bloginfo( 'url' ) );

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

	public function get_current_username() {
		$user = wp_get_current_user();

		if ( isset( $user->data->display_name ) ) {
			return $user->data->display_name;
		}
	}

	private function get_domain_info( $domian_name ) {
		$domains = array(
			'google.me'     => array( 'country' => 'Montenegro', 'language' => 'Albanian' ),
			'google.al'     => array( 'country' => 'Albania', 'language' => 'Albanian' ),
			'google.com.et' => array( 'country' => 'Ethiopia', 'language' => 'Amharic' ),
			'google.ae'     => array( 'country' => 'United Arab Emirates', 'language' => 'Arabic' ),
			'google.co.ma'  => array( 'country' => 'Morocco', 'language' => 'Arabic' ),
			'google.com.sa' => array( 'country' => 'Saudi Arabia', 'language' => 'Arabic' ),
			'google.so'     => array( 'country' => 'Somalia', 'language' => 'Arabic' ),
			'google.tn'     => array( 'country' => 'Tunisia', 'language' => 'Arabic' ),
			'google.com.bh' => array( 'country' => 'Bahrain', 'language' => 'Arabic' ),
			'google.dj'     => array( 'country' => 'Djibouti', 'language' => 'Arabic' ),
			'google.dz'     => array( 'country' => 'Algeria', 'language' => 'Arabic' ),
			'google.com.eg' => array( 'country' => 'Egypt', 'language' => 'Arabic' ),
			'google.iq'     => array( 'country' => 'Iraq', 'language' => 'Arabic' ),
			'google.jo'     => array( 'country' => 'Jordan', 'language' => 'Arabic' ),
			'google.com.kw' => array( 'country' => 'Kuwait', 'language' => 'Arabic' ),
			'google.com.lb' => array( 'country' => 'Lebanon', 'language' => 'Arabic' ),
			'google.com.ly' => array( 'country' => 'Libya', 'language' => 'Arabic' ),
			'google.com.om' => array( 'country' => 'Oman', 'language' => 'Arabic' ),
			'google.ps'     => array( 'country' => 'Palestine', 'language' => 'Arabic' ),
			'google.com.qa' => array( 'country' => 'Qatar', 'language' => 'Arabic' ),
			'google.td'     => array( 'country' => 'Chad', 'language' => 'Arabic' ),
			'google.am'     => array( 'country' => 'Armenia', 'language' => 'Armenian' ),
			'google.az'     => array( 'country' => 'Azerbaijan', 'language' => 'Azerbaijani' ),
			'google.by'     => array( 'country' => 'Belarus', 'language' => 'Belarusian' ),
			'google.com.bd' => array( 'country' => 'Bangladesh', 'language' => 'Bengali' ),
			'google.ba'     => array( 'country' => 'Bosnia and Herzegovina', 'language' => 'Bosnian' ),
			'google.bg'     => array( 'country' => 'Bulgaria', 'language' => 'Bulgarian' ),
			'google.mk'     => array( 'country' => 'Macedonia', 'language' => 'Bulgarian' ),
			'google.com.mm' => array( 'country' => 'Myanmar', 'language' => 'Burmese' ),
			'google.com.hk' => array( 'country' => 'Hong Kong', 'language' => 'Cantonese' ),
			'google.ad'     => array( 'country' => 'Andorra', 'language' => 'Catalan' ),
			'google.cat'    => array( 'country' => 'Catalan Countries', 'language' => 'Catalan' ),
			'google.hr'     => array( 'country' => 'Croatia', 'language' => 'Croatian' ),
			'google.cz'     => array( 'country' => 'Czech Republic', 'language' => 'Czech' ),
			'google.dk'     => array( 'country' => 'Denmark', 'language' => 'Danish' ),
			'google.mv'     => array( 'country' => 'Maldives', 'language' => 'Dhivehi' ),
			'google.sr'     => array( 'country' => 'Suriname', 'language' => 'Dutch' ),
			'google.be'     => array( 'country' => 'Belgium', 'language' => 'Dutch' ),
			'google.nl'     => array( 'country' => 'Netherlands', 'language' => 'Dutch' ),
			'google.bt'     => array( 'country' => 'Bhutan', 'language' => 'Dzongkha' ),
			'google.com.ag' => array( 'country' => 'Antigua and Barbuda', 'language' => 'English' ),
			'google.fm'     => array( 'country' => 'Federated States of Micronesia', 'language' => 'English' ),
			'google.com.lc' => array( 'country' => 'Saint Lucia', 'language' => 'English' ),
			'google.com.ng' => array( 'country' => 'Nigeria', 'language' => 'English' ),
			'google.com.sb' => array( 'country' => 'Solomon Islands', 'language' => 'English' ),
			'google.sc'     => array( 'country' => 'Seychelles', 'language' => 'English' ),
			'google.com.sg' => array( 'country' => 'Singapore', 'language' => 'English' ),
			'google.to'     => array( 'country' => 'Tonga', 'language' => 'English' ),
			'google.tt'     => array( 'country' => 'Trinidad and Tobago', 'language' => 'English' ),
			'google.co.ug'  => array( 'country' => 'Uganda', 'language' => 'English' ),
			'google.co.uk'  => array( 'country' => 'United Kingdom', 'language' => 'English' ),
			'google.com'    => array( 'country' => 'United States', 'language' => 'English' ),
			'google.vu'     => array( 'country' => 'Vanuatu', 'language' => 'English' ),
			'google.co.zm'  => array( 'country' => 'Zambia', 'language' => 'English' ),
			'google.co.zw'  => array( 'country' => 'Zimbabwe', 'language' => 'English' ),
			'google.com'    => array( 'country' => 'Worldwide', 'language' => 'English' ),
			'google.ac'     => array( 'country' => 'Ascension Island', 'language' => 'English' ),
			'google.com.ai' => array( 'country' => 'Anguilla', 'language' => 'English' ),
			'google.as'     => array( 'country' => 'American Samoa', 'language' => 'English' ),
			'google.com.au' => array( 'country' => 'Australia', 'language' => 'English' ),
			'google.bs'     => array( 'country' => 'Bahamas', 'language' => 'English' ),
			'google.co.bw'  => array( 'country' => 'Botswana', 'language' => 'English' ),
			'google.com.bz' => array( 'country' => 'Belize', 'language' => 'English' ),
			'google.cc'     => array( 'country' => 'Cocos (Keeling) Islands', 'language' => 'English' ),
			'google.co.ck'  => array( 'country' => 'Cook Islands', 'language' => 'English' ),
			'google.cm'     => array( 'country' => 'Cameroon', 'language' => 'English' ),
			'google.dm'     => array( 'country' => 'Dominica', 'language' => 'English' ),
			'google.com.fj' => array( 'country' => 'Fiji', 'language' => 'English' ),
			'google.com.gh' => array( 'country' => 'Ghana', 'language' => 'English' ),
			'google.com.gi' => array( 'country' => 'Gibraltar', 'language' => 'English' ),
			'google.gm'     => array( 'country' => 'Gambia', 'language' => 'English' ),
			'google.gy'     => array( 'country' => 'Guyana', 'language' => 'English' ),
			'google.ie'     => array( 'country' => 'Ireland', 'language' => 'English' ),
			'google.io'     => array( 'country' => 'British Indian Ocean Territory', 'language' => 'English' ),
			'google.com.jm' => array( 'country' => 'Jamaica', 'language' => 'English' ),
			'google.co.ke'  => array( 'country' => 'Kenya', 'language' => 'English' ),
			'google.ki'     => array( 'country' => 'Kiribati', 'language' => 'English' ),
			'google.co.ls'  => array( 'country' => 'Lesotho', 'language' => 'English' ),
			'google.ms'     => array( 'country' => 'Montserrat', 'language' => 'English' ),
			'google.com.mt' => array( 'country' => 'Malta', 'language' => 'English' ),
			'google.mu'     => array( 'country' => 'Mauritius', 'language' => 'English' ),
			'google.mw'     => array( 'country' => 'Malawi', 'language' => 'English' ),
			'google.com.my' => array( 'country' => 'Malaysia', 'language' => 'English' ),
			'google.com.na' => array( 'country' => 'Namibia', 'language' => 'English' ),
			'google.com.nf' => array( 'country' => 'Norfolk Island', 'language' => 'English' ),
			'google.nr'     => array( 'country' => 'Nauru', 'language' => 'English' ),
			'google.co.nz'  => array( 'country' => 'New Zealand', 'language' => 'English' ),
			'google.com.pg' => array( 'country' => 'Papua New Guinea', 'language' => 'English' ),
			'google.pn'     => array( 'country' => 'Pitcairn Islands', 'language' => 'English' ),
			'google.rw'     => array( 'country' => 'Rwanda', 'language' => 'English' ),
			'google.sh'     => array(
				'country'  => 'Saint Helena, Ascension and Tristan da Cunha',
				'language' => 'English'
			),
			'google.com.sl' => array( 'country' => 'Sierra Leone', 'language' => 'English' ),
			'google.com.vc' => array( 'country' => 'Saint Vincent and the Grenadines', 'language' => 'English' ),
			'google.vg'     => array( 'country' => 'British Virgin Islands', 'language' => 'English' ),
			'google.co.vi'  => array( 'country' => 'United States Virgin Islands', 'language' => 'English' ),
			'google.ws'     => array( 'country' => 'Samoa', 'language' => 'English' ),
			'google.co.za'  => array( 'country' => 'South Africa', 'language' => 'English' ),
			'google.com.ph' => array( 'country' => 'Philippines', 'language' => 'Filipino' ),
			'google.fi'     => array( 'country' => 'Finland', 'language' => 'Finnish' ),
			'google.bf'     => array( 'country' => 'Burkina Faso', 'language' => 'French' ),
			'google.cd'     => array( 'country' => 'Democratic Republic of the Congo', 'language' => 'French' ),
			'google.cg'     => array( 'country' => 'Republic of the Congo', 'language' => 'French' ),
			'google.ci'     => array( 'country' => 'Ivory Coast', 'language' => 'French' ),
			'google.ne'     => array( 'country' => 'Niger', 'language' => 'French' ),
			'google.tg'     => array( 'country' => 'Togo', 'language' => 'French' ),
			'google.bj'     => array( 'country' => 'Benin', 'language' => 'French' ),
			'google.ca'     => array( 'country' => 'Canada', 'language' => 'French' ),
			'google.cf'     => array( 'country' => 'Central African Republic', 'language' => 'French' ),
			'google.fr'     => array( 'country' => 'France', 'language' => 'French' ),
			'google.ga'     => array( 'country' => 'Gabon', 'language' => 'French' ),
			'google.gf'     => array( 'country' => 'French Guiana', 'language' => 'French' ),
			'google.gg'     => array( 'country' => 'Guernsey', 'language' => 'French' ),
			'google.gp'     => array( 'country' => 'Guadeloupe', 'language' => 'French' ),
			'google.ht'     => array( 'country' => 'Haiti', 'language' => 'French' ),
			'google.je'     => array( 'country' => 'Jersey', 'language' => 'French' ),
			'google.lu'     => array( 'country' => 'Luxembourg', 'language' => 'French' ),
			'google.mg'     => array( 'country' => 'Madagascar', 'language' => 'French' ),
			'google.ml'     => array( 'country' => 'Mali', 'language' => 'French' ),
			'google.sn'     => array( 'country' => 'Senegal', 'language' => 'French' ),
			'google.ge'     => array( 'country' => 'Georgia', 'language' => 'Georgian' ),
			'google.ch'     => array( 'country' => 'Switzerland', 'language' => 'German' ),
			'google.de'     => array( 'country' => 'Germany', 'language' => 'German' ),
			'google.at'     => array( 'country' => 'Austria', 'language' => 'German' ),
			'google.li'     => array( 'country' => 'Liechtenstein', 'language' => 'German' ),
			'google.com.cy' => array( 'country' => 'Cyprus', 'language' => 'Greek' ),
			'google.gr'     => array( 'country' => 'Greece', 'language' => 'Greek' ),
			'google.gl'     => array( 'country' => 'Greenland', 'language' => 'Greenlandic' ),
			'google.co.il'  => array( 'country' => 'Israel', 'language' => 'Hebrew' ),
			'google.co.in'  => array( 'country' => 'India', 'language' => 'Hindi' ),
			'google.hu'     => array( 'country' => 'Hungary', 'language' => 'Hungarian' ),
			'google.is'     => array( 'country' => 'Iceland', 'language' => 'Icelandic' ),
			'google.co.id'  => array( 'country' => 'Indonesia', 'language' => 'Indonesian' ),
			'google.it'     => array( 'country' => 'Italy', 'language' => 'Italian' ),
			'google.sm'     => array( 'country' => 'San Marino', 'language' => 'Italian' ),
			'google.co.jp'  => array( 'country' => 'Japan', 'language' => 'Japanese' ),
			'google.com.kh' => array( 'country' => 'Cambodia', 'language' => 'Khmer' ),
			'google.bi'     => array( 'country' => 'Burundi', 'language' => 'Kirundi' ),
			'google.co.kr'  => array( 'country' => 'South Korea', 'language' => 'Korean' ),
			'google.la'     => array( 'country' => 'Laos', 'language' => 'Lao' ),
			'google.lv'     => array( 'country' => 'Latvia', 'language' => 'Latvian' ),
			'google.lt'     => array( 'country' => 'Lithuania', 'language' => 'Lithuanian' ),
			'google.com.np' => array( 'country' => 'Nepal', 'language' => 'Maithili' ),
			'google.com.bn' => array( 'country' => 'Brunei', 'language' => 'Malay' ),
			'google.cn'     => array( 'country' => 'China', 'language' => 'Mandarin' ),
			'google.com.tw' => array( 'country' => 'Taiwan', 'language' => 'Mandarin' ),
			'google.co.tz'  => array( 'country' => 'Tanzania', 'language' => 'Mandarin' ),
			'google.im'     => array( 'country' => 'Isle of Man', 'language' => 'Manx' ),
			'google.mn'     => array( 'country' => 'Mongolia', 'language' => 'Mongolian' ),
			'google.nu'     => array( 'country' => 'Niue', 'language' => 'Niuean' ),
			'google.no'     => array( 'country' => 'Norway', 'language' => 'Norwegian' ),
			'google.com.af' => array( 'country' => 'Afghanistan', 'language' => 'Pashto' ),
			'google.pl'     => array( 'country' => 'Poland', 'language' => 'Polish' ),
			'google.co.ao'  => array( 'country' => 'Angola', 'language' => 'Portuguese' ),
			'google.com.br' => array( 'country' => 'Brazil', 'language' => 'Portuguese' ),
			'google.cv'     => array( 'country' => 'Cape Verde', 'language' => 'Portuguese' ),
			'google.co.mz'  => array( 'country' => 'Mozambique', 'language' => 'Portuguese' ),
			'google.pt'     => array( 'country' => 'Portugal', 'language' => 'Portuguese' ),
			'google.st'     => array( 'country' => 'São Tomé and Príncipe', 'language' => 'Portuguese' ),
			'google.tl'     => array( 'country' => 'Timor-Leste', 'language' => 'Portuguese' ),
			'google.md'     => array( 'country' => 'Moldova', 'language' => 'Romanian' ),
			'google.ro'     => array( 'country' => 'Romania', 'language' => 'Romanian' ),
			'google.kg'     => array( 'country' => 'Kyrgyzstan', 'language' => 'Russian' ),
			'google.kz'     => array( 'country' => 'Kazakhstan', 'language' => 'Russian' ),
			'google.ru'     => array( 'country' => 'Russia', 'language' => 'Russian' ),
			'google.rs'     => array( 'country' => 'Serbia', 'language' => 'Serbian' ),
			'google.lk'     => array( 'country' => 'Sri Lanka', 'language' => 'Sinhala' ),
			'google.sk'     => array( 'country' => 'Slovakia', 'language' => 'Slovak' ),
			'google.si'     => array( 'country' => 'Slovenia', 'language' => 'Slovene' ),
			'google.es'     => array( 'country' => 'Spain', 'language' => 'Spanish' ),
			'google.com.ni' => array( 'country' => 'Nicaragua', 'language' => 'Spanish' ),
			'google.com.pa' => array( 'country' => 'Panama', 'language' => 'Spanish' ),
			'google.com.pe' => array( 'country' => 'Peru', 'language' => 'Spanish' ),
			'google.com.uy' => array( 'country' => 'Uruguay', 'language' => 'Spanish' ),
			'google.co.ve'  => array( 'country' => 'Venezuela', 'language' => 'Spanish' ),
			'google.com.ar' => array( 'country' => 'Argentina', 'language' => 'Spanish' ),
			'google.com.bo' => array( 'country' => 'Bolivia', 'language' => 'Spanish' ),
			'google.cl'     => array( 'country' => 'Chile', 'language' => 'Spanish' ),
			'google.com.co' => array( 'country' => 'Colombia', 'language' => 'Spanish' ),
			'google.co.cr'  => array( 'country' => 'Costa Rica', 'language' => 'Spanish' ),
			'google.com.cu' => array( 'country' => 'Cuba', 'language' => 'Spanish' ),
			'google.com.do' => array( 'country' => 'Dominican Republic', 'language' => 'Spanish' ),
			'google.com.ec' => array( 'country' => 'Ecuador', 'language' => 'Spanish' ),
			'google.ee'     => array( 'country' => 'Estonia', 'language' => 'Spanish' ),
			'google.com.gt' => array( 'country' => 'Guatemala', 'language' => 'Spanish' ),
			'google.hn'     => array( 'country' => 'Honduras', 'language' => 'Spanish' ),
			'google.com.mx' => array( 'country' => 'Mexico', 'language' => 'Spanish' ),
			'google.com.pr' => array( 'country' => 'Puerto Rico', 'language' => 'Spanish' ),
			'google.com.py' => array( 'country' => 'Paraguay', 'language' => 'Spanish' ),
			'google.com.sv' => array( 'country' => 'El Salvador', 'language' => 'Spanish' ),
			'google.se'     => array( 'country' => 'Sweden', 'language' => 'Swedish' ),
			'google.com.tj' => array( 'country' => 'Tajikistan', 'language' => 'Tajiki' ),
			'google.co.th'  => array( 'country' => 'Thailand', 'language' => 'Thai' ),
			'google.tk'     => array( 'country' => 'Tokelau', 'language' => 'Tokelauan' ),
			'google.com.tr' => array( 'country' => 'Turkey', 'language' => 'Turkish' ),
			'google.tm'     => array( 'country' => 'Turkmenistan', 'language' => 'Turkmen' ),
			'google.com.ua' => array( 'country' => 'Ukraine', 'language' => 'Ukrainian' ),
			'google.com.pk' => array( 'country' => 'Pakistan', 'language' => 'Urdu' ),
			'google.co.uz'  => array( 'country' => 'Uzbekistan', 'language' => 'Uzbek' ),
			'google.com.vn' => array( 'country' => 'Vietnam', 'language' => 'Vietnamese' ),
		);

		return $domains[ $domian_name ];
	}

	public function get_countries() {
        global $wpdb, $WP_Statistics;

        $result = $wpdb->get_results( "SELECT referred, hits, COUNT(*) as visitors FROM {$wpdb->prefix}statistics_visitor WHERE referred != '' AND referred LIKE '%google%' and referred NOT LIKE '%google.com%' AND referred REGEXP \"^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\" AND `last_counter` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', -365 )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}' GROUP BY referred ORDER BY `visitors` DESC LIMIT 4" );

        return $result;
    }

	public function get_suggestion() {
		$data_rate    = array( 2.4, 2.2, 1.8, 0.8 );
		$traffic_rate = array( 3.4, 3.2, 2.8, 2.0 );
		$leads_rate   = array( 4.5, 3.5, 2.5, 1.5 );
		$countries   = $this->get_countries();

		if($countries) {
            foreach ( $countries as $key => $value ) {
                $country = $this->get_domain_info( $this->get_base_url( $value->referred ) );

                $visitor = (int) ( $value->visitors * $data_rate[ $key ] );
                $leads   = $this->percentage( $visitor, 3 ) * $leads_rate[ $key ];

                $data[] = array(
                    'domain'                    => $value->referred,
                    'country'                   => ( isset( $country['language'] ) ? $country['language'] : '' ),
                    'visitors'                  => $visitor,
                    'potential_traffic'         => $visitor * $traffic_rate[ $key ],
                    'potential_traffic_percent' => $this->percentage_increase( $visitor, $visitor * $traffic_rate[ $key ] ) . '%',
                    'potential_leads'           => $leads,
                    'potential_leads_percent'   => $this->percentage_increase( $this->percentage( $visitor, 3 ), $leads ) . '%',
                    'hits'                      => $value->hits,
                );
            }
        } else {
            $data = array(
                array(
                    'country'                   => 'Spanish',
                    'potential_traffic'         => '1706',
                    'potential_traffic_percent' => '239%',
                    'potential_leads'           => '67',
                    'potential_leads_percent'   => '346%',
                ),
                array(
                    'country'                   => 'German',
                    'potential_traffic'         => '1600',
                    'potential_traffic_percent' => '218%',
                    'potential_leads'           => '52',
                    'potential_leads_percent'   => '246%',
                ),
                array(
                    'country'                   => 'Italian',
                    'potential_traffic'         => '1383',
                    'potential_traffic_percent' => '179%',
                    'potential_leads'           => '37',
                    'potential_leads_percent'   => '146%',
                ),
                array(
                    'country'                   => 'French',
                    'potential_traffic'         => '906',
                    'potential_traffic_percent' => '100%',
                    'potential_leads'           => '20',
                    'potential_leads_percent'   => '53%',
                )
            );
        }

		return $data;
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