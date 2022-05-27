<?php

use Cake\Routing\Router;

/**
 * @var string $user
 * @var string $newPassword
 * @var string $dashboard
 */
?>
<p> <?= __d("friendsofbabba_core", "Dear {0}", $user); ?></p>
<p>
	<?= __d("friendsofbabba_core", "Your password has been changed"); ?> 🔑
</p>
<p style="font-size: 26px; font-weight: bold; font-family: monospace; color: #505050; text-align: center">
	<?= $newPassword; ?>
</p>
<?= $this->element("email/button", [
	'href' => Router::url($dashboard, true),
	'label' => __d("friendsofbabba_core", "Login")
]); ?>