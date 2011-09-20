<?php
	/* Alexa Pagerank checker function
	/* Source: http://abcoder.com/php/get-google-page-rank-and-alexa-rank-of-a-domain-using-php/
	*/
function wp_statistics_alexaRank(){
	$get_pagerank_alexa_url = get_option('pagerank_alexa_url');
	if(!$get_pagerank_alexa_url) {
		$domain = get_bloginfo('url');
	} else {
		$domain = get_option('pagerank_alexa_url');
	}

    $remote_url = 'http://data.alexa.com/data?cli=10&dat=snbamz&url='.trim($domain);
    $search_for = '<POPULARITY URL';
    if ($handle = @fopen($remote_url, "r")) {
        while (!feof($handle)) {
            $part .= fread($handle, 100);
            $pos = strpos($part, $search_for);
            if ($pos === false)
            continue;
            else
            break;
        }
        $part .= fread($handle, 100);
        fclose($handle);
    }
    $str = explode($search_for, $part);
    $str = array_shift(explode('"/>', $str[1]));
    $str = explode('TEXT="', $str);
 
    return $str[1];
}
?>