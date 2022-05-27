<?php

use App\Model\Entity\User;
use Cake\Routing\Router;

/**
 * @var User $user
 * @var string $subject
 * @var string $content
 * @var string $resource
 * @var string $appName
 * @var string $dashboard
 */
?>
<p>
	<?= __d("friendsofbabba_core", "Dear {0}", $user->name); ?>
</p>
<p style="font-weight: bold">
	<?= __d("friendsofbabba_core", "You received new notification"); ?> 🔔
</p>
<p style="text-align: justify">
	<?= $content; ?>
</p>
<?php if (isset($resource) && !empty($resource)) : ?>
	<?= $this->element("email/button", [
		'href' => Router::url($dashboard . $resource, true),
		'label' => __d("friendsofbabba_core", "Open Notification Link")
	]); ?>
<?php endif; ?>