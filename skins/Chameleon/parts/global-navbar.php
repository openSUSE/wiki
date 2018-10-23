<nav id="global-navbar" class="navbar navbar-expand-sm navbar-dark bg-dark">
    <a class="navbar-brand" href="https://www.opensuse.org/">
        <img src="<?= $wgStylePath ?>/Chameleon/dist/images/logo/logo-white.svg" width="48" height="30" class="d-inline-block align-top" alt="Logo">
    </a>

    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link l10n" data-msg-id="download" href="https://software.opensuse.org/">Download</a>
            </li>
            <li class="nav-item">
                <a class="nav-link l10n" data-msg-id="software" href="https://software.opensuse.org/search">Software</a>
            </li>
            <li class="nav-item">
                <a class="nav-link l10n" data-msg-id="documentation" href="https://doc.opensuse.org/">Documentation</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link l10n" data-msg-id="wiki" data-url-id="wiki-url" href="/">Wiki</a>
            </li>
            <li class="nav-item">
                <a class="nav-link l10n" data-msg-id="forum" data-url-id="forum-url" href="https://forums.opensuse.org/">Forums</a>
            </li>
        </ul>
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
				</div><!-- /.dropdown -->
			<?php endif; ?>
		</ul>
    </div>
</nav>
