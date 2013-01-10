<?php
namespace HTML\Mogrify;

require_once 'hm-main.php';

/**
 * Performs substitutions with awareness of HTML elements
 *
 * @author Cameron Thornton <cthor@cpan.org>
 * @copyright Copyright (c) 2013 Cameron Thornton
 */
class Wordfilter
{	
	// Substitution rules
	private $_rules;

	// HTML tags in which text is not mogrified
	private $_ignores;

	public function __construct() 
	{
		$this->_rules = array();
		$this->_ignores = array("pre", "code", "script", "kbd");
	}

	/**
	 * Adds a rule to the Wordfilter
	 *
	 * @param from the thing to change
	 * @param to what to change it to
	 * @param type what type of substitution to use
	 */
	public function addRule($from, $to, $type = PLAIN) 
	{
		$ok = is_string($from) 
			&& ( $type == CALLBACK && is_callable($to) || is_string($to) );

		if( $ok ) {
			$this->_rules[] = array($from, $to, $type);
		}
		else throw new \Exception("Not a valid rule");
	}

	public function clearRules() {
		$this->_rules = array();
	}

	/**
	 * Adds an ignore to the Wordfilter
	 *
	 * @param ignore html element to ignore the contents of
	 */
	public function addIgnore($ignore) {
		if(is_string($ignore) && preg_match("/^[a-zA-Z]+$/", $ignore)) {
			$this->_ignores[] = strtolower($ignore);
		}
		elseif(is_array($ignore)) {
			foreach($ignore as $elem) {
				$this->addIgnore($elem);
			}
		}
		else throw new \Exception("Ignores must be strings containing only ASCII letters");
	}

	public function clearIgnores() {
		$this->_ignores = array();
	}

	public function process($text) {

		$tokens = _tokenise($text);
		$output = '';

		// Array with indices of tag elements and values of how many of these deep we are
		// e.g., (pre => 2, p => 1) says we're inside two <pre>s and one <p>
		$ignore_stack = array();

		// This is safe, since we know that the ignores are only alphabetical characters
		$ignore_re = "`" . "^<(/?)(" . join("|", $this->_ignores) . ")[\\s>]" . "`";

		foreach($tokens as $token) {

			if($token[0] === TAG) {

				if(preg_match($ignore_re, $token[1], $matches)) {

					$tag = $matches[2];

					if($matches[1] === '/') {
						// Closing tag
						if( isset($ignore_stack[$tag]) ) {
							if( --$ignore_stack[$tag] == 0 ) {
								unset($ignore_stack[$tag]);
							}
						}
					}
					else {
						// Opening tag
						if( !isset($ignore_stack[$tag]) ) {
							$ignore_stack[$tag] = 0;
						}
						$ignore_stack[$tag]++;
					}
				}

				$output .= $token[1];
			}
			elseif($token[0] === TEXT) {

				// Check that we aren't inside an ignored tag
				if($ignore_stack == array()) {
					foreach($this->_rules as $rule) {
						if($rule[2] === PLAIN) {
							$token[1] = str_replace($rule[0], $rule[1], $token[1]);
						}
						elseif($rule[2] === REGEX) {
							$token[1] = preg_replace($rule[0], $rule[1], $token[1]);
						}
						elseif($rule[2] === CALLBACK) {
							$token[1] = preg_replace_callback($rule[0], $rule[1], $token[1]);
						}
					}
				}
				$output .= $token[1];
			}

		}

		return $output;
	}

}

?>