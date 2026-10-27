<div id="sponsors" class="sponsors">
	<p>Sponsors</p>
	<?php
	$arr = array(
		'sponsor_amd.png',
		'sponsor_b1_systems.png',
		'sponsor_corebackbone.png',
		'sponsor_fastly.png',
		'sponsor_heinlein.png',
		'sponsor_marvell.png',
		'sponsor_northc.png',
		'sponsor_rsync.png',
		'sponsor_suse.png',
	);
	?>
	<a href="/Sponsors">
		<img class="sponsor-image" src="https://static.opensuse.org/themes/bento/images/sponsors/<?php echo $arr[rand(0, count($arr)-1)] ?>" alt="Sponsor"/>
	</a>
</div>
