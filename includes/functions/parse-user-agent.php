<?php

/**
 * Parses a user agent string into its important parts
 *
 ****************************************************************************
 * The MIT License
 * 
 * Copyright (c) 2013 Jesse G. Donat
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a 
 * copy of this software and associated documentation files (the 
 * "Software"), to deal in the Software without restriction, including 
 * without limitation the rights to use, copy, modify, merge, publish, 
 * distribute, sublicense, and/or sell copies of the Software, and to 
 * permit persons to whom the Software is furnished to do so, subject to 
 * the following conditions: 
 * 
 * The above copyright notice and this permission notice shall be included 
 * in all copies or substantial portions of the Software. 
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS 
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. 
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY 
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, 
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE 
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 ****************************************************************************
 * 
 * @author Jesse G. Donat <donatj@gmail.com>
 * @link https://github.com/donatj/PhpUserAgent
 * @link http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
 * @param string|null $u_agent
 * @return array an array with browser, version and platform keys
 */
	function parse_user_agent( $u_agent = null ) {
        if( is_null($u_agent) && isset($_SERVER['HTTP_USER_AGENT']) ) $u_agent = $_SERVER['HTTP_USER_AGENT'];

        $platform = null;
        $browser  = null;
        $version  = null;

        $empty = array( 'platform' => $platform, 'browser' => $browser, 'version' => $version );

        if( !$u_agent ) return $empty;

        if( preg_match('/\((.*?)\)/im', $u_agent, $parent_matches) ) {

                preg_match_all('/(?P<platform>Android|CrOS|iPhone|iPad|Linux|Macintosh|Windows(\ Phone\ OS)?|Silk|linux-gnu|BlackBerry|PlayBook|Nintendo\ (WiiU?|3DS)|Xbox)
                        (?:\ [^;]*)?
                        (?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);

                $priority           = array( 'Android', 'Xbox' );
                $result['platform'] = array_unique($result['platform']);
                if( count($result['platform']) > 1 ) {
                        if( $keys = array_intersect($priority, $result['platform']) ) {
                                $platform = reset($keys);
                        } else {
                                $platform = $result['platform'][0];
                        }
                } elseif( isset($result['platform'][0]) ) {
                        $platform = $result['platform'][0];
                }
        }

        if( $platform == 'linux-gnu' ) {
                $platform = 'Linux';
        } elseif( $platform == 'CrOS' ) {
                $platform = 'Chrome OS';
        }

        preg_match_all('%(?P<browser>Camino|Kindle(\ Fire\ Build)?|Firefox|Iceweasel|Safari|MSIE|Trident/.*rv|AppleWebKit|Chrome|IEMobile|Opera|OPR|Silk|Lynx|Midori|Version|Wget|curl|NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
                        (?:\)?;?)
                        (?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
                $u_agent, $result, PREG_PATTERN_ORDER);

		// If nothing has been found, handle cases like: "WordPress/3.7.1; http://wordpress.com" or "Googlebot/2.1 http://www.google.com/bot.html" or "FeedValidator/1.3"
        if( !isset($result['browser'][0]) || !isset($result['version'][0]) ) {
			if( preg_match( "/.*\/.*[; ]?.*/", $u_agent ) ) { 
				$split = explode( "/", $u_agent );
				$result['browser'][0] = $split[0];
				
				unset( $split[0] );
				
				$split = preg_split( "/[; ]/", implode( "/", $split ), 2 );
				$result['version'][0] = $split[0];
				
				// If we didn't actually split on anything, leave the platform blank.
				if( array_key_exists( 1, $split ) ) {
					$platform = trim( $split[1] );
				}
			}
		}
			
        // If nothing matched, return null (to avoid undefined index errors)
        if( !isset($result['browser'][0]) || !isset($result['version'][0]) ) {
                return $empty;
        }

        $browser = $result['browser'][0];
        $version = $result['version'][0];

        $key = 0;
        if( $browser == 'Iceweasel' ) {
                $browser = 'Firefox';
        }elseif( parse_user_agent_find('Playstation Vita', $key, $result['browser']) ) {
                $platform = 'PlayStation Vita';
                $browser  = 'Browser';
        } elseif( parse_user_agent_find('Kindle Fire Build', $key, $result['browser']) || parse_user_agent_find('Silk', $key, $result['browser']) ) {
                $browser  = $result['browser'][$key] == 'Silk' ? 'Silk' : 'Kindle';
                $platform = 'Kindle Fire';
                if( !($version = $result['version'][$key]) || !is_numeric($version[0]) ) {
                        $version = $result['version'][array_search('Version', $result['browser'])];
                }
        } elseif( parse_user_agent_find('NintendoBrowser', $key, $result['browser']) || $platform == 'Nintendo 3DS' ) {
                $browser = 'NintendoBrowser';
                $version = $result['version'][$key];
        } elseif( parse_user_agent_find('Kindle', $key, $result['browser']) ) {
                $browser  = $result['browser'][$key];
                $platform = 'Kindle';
                $version  = $result['version'][$key];
        } elseif( parse_user_agent_find('OPR', $key, $result['browser']) ) {
                $browser = 'Opera Next';
                $version = $result['version'][$key];
        } elseif( parse_user_agent_find('Opera', $key, $result['browser']) ) {
                $browser = 'Opera';
                parse_user_agent_find('Version', $key, $result['browser']);
                $version = $result['version'][$key];
        } elseif( parse_user_agent_find('Midori', $key, $result['browser']) ) {
                $browser = 'Midori';
                $version = $result['version'][$key]; 
        } elseif( $browser == 'AppleWebKit' ) {
                if( ($platform == 'Android' && !($key = 0)) || parse_user_agent_find('Chrome', $key, $result['browser']) ) {
                        $browser = 'Chrome';
                } elseif( $platform == 'BlackBerry' || $platform == 'PlayBook' ) {
                        $browser = 'BlackBerry Browser';
                } elseif( parse_user_agent_find('Safari', $key, $result['browser']) ) {
                        $browser = 'Safari';
                }

                parse_user_agent_find('Version', $key, $result['browser']);

                $version = $result['version'][$key];
        } elseif( $browser == 'MSIE' || strpos($browser, 'Trident') !== false ) {
                if( parse_user_agent_find('IEMobile', $key, $result['browser']) ) {
                        $browser = 'IEMobile';
                } else {
                        $browser = 'MSIE';
                        $key     = 0;
                }
                $version = $result['version'][$key];
        } elseif( $key = array_search('playstation 3', array_map('strtolower', $result['browser'])) === 0 ) {
                $platform = 'PlayStation 3';
                $browser  = 'NetFront';
        }

        return array( 'platform' => $platform, 'browser' => $browser, 'version' => $version );

	}
	
	function parse_user_agent_find( $search, &$key, $browser ) {
	
		$xkey = array_search(strtolower($search),array_map('strtolower',$browser));
		
		if( $xkey !== false ) {
			$key = $xkey;
			
			return true;
		}
		
		return false;
	}