<?php

/** @var String $destination */
?>
<script type="text/javascript">
	function redirect() {
		var timeout = 3000;
		setTimeout(function() {
			document.location.href = '<?= $destination; ?>';
		}, timeout);
	};
	document.onload = redirect;
	setTimeout(redirect, 1000);
</script>
<section>
	<h1><?= __d('friendsofbabba_core', "SPID"); ?></h1>
	<p> <?= __d('friendsofbabba_core', "Redirecting to the login page {0}", $destination); ?></p>
</section>