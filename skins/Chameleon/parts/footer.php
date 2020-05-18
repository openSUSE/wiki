<footer class="footer noprint"<?php $this->html( 'userlangattributes' ) ?>>
	<div class="container">
		<div class="row">
			<div class="col-lg-9">
				<?php foreach ($this->getFooterLinks() as $category => $links) : ?>
					<?php if ($category === 'places'): ?>
						<ul id="footer-<?= $category ?>" class="list-inline">
							<?php foreach ($links as $link) : ?>
								<li class="list-inline-item"><?php $this->html( $link ) ?></li>
							<?php endforeach; ?>
						</ul>
					<?php else: ?>
						<p id="footer-<?= $category ?>">
							<?php foreach ($links as $link) : ?>
								<?php $this->html( $link ) ?>
							<?php endforeach; ?>
						</p>
					<?php endif; ?>
				<?php endforeach; ?>
				<p id="footer-copyright">
					&copy; 2001&ndash;<?php echo date('Y') ?> SUSE LLC, &copy; 2005&ndash;<?php echo date('Y') ?> openSUSE contributors &amp; others.
				</p>
			</div><!-- /.col-* -->
			<div class="col-lg-3">
				<?php include __DIR__ . '/sponsors.php'; ?>
			</div><!-- /.col-* -->
		</div><!-- /.row -->
	</div><!-- /.container -->
</footer>
