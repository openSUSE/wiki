<nav class="navbar navbar-expand-md sticky-top noprint">
    <a class="navbar-brand" href="/">
		<img src="https://static.opensuse.org/favicon.svg" class="d-inline-block align-top" alt="🦎" title="openSUSE" width="30" height="30">
		<span class="l10n" data-msg-id="wiki">Wiki</span>
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-collapse"><svg width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M2.5 11.5A.5.5 0 0 1 3 11h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 3 7h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 3 3h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"></path></svg></button>

    <div id="navbar-collapse" class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <?php $this->renderPortals($this->data['sidebar']); ?>
		</ul>

		<?php include __DIR__ . '/search-form.php' ?>

		<ul id="user-menu" class="navbar-nav">
			<!-- User Menu -->
			<?php if ($this->data['username'] == null) : ?>
				<li class="nav-item">
					<a id="login-modal-toggle" class="nav-link" href="#" data-toggle="modal" data-target="#login-modal" title="<?= $this->msg('login') ?>">
					<svg class="avatar" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.468 12.37C12.758 11.226 11.195 10 8 10s-4.757 1.225-5.468 2.37A6.987 6.987 0 0 0 8 15a6.987 6.987 0 0 0 5.468-2.63z"/>
						<path fill-rule="evenodd" d="M8 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
						<path fill-rule="evenodd" d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8z"/>
					</svg>
						<span class="d-md-none"><?= $this->msg('login') ?></span>
					</a>
				</li>
			<?php else : ?>
				<li class="nav-item dropdown">
					<a class="nav-link" href="#" id="user-dropdown"
					   	role="button" data-toggle="dropdown" aria-haspopup="true"
					   	aria-expanded="false">
						<img class="avatar" src="<?php echo $this->data['gravatar'] ?>"
						 	width="40" height="40" alt="Avatar" title="<?php echo $this->data['username'] ?>"/>
						<span class="ml-1 d-md-none"><?php echo $this->data['username'] ?></span>
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

	<button class="navbar-toggler megamenu-toggler" type="button" data-toggle="collapse" data-target="#megamenu" aria-expanded="true">
		<svg class="bi bi-grid" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
		</svg>
	</button>
</nav>

<div id="megamenu" class="megamenu collapse"></div>
