<nav class="navbar navbar-expand-md navbar-light bg-light noprint">
    <a class="navbar-brand" href="/">
		<svg class="icon icon-2x mr-2">
			<use xlink:href="#opensuse">
		</svg>
		<span class="l10n" data-msg-id="wiki">Wiki</span>
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#site-menu">
        <span class="navbar-toggler-icon"></span>
	</button>

    <div class="collapse navbar-collapse" id="site-menu">
        <ul class="navbar-nav mr-auto">
            <?php $this->renderPortals($this->data['sidebar']); ?>
		</ul>

		<?php include __DIR__ . '/search-form.php' ?>

		<ul id="user-menu" class="navbar-nav">
			<!-- User Menu -->
			<?php if ($this->data['username'] == null) : ?>
				<li class="nav-item">
					<a class="nav-link" href="<?php echo $this->data['signup_url'] ?>">
						<?php echo $this->msg('createaccount') ?>
					</a>
				</li>
				<li class="nav-item">
					<a id="login-modal-toggle" class="nav-link" href="#" data-toggle="modal" data-target="#login-modal">
						<?php echo $this->msg('login') ?>
					</a>
				</li>
			<?php else : ?>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="user-dropdown"
					   	role="button" data-toggle="dropdown" aria-haspopup="true"
					   	aria-expanded="false">
						<img class="avatar rounded" src="<?php echo $this->data['gravatar'] ?>"
						 	width="40" height="40" alt="Avatar" title="<?php echo $this->data['username'] ?>"/>
						<span class="ml-1"><?php echo $this->data['username'] ?></span>
					</a>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="user-dropdown">
						<?php
							foreach ($this->getPersonalTools() as $key => $item) {
								foreach ($item['links'] as $k => $link) {
									if (isset($link['class'])) {
										$link['class'] .= ' dropdown-item';
									} else {
										$link['class'] = ' dropdown-item';
									}
									echo $this->makeLink( $k, $link );
								}
							}
						?>
					</div><!-- /.dropdown-menu -->
				</li><!-- /.dropdown -->
			<?php endif; ?>
		</ul>
    </div>
</nav>
