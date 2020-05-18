<form action="<?php $this->text( 'wgScript' ) ?>" id="searchform" class="form-inline mr-md-2">
	<div class="input-group">
		<?php echo $this->makeSearchInput( array( 'id' => 'searchInput', 'class' => 'form-control', 'type' => 'search' ) ); ?>
		<div class="input-group-append">
			<button class="btn btn-secondary" type="submit">
				<svg class="icon">
					<use xlink:href="#search-line">
				</svg>
			</button>
		</div>
	</div>
</form>
