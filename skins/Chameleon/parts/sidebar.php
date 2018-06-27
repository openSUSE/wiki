<aside id="sidebar">
	<div class="container-fluid">
		<nav class="d-flex mb-3">
			<!-- Search Form -->
			<form action="<?php $this->text( 'wgScript' ) ?>" id="searchform" class="form-inline">
				<?php echo $this->makeSearchInput( array( 'id' => 'searchInput', 'class' => 'form-control', 'type' => 'search' ) ); ?>
			</form>

			<!-- User Menu -->
			<?php if ($this->data['username'] == null) : ?>

				<!-- Login Menu -->
				<div class="dropdown ml-2">
					<button class="btn btn-secondary" type="button" id="user-menu-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?php echo $this->msg('login') ?>
					</button>
					<div class="dropdown-menu dropdown-menu-right">
						<a class="dropdown-item" href="<?php echo $this->data['signup_url'] ?>"><?php echo $this->msg('createaccount') ?></a>
						<a id="login-modal-toggle" class="dropdown-item" href="#" data-toggle="modal" data-target="#login-modal"><?php echo $this->msg('login') ?></a>
					</div>
				</div><!-- /.dropdown -->

				<!-- Login Modal -->
				<div class="modal fade" id="login-modal" role="dialog" aria-labelledby="login-modal-label" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<form action="<?php echo $this->data['login_url'] ?>" method="post" enctype="application/x-www-form-urlencoded" name="login_form">
								<div class="modal-header">
									<h5 class="modal-title" id="login-modal-label"><?php echo $this->msg('login') ?></h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">

									<input name="url" value="https://<?php echo $_SERVER['SERVER_NAME'] . htmlentities($_SERVER['REQUEST_URI']) ?>" type="hidden">
									<input name="return_to_path" value="<?php echo htmlentities($_SERVER['REQUEST_URI']) ?>" type="hidden">
									<input name="context" value="default" type="hidden"/>
									<input name="proxypath" value="reverse" type="hidden"/>
									<input name="message" value="Please log In" type="hidden"/>

									<div class="form-group">
										<label for="login-username"><?php echo $this->msg('userlogin-yourname') ?></label>
										<input type="text" class="form-control" name="username" value="" id="login-username" autofocus />
									</div>
									<div class="form-group">
										<label for="login-password"><?php echo $this->msg('userlogin-yourpassword') ?></label>
										<input type="password" class="form-control" name="password" value="" id="login-password" />
									</div>

								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $this->msg('cancel') ?></button>
									<button type="submit" class="btn btn-primary"><?php echo $this->msg('login') ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>

			<?php else : ?>
				<div class="dropdown ml-2">
					<button class="btn btn-secondary" type="button" id="user-menu-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<img class="avatar rounded" src="<?php echo $this->data['gravatar'] ?>" width="40" height="40" alt="Avatar" title="<?php echo $this->data['username'] ?>"/>
					</button>
					<div class="dropdown-menu dropdown-menu-right">
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
					</div>
				</div><!-- /.dropdown -->
			<?php endif ?>

			<button id="sidebar-toggle-button" class="btn btn-secondary d-md-none ml-2" type="button" data-toggle="collapse" data-target="#sidebar-content" aria-controls="sidebar-content" aria-expanded="false" aria-label="Toggle navigation">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" width="30" height="30" focusable="false"><title>Menu</title><path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-miterlimit="10" d="M4 7h22M4 15h22M4 23h22"></path></svg>
			</button>

		</nav><!-- /.d-flex -->

		<div id="sidebar-content" class="collapse">
			<?php $this->renderPortals( $this->data['sidebar'] ); ?>
			<section class="portal">
				<h4 class="my-3">Sponsors</h4>
				<?php $arr = array("sponsor_amd.png", 'sponsor_b1-systems.png', 'sponsor_ip-exchange2.png', 'sponsor_heinlein.png'); ?>
				<a class="sponsor-image" href="/Sponsors"><img src="https://static.opensuse.org/themes/bento/images/sponsors/<?php echo $arr[rand(0, count($arr)-1)] ?>" alt="Sponsor" style="max-width: 145px;"/></a>
			</section>
		</div>
	</div><!-- /.container -->
</aside>
