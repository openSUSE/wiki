<footer id="site-footer" class="site-footer noprint"<?php $this->html( 'userlangattributes' ) ?>>
    <div class="container-fluid">
		<div class="row">
			<div class="col-12 col-lg-9">
				<?php foreach ($this->getFooterLinks() as $category => $links) : ?>
					<p>
						<?php foreach ($links as $link) : ?>
							<?php $this->html( $link ) ?>
						<?php endforeach; ?>
					</p>
				<?php endforeach; ?>
				<p>
					&copy; 2001&ndash;<?php echo date('Y') ?> SUSE LLC, &copy; 2005&ndash;<?php echo date('Y') ?> openSUSE Contributors &amp; others.
				</p>
			</div><!-- /.col-* -->
			<div class="col-12 col-lg-3">
				<?php include __DIR__ . '/sponsors.php'; ?>
			</div><!-- /.col-* -->
		</div><!-- /.row -->
    </div><!-- /.container -->
</footer>
