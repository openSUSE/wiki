<?php
/**
 * OpenSUSE Chameleon Skin
 *
 * Follow openSUSE Branding Guidelines
 */

if (!defined( 'MEDIAWIKI' )) {
    die();
}

class SkinChameleon extends SkinTemplate
{
    public $skinname  = 'chameleon';
    public $stylename = 'chameleon';
    public $template  = 'ChameleonTemplate';
    public $useHeadElement = true;

    function initPage(OutputPage $out)
    {
        parent::initPage( $out );
        $out->addMeta( 'viewport', 'width=device-width, initial-scale=1' );
        $out->addStyle( 'https://static.opensuse.org/chameleon/dist/css/chameleon.css' );
        $out->addScriptFile( 'https://static.opensuse.org/chameleon/dist/js/chameleon-no-jquery.js' );
    }

    function setupSkinUserCss(OutputPage $out)
    {
        parent::setupSkinUserCss( $out );
    }
}


class ChameleonTemplate extends BaseTemplate
{
    var $skin;

    function xmlns()
    {
        foreach ($this->data['xhtmlnamespaces'] as $tag => $ns) {
            echo "xmlns:{$tag}=\"{$ns}\" ";
        }
    }

    function execute()
    {
        global $wgRequest, $wgStylePath;
        $this->skin = $skin = $this->data['skin'];
        $action = $wgRequest->getText( 'action' );

        // Build additional attributes for navigation urls
        $nav = $this->data['content_navigation'];

        $xmlID = '';
        foreach ($nav as $section => $links) {
            foreach ($links as $key => $link) {
                if ($section == 'views' && !( isset( $link['primary'] ) && $link['primary'] )) {
                    $link['class'] = rtrim( 'collapsible ' . $link['class'], ' ' );
                }

                $xmlID = isset( $link['id'] ) ? $link['id'] : 'ca-' . $xmlID;
                $nav[$section][$key]['attributes'] =
                    ' id="' . Sanitizer::escapeId( $xmlID ) . '"';
                if ($link['class']) {
                    $nav[$section][$key]['attributes'] .=
                        ' class="' . htmlspecialchars( $link['class'] ) . '"';
                    unset( $nav[$section][$key]['class'] );
                }
                if (isset( $link['tooltiponly'] ) && $link['tooltiponly']) {
                    $nav[$section][$key]['key'] =
                        Linker::tooltip( $xmlID );
                } else {
                    $nav[$section][$key]['key'] =
                        Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( $xmlID ) );
                }
            }
        }
        $this->data['namespace_urls'] = $nav['namespaces'];
        $this->data['view_urls'] = $nav['views'];
        $this->data['action_urls'] = $nav['actions'];
        $this->data['variant_urls'] = $nav['variants'];

        // Reverse horizontally rendered navigation elements
        if ($this->data['rtl']) {
            $this->data['view_urls'] =
                array_reverse( $this->data['view_urls'] );
            $this->data['namespace_urls'] =
                array_reverse( $this->data['namespace_urls'] );
            $this->data['personal_urls'] =
                array_reverse( $this->data['personal_urls'] );
        }

        $this->data['login_url'] = '/ICSLogin/auth-up';
        $this->data['signup_url'] = "https://www.suse.com/selfreg/jsp/createOpenSuseAccount.jsp?login=Sign+up";

        if ($this->data['username']) {
            $user = User::newFromName( $this->data['username'] );
            $this->data['gravatar'] = "https://www.gravatar.com/avatar/" . md5( $user->getEmail() );
        }

        $this->html( 'headelement' );
?>

<!-- Global Navbar -->
<?php include(__DIR__ . '/parts/global-navbar.php'); ?>

<!-- Main Wrap -->
<div class="container-fluid">
	<div class="row flex-xl-nowrap">
		<div class="col-12 col-md-3 col-xl-2 noprint">
			<?php include(__DIR__ . '/parts/sidebar.php'); ?>
		</div><!-- /.col -->
		<div class="col-12 col-md-9 col-xl-8">
			<div id="mw-page-base" class="noprint"></div>
			<div id="mw-head-base" class="noprint"></div>

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

			<!-- content -->
			<main id="content" class="mw-body">
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

			<hr class="mt-5 noprint" />
			<!-- Wiki Footer -->
			<footer class="row noprint" <?php $this->html( 'userlangattributes' ) ?>>
				<div class="col-sm-6 text-muted">
					<?php foreach ($this->getFooterLinks() as $category => $links) : ?>
						<ul id="footer-<?php echo $category ?>" class="list-inline">
							<?php foreach ($links as $link) : ?>
								<li id="footer-<?php echo $category ?>-<?php echo $link ?>" class="list-inline-item"><small><?php $this->html( $link ) ?></small></li>
							<?php endforeach; ?>
						</ul>
					<?php endforeach; ?>
				</div>
				<div class="col-sm-6 text-right">
					<?php $footericons = $this->getFooterIcons("icononly");
					if (count( $footericons ) > 0) : ?>
						<ul id="footer-icons" class="list-inline">
				<?php	      foreach ($footericons as $blockName => $footerIcons) : ?>
							<li id="footer-<?php echo htmlspecialchars( $blockName ); ?>ico" class="list-inline-item">
				<?php	          foreach ($footerIcons as $icon) : ?>
								<?php echo $this->getSkin()->makeFooterIcon( $icon ); ?>

				<?php	          endforeach; ?>
							</li>
				<?php	      endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</footer>

		</div><!-- /.col -->
		<div class="d-none d-xl-block col-xl-2 noprint">
			<aside id="toc-sidebar"></aside>
		</div><!-- /.col -->
	</div><!-- /.row -->
</div><!-- /.container-fluid -->

<!-- Global Footer -->
<?php include(__DIR__ . '/parts/global-footer.php'); ?>
<?php include(__DIR__ . '/parts/login-modal.php'); ?>
<?php $this->printTrail(); ?>

<script>
var _paq = _paq || [];
(function () {
    var u = (("https:" == document.location.protocol) ? "https://beans.opensuse.org/piwik/" : "http://beans.opensuse.org/piwik/");
    _paq.push(['setSiteId', 9]);
    _paq.push(['setTrackerUrl', u + 'piwik.php']);
    _paq.push(['trackPageView']);
    _paq.push(['setDomains', ["*.opensuse.org"]]);
    var d = document,
        g = d.createElement('script'),
        s = d.getElementsByTagName('script')[0];
    g.type = 'text/javascript';
    g.defer = true;
    g.async = true;
    g.src = u + 'piwik.js';
    s.parentNode.insertBefore(g, s);
})();
</script>

</body>
</html>
<?php
    }

    /**
     * Render a series of portals
     *
     * @param $portals array
     */
    private function renderPortals($portals)
    {
        // Force the rendering of the following portals
        if (!isset( $portals['SEARCH'] )) {
            $portals['SEARCH'] = true;
        }
        if (!isset( $portals['TOOLBOX'] )) {
            $portals['TOOLBOX'] = true;
        }
        if (!isset( $portals['LANGUAGES'] )) {
            $portals['LANGUAGES'] = true;
        }
        // Render portals
        foreach ($portals as $name => $content) {
            if ($content === false) {
                continue;
            }

            echo "\n<!-- {$name} -->\n";
            switch ($name) {
                case 'SEARCH':
                    break;
                case 'TOOLBOX':
                    $this->renderPortal( 'tb', $this->getToolbox(), 'toolbox', 'SkinTemplateToolboxEnd' );
                    break;
                case 'LANGUAGES':
                    if ($this->data['language_urls']) {
                        $this->renderPortal( 'lang', $this->data['language_urls'], 'otherlanguages' );
                    }
                    break;
                default:
                    $this->renderPortal( $name, $content );
                    break;
            }
            echo "\n<!-- /{$name} -->\n";
        }
    }

    private function renderPortal($name, $content, $msg = null, $hook = null)
    {
        if ($msg === null) {
            $msg = $name;
        }
        ?>
<section class="portal mb-3" id="<?php echo Sanitizer::escapeId( "p-$name" ) ?>" <?php echo Linker::tooltip( 'p-' . $name ) ?>>
    <h4 class="mb-3"<?php $this->html( 'userlangattributes' ) ?>><?php $msgObj = wfMessage( $msg );
    echo htmlspecialchars( $msgObj->exists() ? $msgObj->text() : $msg ); ?></h4>
    <?php if (is_array( $content )) : ?>
        <ul class="list-unstyled">
            <?php foreach ($content as $key => $val) : ?>
                <?php $val['class'] = 'mb-1' ?>
                <?php echo $this->makeListItem( $key, $val ); ?>
            <?php endforeach; ?>
            <?php
            if ($hook !== null) {
                wfRunHooks( $hook, array( &$this, true ) );
            }
            ?>
        </ul>
    <?php else : ?>
        <?php echo $content; /* Allow raw HTML block to be defined by extensions */ ?>
    <?php endif; ?>
</section>
<?php
    }
}

# vim:expandtab
