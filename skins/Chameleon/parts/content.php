<!-- content -->
<main id="content" class="mw-body mb-5">
	<a id="top"></a>
	<div id="mw-js-message" style="display:none;"<?php $this->html( 'userlangattributes' ) ?>></div>
	<?php if ($this->data['sitenotice']) : ?>
	<!-- sitenotice -->
	<div id="siteNotice"><?php $this->html( 'sitenotice' ) ?></div>
	<!-- /sitenotice -->
	<?php endif; ?>
	<!-- firstHeading -->
	<h1 id="firstHeading" class="firstHeading display-4 my-3">
		<span dir="auto"><?php $this->html( 'title' ) ?></span>
	</h1>
	<!-- /firstHeading -->
	<!-- bodyContent -->
	<div id="bodyContent">
		<?php if ($this->data['isarticle']) : ?>
		<?php endif; ?>
		<!-- subtitle -->
		<div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
		<!-- /subtitle -->
		<?php if ($this->data['undelete']) : ?>
		<!-- undelete -->
		<div id="contentSub2"><?php $this->html( 'undelete' ) ?></div>
		<!-- /undelete -->
		<?php endif; ?>
		<?php if ($this->data['newtalk']) : ?>
		<!-- newtalk -->
		<div class="usermessage"><?php $this->html( 'newtalk' )  ?></div>
		<!-- /newtalk -->
		<?php endif; ?>
		<?php if ($this->data['showjumplinks']) : ?>
		<!-- jumpto -->
		<div id="jump-to-nav" class="mw-jump">
			<?php $this->msg( 'jumpto' ) ?> <a href="#mw-head"><?php $this->msg( 'jumptonavigation' ) ?></a>,
			<a href="#p-search"><?php $this->msg( 'jumptosearch' ) ?></a>
		</div>
		<!-- /jumpto -->
		<?php endif; ?>
		<!-- bodycontent -->
		<?php $this->html( 'bodycontent' ) ?>
		<!-- /bodycontent -->
		<?php if ($this->data['printfooter']) : ?>
		<!-- printfooter -->
		<div class="printfooter d-none">
			<?php $this->html( 'printfooter' ); ?>
		</div>
		<!-- /printfooter -->
		<?php endif; ?>
		<?php if ($this->data['catlinks']) : ?>
		<!-- catlinks -->
		<?php $this->html( 'catlinks' ); ?>
		<!-- /catlinks -->
		<?php endif; ?>
		<?php if ($this->data['dataAfterContent']) : ?>
		<!-- dataAfterContent -->
		<?php $this->html( 'dataAfterContent' ); ?>
		<!-- /dataAfterContent -->
		<?php endif; ?>
		<div class="visualClear"></div>
		<!-- debughtml -->
		<?php $this->html( 'debughtml' ); ?>
		<!-- /debughtml -->
	</div>
	<!-- /bodyContent -->
</main>
<!-- /content -->
