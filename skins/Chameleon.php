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
    }
    function setupSkinUserCss(OutputPage $out)
    {
        global $wgStylePath;
        parent::setupSkinUserCss( $out );
        // Append to the default screen common & print styles...
        $out->addStyle( "$wgStylePath/chameleon/css/app.css", 'screen' );
        $out->addStyle( "$wgStylePath/chameleon-wiki.css", 'screen' );
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

        global $wgVectorUseIconWatch;

		// Build additional attributes for navigation urls
		$nav = $this->data['content_navigation'];

		if ( $wgVectorUseIconWatch ) {
			$mode = $this->getSkin()->getTitle()->userIsWatching() ? 'unwatch' : 'watch';
			if ( isset( $nav['actions'][$mode] ) ) {
				$nav['views'][$mode] = $nav['actions'][$mode];
				$nav['views'][$mode]['class'] = rtrim( 'icon ' . $nav['views'][$mode]['class'], ' ' );
				$nav['views'][$mode]['primary'] = true;
				unset( $nav['actions'][$mode] );
			}
		}

		$xmlID = '';
		foreach ( $nav as $section => $links ) {
			foreach ( $links as $key => $link ) {
				if ( $section == 'views' && !( isset( $link['primary'] ) && $link['primary'] ) ) {
					$link['class'] = rtrim( 'collapsible ' . $link['class'], ' ' );
				}

				$xmlID = isset( $link['id'] ) ? $link['id'] : 'ca-' . $xmlID;
				$nav[$section][$key]['attributes'] =
					' id="' . Sanitizer::escapeId( $xmlID ) . '"';
				if ( $link['class'] ) {
					$nav[$section][$key]['attributes'] .=
						' class="' . htmlspecialchars( $link['class'] ) . '"';
					unset( $nav[$section][$key]['class'] );
				}
				if ( isset( $link['tooltiponly'] ) && $link['tooltiponly'] ) {
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
		if ( $this->data['rtl'] ) {
			$this->data['view_urls'] =
				array_reverse( $this->data['view_urls'] );
			$this->data['namespace_urls'] =
				array_reverse( $this->data['namespace_urls'] );
			$this->data['personal_urls'] =
				array_reverse( $this->data['personal_urls'] );
		}

        $this->html( 'headelement' );
?>

<!-- Global Navbar -->
<nav id="global-navbar" class="navbar navbar-toggleable navbar-inverse bg-inverse">

    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <a class="navbar-brand" href="https://www.opensuse.org/">
        <img src="<?= $wgStylePath ?>/chameleon/images/logo/logo-white.svg" width="48" height="30" class="d-inline-block align-top" alt="openSUSE Logo">
    </a>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="https://software.opensuse.org/"><?= _('Download') ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="https://software.opensuse.org/search"><?= _('Software') ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="https://doc.opensuse.org/"><?= _('Guides') ?></a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="/"><?= _('Wiki') ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="https://forums.opensuse.org/"><?= _('Forums') ?></a>
            </li>
        </ul>
    </div>
</nav>

<!-- Main Wrap -->
<div id="main-wrap" class="main-wrap d-flex">

    <sidebar class="w-20 hidden-md-down">
        <div class="container-fluid">
            <div id="p-logo"><a style="background-image: url(<?php $this->text( 'logopath' ) ?>);" href="<?php echo htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] ) ?>" <?php echo Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) ) ?>></a></div>
            <?php $this->renderPortals( $this->data['sidebar'] ); ?>
            <section>
                <h4 class="my-3">Sponsors</h4>
                <?php $arr = array("sponsor_amd.png", 'sponsor_b1-systems.png', 'sponsor_ip-exchange2.png', 'sponsor_heinlein.png'); ?>
                <a class="sponsor-image" href="/Sponsors"><img src="https://static.opensuse.org/themes/bento/images/sponsors/<?php echo $arr[rand(0, count($arr)-1)] ?>" alt="Sponsor" style="max-width: 145px;"/></a>
            </section>
        </div><!-- /.container-fluid -->
    </sidebar>

    <main>
        <div class="container-fluid">
            
            <div id="mw-page-base" class="noprint"></div>
		    <div id="mw-head-base" class="noprint"></div>

            <!-- Page Header -->
            <header id="mw-head" class="my-3">
                
                <div class="row my-3">
                    <div class="col-lg-4">
                        <!-- Search Form -->
                        <form action="<?php $this->text( 'wgScript' ) ?>" id="searchform" class="form-inline">
                            <div class="input-group">
                                <?php echo $this->makeSearchInput( array( 'id' => 'searchInput', 'class' => 'form-control', 'type' => 'search' ) ); ?>
                            </div>
                        </form>
                    </div><!-- /.col- -->

                    <div class="col-lg-8">
                        <!-- User Menu -->
                        <ul class="nav nav-sm flex-wrap justify-content-lg-end hidden-sm-down"<?php $this->html( 'userlangattributes' ) ?>>
                            <?php
                                foreach( $this->getPersonalTools() as $key => $item ) {
                                    $item['class'] .= ' nav-item';
                                    foreach ($item['links'] as $key => $link) {
                                        $link['class'] .= ' nav-link';
                                        $item['links'][$key] = $link;
                                    }
                                    echo $this->makeListItem( $key, $item );
                                }
                            ?>
                        </ul>
                    </div><!-- /.col- -->
                    
                </div><!-- /.row- -->

                <div class="my-3">
                    <!-- Tabs for talk page and language variants -->
                    <ul id="p-namespaces" class="nav nav-tabs"<?php $this->html( 'userlangattributes' ) ?>>
                        <?php foreach ( $this->data['namespace_urls'] as $link ): ?>
                            <li <?php echo str_replace('class="', 'class="nav-item ', $link['attributes']) ?>>
                                <a class="nav-link <?php echo strpos($link['attributes'], 'selected') ? 'active' : '' ?>" href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?>>
                                    <?php echo htmlspecialchars( $link['text'] ) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                                <?php foreach ( $this->data['variant_urls'] as $link ): ?>
                                    <?php if ( stripos( $link['attributes'], 'selected' ) !== false ): ?>
                                        <?php echo htmlspecialchars( $link['text'] ) ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </a>
                            <div class="dropdown-menu" <?php $this->html( 'userlangattributes' ) ?>>
                                <?php foreach ( $this->data['variant_urls'] as $link ): ?>
                                    <a class="dropdown-item" <?php echo $link['attributes'] ?> href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?>><?php echo htmlspecialchars( $link['text'] ) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Page Actions -->
                <div class="btn-toolbar justify-content-end hidden-sm-down" role="toolbar" aria-label="Toolbar with button groups">
                    <div class="btn-group mr-2" role="group" aria-label="First group">
                        <?php foreach ( $this->data['view_urls'] as $link ): ?>
                            <a class="btn btn-secondary" <?php echo $link['attributes'] ?> href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?>><?php
                                // $link['text'] can be undefined - bug 27764
                                if ( array_key_exists( 'text', $link ) ) {
                                    echo array_key_exists( 'img', $link ) ?  '<img src="' . $link['img'] . '" alt="' . $link['text'] . '" />' : htmlspecialchars( $link['text'] );
                                }
                                ?></a>
                        <?php endforeach; ?>
                    </div>
                    <div class="btn-group mr-2" role="group" aria-label="Second group">
                        <?php foreach ( $this->data['action_urls'] as $link ): ?>
                            <a class="btn btn-secondary" <?php echo $link['attributes'] ?> href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?>><?php echo htmlspecialchars( $link['text'] ) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </header>
            <!-- /header -->

            <!-- content -->
            <div id="content" class="mw-body">
                <a id="top"></a>
                <div id="mw-js-message" style="display:none;"<?php $this->html( 'userlangattributes' ) ?>></div>
                <?php if ( $this->data['sitenotice'] ): ?>
                <!-- sitenotice -->
                <div id="siteNotice"><?php $this->html( 'sitenotice' ) ?></div>
                <!-- /sitenotice -->
                <?php endif; ?>
                <!-- firstHeading -->
                <h1 id="firstHeading" class="firstHeading display-3 mb-5">
                    <span dir="auto"><?php $this->html( 'title' ) ?></span>
                </h1>
                <!-- /firstHeading -->
                <!-- bodyContent -->
                <div id="bodyContent">
                    <?php if ( $this->data['isarticle'] ): ?>
                    <?php endif; ?>
                    <!-- subtitle -->
                    <div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
                    <!-- /subtitle -->
                    <?php if ( $this->data['undelete'] ): ?>
                    <!-- undelete -->
                    <div id="contentSub2"><?php $this->html( 'undelete' ) ?></div>
                    <!-- /undelete -->
                    <?php endif; ?>
                    <?php if( $this->data['newtalk'] ): ?>
                    <!-- newtalk -->
                    <div class="usermessage"><?php $this->html( 'newtalk' )  ?></div>
                    <!-- /newtalk -->
                    <?php endif; ?>
                    <?php if ( $this->data['showjumplinks'] ): ?>
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
                    <?php if ( $this->data['printfooter'] ): ?>
                    <!-- printfooter -->
                    <div class="printfooter">
                    <?php $this->html( 'printfooter' ); ?>
                    </div>
                    <!-- /printfooter -->
                    <?php endif; ?>
                    <?php if ( $this->data['catlinks'] ): ?>
                    <!-- catlinks -->
                    <?php $this->html( 'catlinks' ); ?>
                    <!-- /catlinks -->
                    <?php endif; ?>
                    <?php if ( $this->data['dataAfterContent'] ): ?>
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
            </div>
            <!-- /content -->

            <!-- Wiki Footer -->
            <footer class="row my-5" <?php $this->html( 'userlangattributes' ) ?>>
                <div class="col-sm-6 text-muted">
                    <?php foreach( $this->getFooterLinks() as $category => $links ): ?>
                        <ul id="footer-<?php echo $category ?>" class="list-inline">
                            <?php foreach( $links as $link ): ?>
                                <li id="footer-<?php echo $category ?>-<?php echo $link ?>" class="list-inline-item"><small><?php $this->html( $link ) ?></small></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endforeach; ?>
                </div>
                <div class="col-sm-6 text-right">
                    <?php $footericons = $this->getFooterIcons("icononly");
                    if ( count( $footericons ) > 0 ): ?>
                        <ul id="footer-icons" class="list-inline">
                <?php			foreach ( $footericons as $blockName => $footerIcons ): ?>
                            <li id="footer-<?php echo htmlspecialchars( $blockName ); ?>ico">
                <?php				foreach ( $footerIcons as $icon ): ?>
                                <?php echo $this->getSkin()->makeFooterIcon( $icon ); ?>

                <?php				endforeach; ?>
                            </li>
                <?php			endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </footer>

        </div><!-- /.container -->
    </main>
</div>

<!-- Global Footer -->
<footer class="global-footer m-0"<?php $this->html( 'userlangattributes' ) ?>>
    <div class="container">
        <div class="row">
            <div class="col-6 col-md-3">
                <h6><?= _("Developers") ?></h6>
                <ul class="list-unstyled">
                    <li><a href="https://en.opensuse.org/Portal:Development"><?= _("Documentation") ?></a></li>
                    <li><a href="https://build.opensuse.org/"><?= _("Build service") ?></a></li>
                    <li><a href="https://bugzilla.opensuse.org/">Bugzilla</a></li>
                    <li><a href="https://github.com/openSUSE">Github</a></li>
                    <li><a href="https://features.opensuse.org/">openFATE</a></li>
                    <li><a href="https://susestudio.com/">SUSE Studio</a></li>
                </ul>
            </div><!-- /.col- -->
            <div class="col-6 col-md-3">
                <h6><?= _("Information") ?></h6>
                <ul class="list-unstyled">
                    <li><a href="https://news.opensuse.org/"><?= _("News") ?></a></li>
                    <li><a href="https://doc.opensuse.org/release-notes/"><?= _("Release notes") ?></a></li>
                    <li><a href="https://events.opensuse.org/"><?= _("Events") ?></a></li>
                    <li><a href="http://planet.opensuse.org/"><?= _("Planet") ?></a></li>
                    <li><a href="https://shop.opensuse.org/"><?= _("Shop") ?></a></li>
                </ul>
            </div><!-- /.col- -->
            <div class="col-6 col-md-3">
                <h6><?= _("Community") ?></h6>
                <ul class="list-unstyled">
                    <li><a href="https://forums.opensuse.org/"><?= _("Forums") ?></a></li>
                    <li><a href="https://connect.opensuse.org/">Connect</a></li>
                    <li><a href="https://www.facebook.com/groups/opensuseproject/"><?= _("Facebook group") ?></a></li>
                    <li><a href="https://plus.google.com/communities/115444043324891769569"><?= _("Google+ group") ?></a></li>
                    <li><a href="https://en.opensuse.org/openSUSE:Mailing_lists_subscription"><?= _("Mail lists") ?></a></li>
                    <li><a href="https://en.opensuse.org/openSUSE:IRC_list"><?= _("IRC channels") ?></a></li>
                </ul>
            </div><!-- /.col- -->
            <div class="col-6 col-md-3">
                <h6><?= _("Social Media") ?></h6>
                <ul class="list-unstyled">
                    <li><a href="https://www.facebook.com/en.openSUSE">Facebook</a></li>
                    <li><a href="https://plus.google.com/+openSUSE">Google+</a></li>
                    <li><a href="https://twitter.com/opensuse">Twitter</a></li>
                    <li><a href="https://www.youtube.com/user/opensusetv">YouTube</a></li>
                    <li><a href="https://t.me/opensusenews">Telegram</a></li>
                </ul>
            </div><!-- /.col- -->
        </div><!-- /.row -->

        <p>&copy; 2011&ndash;2017 <?= _("openSUSE contributors") ?></p>
    </div><!-- /.container -->
</footer>

<!-- Load Scripts Manually-->
<!-- Better to load Bootstrap without jQuery, but MediaWiki's jQuery version is too old. -->
<script src="/skins/chameleon/js/app.js"></script>
<script src="/skins/chameleon-wiki.js"></script>

<?php $this->printTrail(); ?>

</body>
</html>
<?php
    }

	/**
	 * Render a series of portals
	 *
	 * @param $portals array
	 */
	private function renderPortals( $portals ) {
		// Force the rendering of the following portals
		if ( !isset( $portals['SEARCH'] ) ) {
			$portals['SEARCH'] = true;
		}
		if ( !isset( $portals['TOOLBOX'] ) ) {
			$portals['TOOLBOX'] = true;
		}
		if ( !isset( $portals['LANGUAGES'] ) ) {
			$portals['LANGUAGES'] = true;
		}
		// Render portals
		foreach ( $portals as $name => $content ) {
			if ( $content === false )
				continue;

			echo "\n<!-- {$name} -->\n";
			switch( $name ) {
				case 'SEARCH':
					break;
				case 'TOOLBOX':
					$this->renderPortal( 'tb', $this->getToolbox(), 'toolbox', 'SkinTemplateToolboxEnd' );
					break;
				case 'LANGUAGES':
					if ( $this->data['language_urls'] ) {
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

	private function renderPortal( $name, $content, $msg = null, $hook = null ) {
		if ( $msg === null ) {
			$msg = $name;
		}
		?>
<div class="portal mb-5" id='<?php echo Sanitizer::escapeId( "p-$name" ) ?>'<?php echo Linker::tooltip( 'p-' . $name ) ?>>
	<h4 class="mb-3"<?php $this->html( 'userlangattributes' ) ?>><?php $msgObj = wfMessage( $msg ); echo htmlspecialchars( $msgObj->exists() ? $msgObj->text() : $msg ); ?></h4>
    <?php if ( is_array( $content ) ): ?>
		<ul class="list-unstyled">
            <?php foreach( $content as $key => $val ): ?>
                <?php $val['class'] = 'mb-2' ?>
			    <?php echo $this->makeListItem( $key, $val ); ?>
            <?php endforeach; ?>
			<?php
            if ( $hook !== null ) {
				wfRunHooks( $hook, array( &$this, true ) );
			}
			?>
		</ul>
    <?php else: ?>
		<?php echo $content; /* Allow raw HTML block to be defined by extensions */ ?>
    <?php endif; ?>
</div>
<?php
	}

}

