<?php
	/* Google Pagerank checker function
	/* Source: http://abcoder.com/php/get-google-page-rank-and-alexa-rank-of-a-domain-using-php/
	*/
function wp_statistics_google_page_rank() {
	$get_pagerank_google_url = get_option('pagerank_google_url');
	if(!$get_pagerank_google_url) {
		$url = get_bloginfo('url');
	} else {
		$url = get_option('pagerank_google_url');
	}

    if (strlen(trim($url))>0) {
        $_url = eregi("http://",$url)? $url:"http://".$url;
        $pagerank = trim(wp_statistics_GooglePageRank($_url));
        if (empty($pagerank)) $pagerank = 0;
        return (int)($pagerank);
    }
    return 0;
}
 
function wp_statistics_GooglePageRank($url) {
    $fp = fsockopen("toolbarqueries.google.com", 80, $errno, $errstr, 30);
    if (!$fp) {
        echo "$errstr ($errno)<br />\n";
        } else {
        $out = "GET /search?client=navclient-auto&ch=".wp_statistics_CheckHash(wp_statistics_HashURL($url))."&features=Rank&q=info:".$url."&num=100&filter=0 HTTP/1.1\r\n";
        $out .= "Host: toolbarqueries.google.com\r\n";
        $out .= "User-Agent: Mozilla/4.0 (compatible; GoogleToolbar 2.0.114-big; Windows XP 5.1)\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
 
        while (!feof($fp)) {
            $data = fgets($fp, 128);
            $pos = strpos($data, "Rank_");
        if($pos === false){} else{
                $pagerank = substr($data, $pos + 9);
            }
        }
        fclose($fp);
        return $pagerank;
    }
}
 
function wp_statistics_StrToNum($Str, $Check, $Magic) {
    $Int32Unit = 4294967296; // 2^32
    $length = strlen($Str);
    for ($i = 0; $i < $length; $i++) {
        $Check *= $Magic;
        if ($Check >= $Int32Unit) {
            $Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
            $Check = ($Check < -2147483648)? ($Check + $Int32Unit) : $Check;
        }
        $Check += ord($Str{$i});
    }
    return $Check;
}
 
function wp_statistics_HashURL($String) {
    $Check1 = wp_statistics_StrToNum($String, 0x1505, 0x21);
    $Check2 = wp_statistics_StrToNum($String, 0, 0x1003F);
    $Check1 >>= 2;
    $Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
    $Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
    $Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);
    $T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) << 2 ) | ($Check2 & 0xF0F );
    $T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );
    return ($T1 | $T2);
}
 
function wp_statistics_CheckHash($Hashnum) {
    $CheckByte = 0;
    $Flag = 0;
    $HashStr = sprintf('%u', $Hashnum) ;
    $length = strlen($HashStr);
    for ($i = $length - 1; $i >= 0; $i --) {
        $Re = $HashStr{$i};
        if (1 === ($Flag % 2)) {
            $Re += $Re;
            $Re = (int)($Re / 10) + ($Re % 10);
        }
        $CheckByte += $Re;
        $Flag ++;
    }
    $CheckByte %= 10;
    if (0!== $CheckByte) {
        $CheckByte = 10 - $CheckByte;
        if (1 === ($Flag % 2) ) {
            if (1 === ($CheckByte % 2)) {
                $CheckByte += 9;
            }
            $CheckByte >>= 1;
        }
    }
    return '7'.$CheckByte.$HashStr;
}
?>