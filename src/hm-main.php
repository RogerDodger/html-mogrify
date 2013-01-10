<?php
namespace HTML\Mogrify;

const TEXT = 1;
const TAG = 2;

const PLAIN = 1;
const REGEX = 2;
const CALLBACK = 3;

function _tokenise($input) {

	$input_length = strlen($input);
	$tokens = array();
	$state = TEXT;
	$in_str = false;
	$token_length = 0;

	for($i = 0; $i < $input_length; $i++) {

		if($state === TEXT) {

			if($input[$i] == '<') {
				if($token_length > 0) {
					$tokens[] = array(
						TEXT, 
						substr($input, $i - $token_length, $token_length),
					);
				}
				$token_length = 0;
				$state = TAG;
			}

		}

		elseif($state === TAG) {

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
					TAG,
					substr($input, $i - $token_length, $token_length + 1),
				);
				$token_length = -1;
				$state = TEXT;
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
