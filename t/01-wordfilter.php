<?php
	
require_once 'src/hm-wordfilter.php';
$test_counter = 0;

function is($expected, $result) {
	global $test_counter;

	if($expected === $result) {
		echo "OK " . ++$test_counter;
	}
	else {
		echo "FAIL " . ++$test_counter . "\n";
		echo "\texpected: $expected\n";
		echo "\tresult: $result";
	}
	echo "\n";
}

$in = <<<EOI
ģ<p title='<ጾp blah dog>ģ'>ģdog</p>
<script>
	document.write("Dog eat dog world");
</script>
<p class="dog"><pre><code>Dog eat dog world.</code></pre>dog</p>
EOI;

$wf = new Wordfilter();

//---

$wf->addRule("dog", "cat");

$out = <<<EOI
ģ<p title='<ጾp blah dog>ģ'>ģcat</p>
<script>
	document.write("Dog eat dog world");
</script>
<p class="dog"><pre><code>Dog eat dog world.</code></pre>cat</p>
EOI;

is($out, $wf->process($in));


//---

$wf->clearIgnores();

$out = <<<EOI
ģ<p title='<ጾp blah dog>ģ'>ģcat</p>
<script>
	document.write("Dog eat cat world");
</script>
<p class="dog"><pre><code>Dog eat cat world.</code></pre>cat</p>
EOI;

is($out, $wf->process($in));

//--

$wf->addRule("/dog/i", "cat", HM_REGEX);

$out = <<<EOI
ģ<p title='<ጾp blah dog>ģ'>ģcat</p>
<script>
	document.write("cat eat cat world");
</script>
<p class="dog"><pre><code>cat eat cat world.</code></pre>cat</p>
EOI;

is($out, $wf->process($in));

//---

function dogcat($matches) {
	if( $matches[0] == 'Dog' )
		return "BIG KITTY CAT";
	else return $matches[0];
}

$wf->clearRules();
$wf->addRule("/dog/i", "dogcat", HM_CALLBACK);

$out = <<<EOI
ģ<p title='<ጾp blah dog>ģ'>ģdog</p>
<script>
	document.write("BIG KITTY CAT eat dog world");
</script>
<p class="dog"><pre><code>BIG KITTY CAT eat dog world.</code></pre>dog</p>
EOI;

is($out, $wf->process($in));

//---

?>