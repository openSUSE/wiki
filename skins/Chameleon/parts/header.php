<!-- Page Header -->
<header id="mw-head" class="my-3 noprint">

<!-- Tabs for talk page and language variants -->
<ul id="namespaces" class="nav nav-tabs"<?php $this->html( 'userlangattributes' ) ?>>
	<?php foreach ($this->data['namespace_urls'] as $link) : ?>
		<li class="nav-item">
			<a class="nav-link <?php echo strpos($link['attributes'], 'selected') ? 'active' : '' ?>" href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?>>
				<?php echo htmlspecialchars( $link['text'] ) ?>
			</a>
		</li>
	<?php endforeach; ?>
	<?php if ($this->data['variant_urls']) : ?>
		<li class="nav-item dropdown">
			<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
				<?php foreach ($this->data['variant_urls'] as $link) : ?>
					<?php if (stripos( $link['attributes'], 'selected' ) !== false) : ?>
						<?php echo htmlspecialchars( $link['text'] ) ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</a>
			<div class="dropdown-menu" <?php $this->html( 'userlangattributes' ) ?>>
				<?php foreach ($this->data['variant_urls'] as $link) : ?>
					<a class="dropdown-item" <?php echo $link['attributes'] ?> href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?>><?php echo htmlspecialchars( $link['text'] ) ?></a>
				<?php endforeach; ?>
			</div>
		</li>
	<?php endif ?>
</ul>

<!-- Page Actions -->
<?php if ($this->data['view_urls']) : ?>
	<div id="actions" class="btn-toolbar d-flex flex-row-reverse" role="toolbar" aria-label="Toolbar with button groups">
		<div class="btn-group btn-group-sm" role="group">
			<?php foreach ($this->data['view_urls'] as $link) : ?>
				<a class="btn btn-secondary" href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?>><?php
					// $link['text'] can be undefined - bug 27764
				if (array_key_exists( 'text', $link )) {
					echo array_key_exists( 'img', $link ) ?  '<img src="' . $link['img'] . '" alt="' . $link['text'] . '" />' : htmlspecialchars( $link['text'] );
				}
					?></a>
			<?php endforeach; ?>
			<?php if ($this->data['action_urls']) : ?>
				<button id="action-dropdown-button" type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
				<div class="dropdown-menu dropdown-menu-right" aria-labelledby="action-dropdown-button">
					<?php foreach ($this->data['action_urls'] as $link) : ?>
						<a class="dropdown-item" href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?>><?php echo htmlspecialchars( $link['text'] ) ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif ?>
		</div>
	</div>
<?php endif; ?>
</header>
<!-- /header -->
