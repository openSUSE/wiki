<sidebar id="sidebar" class="bg-light col-md-4 col-lg-3 col-xl-2 d-sm-none d-md-block">
    <div class="container-fluid">
        <?php $this->renderPortals( $this->data['sidebar'] ); ?>
        <section>
            <h4 class="my-3">Sponsors</h4>
            <?php $arr = array("sponsor_amd.png", 'sponsor_b1-systems.png', 'sponsor_ip-exchange2.png', 'sponsor_heinlein.png'); ?>
            <a class="sponsor-image" href="/Sponsors"><img src="https://static.opensuse.org/themes/bento/images/sponsors/<?php echo $arr[rand(0, count($arr)-1)] ?>" alt="Sponsor" style="max-width: 145px;"/></a>
        </section>
    </div><!-- /.container-fluid -->
</sidebar>
