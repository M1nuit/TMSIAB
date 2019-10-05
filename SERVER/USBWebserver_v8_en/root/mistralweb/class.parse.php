<?php
################################
# file: class.parse.php
# Version: 2.1b
# Date: 14. December 2006
# http://www.tmbase.de (c) 2006
#
# This notice must remain untouched at all times.
# Modifications to the script
# without the owners permission are prohibited.
# All rights reserved to their proper authors.
################################

class parse {
	var $closetagbuffer = false;

	#-----------------------------
	# Security:
	# parses the _REQUEST array (this including _POST, _GET, _COOKIE)
	# removes not allowed html-tags and not required whitespaces
	# update html specialsingns to their htmlentities
	function parse() {
		foreach($_REQUEST as $key => $content) {
			if (!empty($_REQUEST[$key])) {
				$_REQUEST[$key] = strip_tags($_REQUEST[$key]);
				$_REQUEST[$key] = trim($_REQUEST[$key]);
						
				if(gettype($_REQUEST[$key]) == 'string') {
					$_REQUEST[$key] = htmlentities($_REQUEST[$key], ENT_QUOTES);
				}
			}
		}
	}

	function tmtohtml_color($value1, $value2) {
		$this->closetagbuffer .= '</span>';
		return $closetag ."<span style='color:#". $value1 ."'>". $value2;
	}

	function tmtohtml_tag($value, $tagtype) {
		if($tagtype == 'i' || $tagtype == 'b' || $tagtype == 'u') {
			$opentag = '<'. $tagtype .'>';
		} elseif($tagtype == 'n') { #narrow
			$opentag = '<span style=\'letter-spacing:-1px;\'>';
			$tagtype = 'span';
		} elseif($tagtype == 'm') { #default text
			$opentag = '<span style=\'font-weight:normal; font-style:normal; text-decoration:none;\'>';
			$tagtype = 'span';
		} elseif($tagtype == 'g') { #default color
			$opentag = '<span style=\'color:#000;\'>';
			$tagtype = 'span';
		} elseif($tagtype == 't') { #capital text
			$opentag = '<span style=\'text-transform:capitalize;\'>';
			$tagtype = 'span';
		} elseif($tagtype == 'z') { #reset all
			$opentag = '<span style=\'font-weight:normal; font-style:normal; text-decoration:none; color:#000;\'>';
			$tagtype = 'span';
		} else {
			return false;
		}

		$this->closetagbuffer .= '</'. $tagtype .'>';
		return $closetag . $opentag . $value;
	}

	function tmtohtml($text, $removeall = 0) {
		#  prepare text for parsing
		$text = trim($text);
		$text = urldecode($text);
		$this->closetagbuffer = '';

		if($removeall) {
			$preg = array(	'!\$i(.*?)!ie'									=> "",
					'!\$w(.*?)!ie'									=> "",
					'!\$s(.*?)!ie'									=> "",
					'!\$n(.*?)!ie'									=> "",
					'!\$t(.*?)!ie'									=> "",
					'!\$m(.*?)!ie'									=> "",
					'!\$g(.*?)!ie'									=> "",
					'!\$z(.*?)!ie'									=> "",
					'!\$([abcdef0-9].{2}?)(.*?)!ie'							=> "");
		} else {
			$preg = array(	'!\$i(.*?)!ie'									=> "\$this->tmtohtml_tag('\\1', 'i')",
					'!\$w(.*?)!ie'									=> "\$this->tmtohtml_tag('\\1', 'b')",
					'!\$s(.*?)!ie'									=> "\$this->tmtohtml_tag('\\1', 'u')",
					'!\$n(.*?)!ie'									=> "\$this->tmtohtml_tag('\\1', 'n')",
					'!\$t(.*?)!ie'									=> "\$this->tmtohtml_tag('\\1', 't')",
					'!\$m(.*?)!ie'									=> "\$this->tmtohtml_tag('\\1', 'm')",
					'!\$g(.*?)!ie'									=> "\$this->tmtohtml_tag('\\1', 'g')",
					'!\$z(.*?)!ie'									=> "\$this->tmtohtml_tag('\\1', 'z')",
					'!\$([abcdef0-9].{2}?)(.*?)!ie'							=> "\$this->tmtohtml_color('\\1', '\\2')");
		}

		$text = str_replace('>]', '>$z]', $text);				# set default text after nickname for chat messages
		$text = str_replace('<', '&lt;', $text);				# replace < trough html entnitie
		$text = str_replace('>', '&gt;', $text);				# replace > trough html entnitie
		$text = preg_replace(array_keys($preg), array_values($preg), $text);	# change tm code to html/css
		$text = str_replace('$$', '&#36;', $text);				# replace double $ trough html entnitie
		$text = str_replace('$', '', $text);					# remove all left dollar signs
		return $text.$this->closetagbuffer;
	}

	# function by m.adrian, changed for V2
	function getracetime($mwtime) {
		$min 	= floor($mwtime / 60 / 1000);
		$mwtime = $mwtime - $min * 60 * 1000;
		$sek 	= floor($mwtime / 1000);
		$mwtime = $mwtime - $sek * 1000;
		$hsek 	= $mwtime;
		return sprintf('%02d',$min).':'.sprintf('%02d',$sek).':'.sprintf('%03d',$hsek);
	}

	function uptime($mwtime) {
		$days 	= floor($mwtime/86400);
		$hours 	= floor(($mwtime%86400)/3600);
		$min 	= floor((($mwtime%86400)%3600)/60);
		return sprintf("%d d %d h %2d m", $days, $hours, $min);
	}

	function prepareforchat($text) {
		$text 	= utf8_encode($text);
		$text 	= urldecode($text);
		return $text;
	}

	function unicode_to_utf8($str) {
    		$utf8 = '';
		foreach($str as $unicode) {
			if ($unicode < 128) {
				$utf8.= chr($unicode);
			} elseif ($unicode < 2048) {
				$utf8.= chr( 192 +  ( ( $unicode - ( $unicode % 64 ) ) / 64 ) );
				$utf8.= chr( 128 + ( $unicode % 64 ) );
			} else {
                		$utf8.= chr( 224 + ( ( $unicode - ( $unicode % 4096 ) ) / 4096 ) );
				$utf8.= chr( 128 + ( ( ( $unicode % 4096 ) - ( $unicode % 64 ) ) / 64 ) );
				$utf8.= chr( 128 + ( $unicode % 64 ) );
			}
            	}
    		return $utf8;
    	}
}
?>