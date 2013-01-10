HTML::Mogrify
=============

Sometimes you want to do simple text conversion on HTML. Unfortunately, this can easily break the HTML if not done properly.

This module provides a few text conversion features that work their way around this problem.

Wordfilter
----------

Usage:

  require_once "path/to/src/hm-wordfilter.php";

  $wf = new \HTML\Mogrify\Wordfilter();
  $wf->addRule("dog", "cat");
  $wf->addRule("/dog/i", "cat", \HTML\Mogrify\REGEX);
  $wf->addRule("dog", function($matches) {
    //. . .
  }, \HTML\Mogrify\CALLBACK);

  $wf->process($text);

Requires
--------

* PHP 5.3 or later