<aside id="sidebar">
	<div class="container-fluid">
		<nav class="d-flex mb-3">
			<!-- Search Form -->
			<form action="<?php $this->text( 'wgScript' ) ?>" id="searchform" class="form-inline">
				<?php echo $this->makeSearchInput( array( 'id' => 'searchInput', 'class' => 'form-control', 'type' => 'search' ) ); ?>
			</form>

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
