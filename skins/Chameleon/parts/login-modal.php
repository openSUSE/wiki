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
						<input type="text" class="form-control" name="username" value="" id="login-username" />
					</div>
					<div class="form-group">
						<label for="login-password"><?php echo $this->msg('userlogin-yourpassword') ?></label>
						<input type="password" class="form-control" name="password" value="" id="login-password" />
					</div>

				</div>
				<div class="modal-footer">
					<a class="btn btn-link" href="<?php echo $this->data['signup_url'] ?>">
						<?php echo $this->msg('createaccount') ?>
					</a>
					<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $this->msg('cancel') ?></button>
					<button type="submit" class="btn btn-primary"><?php echo $this->msg('login') ?></button>
				</div>
			</form>
		</div>
	</div>
</div>
