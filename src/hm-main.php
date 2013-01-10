<?php

define("HM_TEXT", 1);
define("HM_TAG", 2);

define("HM_PLAIN", 1);
define("HM_REGEX", 2);
define("HM_CALLBACK", 3);

function _hm_tokenise($input) {

	$input_length = strlen($input);
	$tokens = array();
	$state = HM_TEXT;
	$in_str = false;
	$token_length = 0;

	for($i = 0; $i < $input_length; $i++) {

		if($state === HM_TEXT) {

			if($input[$i] == '<') {
				if($token_length > 0) {
					$tokens[] = array(
						$state, 
						substr($input, $i - $token_length, $token_length),
					);
				}
				$token_length = 0;
				$state = HM_TAG;
			}

		}

		elseif($state === HM_TAG) {

			if($in_str !== false) {
				if($input[$i] === $in_str) {
					$in_str = false;
				}
			}
			elseif($input[$i] == '"' || $input[$i] == "'") {
				$in_str = $input[$i];
			}
			elseif($input[$i] == '>') {
				$tokens[] = array(
					$state,
					substr($input, $i - $token_length, $token_length + 1),
				);
				$token_length = -1;
				$state = HM_TEXT;
			}

		}

		$token_length++;
	}

	// Scoop up the last token 
	if($token_length > 0) {
		$tokens[] = array(
			$state,
			substr($input, $input_length - $token_length, $token_length),
		);
	}

	return $tokens;
}

?>
