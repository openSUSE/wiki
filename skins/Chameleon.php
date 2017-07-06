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
        $out->addScriptFile( "$wgStylePath/chameleon/js/app.js" );
        $out->addScript(`<script>var _paq = _paq || [];
        (function(){
        var u=(("https:" == document.location.protocol) ? "https://beans.opensuse.org/piwik/" : "http://beans.opensuse.org/piwik/");
        _paq.push(['setSiteId', 9]);
        _paq.push(['setTrackerUrl', u+'piwik.php']);
        _paq.push(['trackPageView']);
        _paq.push([ 'setDomains', ["*.opensuse.org"]]);
        var d=document,
        g=d.createElement('script'),
        s=d.getElementsByTagName('script')[0];
        g.type='text/javascript';
        g.defer=true;
        g.async=true;
        g.src=u+'piwik.js';
        s.parentNode.insertBefore(g,s);
        })();</script>`);
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

        // Suppress warnings to prevent notices about missing indexes in $this->data
        wfSuppressWarnings();

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

    <sidebar class="w-20 hidden-sm-down">
        <div class="container-fluid">
            <div id="p-logo"><a style="background-image: url(<?php $this->text( 'logopath' ) ?>);" href="<?php echo htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] ) ?>" <?php echo Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) ) ?>></a></div>
            <?php $this->renderPortals( $this->data['sidebar'] ); ?>
        </div><!-- /.container-fluid -->
    </sidebar>

    <main>
        <div class="container">
            Main
        </div><!-- /.container -->
    </main>
</div>
        <div id="subheader" class="container_16">
            <div id="breadcrump" class="grid_12 alpha">
                <a href="/" title="Home"><img src="<?php $this->text('stylepath' ) ?>/bento/home_grey.png" width="16" height="16" alt="Home" /> Wiki</a> &gt; <a href="" title=""><?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?></a>
            </div>


        <?php if ($this->data['username'] == null) { ?>
            <div id="login-wrapper" class="grid_4 omega">
        <?php if (strpos($_SERVER["SERVER_NAME"], "stage") !== false) { ?>
                    <a href="https://secure-wwwstage.provo.novell.com/selfreg/jsp/createOpenSuseAccount.jsp?login=Sign+up?>">Sign up</a> | <a id="login-trigger" href="https://loginstage.microfocus.com/nidp/idff/sso?id=12&sid=3&option=credential&sid=3">Login</a>
        <?php } else { ?>           
                    <a href="https://secure-www.novell.com/selfreg/jsp/createOpenSuseAccount.jsp?login=Sign+up">Sign up</a> | <a id="login-trigger" href="https://login.microfocus.com/nidp/app/login?id=28&sid=0&option=credential&sid=0">Login</a>
        <?php } ?>
                <!-- <a href="<?php //echo $this->data['personal_urls'][login][href] ?>">Sign up</a> | <a id="login-trigger" href="#login">Login</a> -->

                <div id="login-form">
            <?php if (strpos($_SERVER["SERVER_NAME"], "stage") !== false) { ?>
                        <form action="https://loginstage.microfocus.com/nidp/idff/sso?sid=0" method="post" enctype="application/x-www-form-urlencoded" name="login_form">
                    <?php } else { ?>
                        <form action="https://login.microfocus.com/nidp/idff/sso?sid=0" method="post" enctype="application/x-www-form-urlencoded" name="login_form">
                    <?php } ?>
            <input name="target" value="http://<?php echo $_SERVER['SERVER_NAME'] . $this->data['personal_urls'][login][href] ?>" type="hidden"/>
                        <input name="context" value="default" type="hidden"/>
                        <input name="proxypath" value="reverse" type="hidden"/>
                        <input name="message" value="Please log In" type="hidden"/>
                        <p><label class="inlined" for="username">Username</label><input type="text" class="inline-text" name="Ecom_User_ID" value="" id="username" /></p>
                        <p><label class="inlined" for="password">Password</label><input type="password" class="inline-text" name="Ecom_Password" value="" id="password" /></p>
                        <p><input type="submit" value="Login" /></p>
                        <p class="slim-footer"><a id="close-login" href="#cancel">Cancel</a></p>
                    </form>
                </div>
            </div>
            <?php } else { ?>

            <div id="local-user-actions" class="grid_4 omega">
                <ul id="pt-personal">
                    <!-- Begin Personal links (Login, etc.) xx-->
            <?php foreach ($this->data['personal_urls'] as $key => $item) { ?>
                    <li id="<?php echo Sanitizer::escapeId( "pt-$key" ) ?>"<?php
                    if ($item['active']) {
?> class="active"<?php
                    } ?>><a href="<?php
                                                                echo htmlspecialchars($item['href']) ?>"<?php echo $skin->tooltipAndAccesskeyAttribs('pt-'.$key) ?><?php
if (!empty($item['class'])) {
?> class="<?php
echo htmlspecialchars($item['class']) ?>"<?php
} ?>><?php
                                    echo htmlspecialchars($item['text']) ?></a></li>
                <?php } ?>
                    <!-- End Personal links (Login, etc.) -->
                </ul>
            </div>
            <?php } ?>

        </div>

        <!-- Start: Main Content Area -->
        <div id="content" class="container_16 content-wrapper">

            <div class="column grid_3 alpha">

               <!-- Begin custom navigation -->
                


                <div id="some_other_content" class="box box-shadow alpha clear-both navigation">
                    <h2 class="box-header"><?php $this->msg('toolbox') ?></h2>
                    <ul class="navigation">
                                <?php if ($this->data['notspecialpage']) {
?><li id="t-whatlinkshere"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href']) ?>"<?php echo $this->skin->tooltipAndAccesskeyAttribs('t-whatlinkshere') ?>><?php $this->msg('whatlinkshere') ?></a></li>
                                    <?php if ($this->data['nav_urls']['recentchangeslinked']) {
?><li id="t-recentchangeslinked"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href']) ?>"<?php echo $this->skin->tooltipAndAccesskeyAttribs('t-recentchangeslinked') ?>><?php $this->msg('recentchangeslinked') ?></a></li>
                <?php }
}
        ?>

                                <?php if (isset($this->data['nav_urls']['trackbacklink'])) { ?>
                        <li id="t-trackbacklink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href']) ?>"><?php echo $this->msg('trackbacklink') ?></a></li>
                                    <?php } ?>
                                <?php if ($this->data['feeds']) { ?>
                        <li id="feedlinks"><?php foreach ($this->data['feeds'] as $key => $feed) {
?><span id="feed-<?php echo htmlspecialchars($key) ?>"><a href="<?php echo htmlspecialchars($feed['href']) ?>"><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;</span><?php
} ?></li>
                                    <?php } ?>
        <?php foreach (array('contributions', 'emailuser', 'upload', 'specialpages') as $special) {
?><?php if ($this->data['nav_urls'][$special]) { ?>
                        <li id="t-<?php echo $special ?>"><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href']) ?>"><?php $this->msg($special) ?></a></li>
                <?php } ?><?php
} ?>
                    </ul>
                </div>

                <div id="some_other_content" class="box box-shadow alpha clear-both navigation">
                    <h2 class="box-header">Sponsors</h2>
                    <?php $arr = array("sponsor_amd.png", 'sponsor_b1-systems.png', 'sponsor_ip-exchange2.png', 'sponsor_heinlein.png'); ?>
                    <a class="sponsor-image" href="/Sponsors"><img src="https://static.opensuse.org/themes/bento/images/sponsors/<?php echo $arr[rand(0, count($arr)-1)] ?>" alt="Sponsor" style="max-width: 145px;"/></a>
                </div>


        <?php if ($this->data['language_urls']) { ?>
                <div id="language_box" class="box box-shadow alpha clear-both navigation">
                    <h2 class="box-header"><?php $this->msg('otherlanguages') ?></h2>
                    <ul class="navigation">
            <?php foreach ($this->data['language_urls'] as $langlink) { ?>
                        <li><a href='<?php echo htmlspecialchars($langlink['href']) ?>'><?php echo $langlink['text'] ?></a></li>
                                <?php } ?>
                    </ul>
                </div>
            <?php } ?>


            </div>


                        <?php if ($this->data['sitenotice']) { ?>
            <div class="grid_13">
                <div class="ui-state-highlight ui-corner-all">
                    <p>
                        <span class="ui-icon ui-icon-info"/>
                        <?php $this->html('sitenotice') ?>
                    </p>
                </div>
            </div>
                            <?php } ?>


            <div id="some-content" class="box box-shadow grid_13 clearfix">
                <!-- box header -->
                <div class="box-header header-tabs">
                    <ul>
                                <?php $check=false;
                                foreach ($this->data['content_actions'] as $key => $tab) {
                                    echo '<li>';
                                    echo'<a href="'.htmlspecialchars($tab['href']).'"';
                                    if ($tab['class']) {
                                        echo ' class="'.htmlspecialchars($tab['class']).'"';
                                    }
                                    # We don't want to give the watch tab an accesskey if the
                                    # page is being edited, because that conflicts with the
                                    # accesskey on the watch checkbox.  We also don't want to
                                    # give the edit tab an accesskey, because that's fairly su-
                                    # perfluous and conflicts with an accesskey (Ctrl-E) often
                                    # used for editing in Safari.
                                    if (in_array( $action, array( 'edit', 'submit' ) )
                                            && in_array( $key, array( 'edit', 'watch', 'unwatch' ))) {
                                        echo $skin->tooltip( "ca-$key" );
                                    } else {
                                        echo $skin->tooltipAndAccesskeyAttribs( "ca-$key" );
                                    }
                                    echo '>'.htmlspecialchars($tab['text']).'</a></li>';
                                } ?>
                    </ul>
                </div>
                <div id="contentSub"><?php $this->html('subtitle') ?></div>
                <!-- End: box header -->


                <a name="top" id="top"></a>

                <div class="alpha omega">
                    <h1><?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?></h1>

                    <h3 id="siteSub">tagline: <?php $this->msg('tagline') ?></h3>
                    <!-- <div id="contentSub"><?php $this->html('subtitle') ?></div> -->
                            <?php if ($this->data['undelete']) { ?>
                    <div id="contentSub2">undelete: <?php $this->html('undelete') ?></div>
                                <?php } ?>
        <?php if ($this->data['newtalk']) { ?>
                    <div class="usermessage">usermessage: <?php $this->html('newtalk')  ?></div>
                                <?php } ?>

                    <!-- Begin Content Area -->
                            <?php $this->html('bodytext') ?>

        <?php if ($this->data['catlinks']) {
            $this->html('catlinks');
} ?>
        <?php if ($this->data['dataAfterContent']) {
            $this->html ('dataAfterContent');
} ?>
                    <!-- End Content Area -->

                </div>
                <br/>

                <div class="box-footer header-tabs">
                    <ul>
                                <?php $check=false;
                                foreach ($this->data['content_actions'] as $key => $tab) {
                                    echo '<li>';
                                    echo'<a href="'.htmlspecialchars($tab['href']).'"';
                                    if ($tab['class']) {
                                        echo ' class="'.htmlspecialchars($tab['class']).'"';
                                    }
                                    # We don't want to give the watch tab an accesskey if the
                                    # page is being edited, because that conflicts with the
                                    # accesskey on the watch checkbox.  We also don't want to
                                    # give the edit tab an accesskey, because that's fairly su-
                                    # perfluous and conflicts with an accesskey (Ctrl-E) often
                                    # used for editing in Safari.
                                    if (in_array( $action, array( 'edit', 'submit' ) )
                                    && in_array( $key, array( 'edit', 'watch', 'unwatch' ))) {
                                        echo $skin->tooltip( "ca-$key" );
                                    } else {
                                        echo $skin->tooltipAndAccesskeyAttribs( "ca-$key" );
                                    }
                                    echo '>'.htmlspecialchars($tab['text']).'</a></li>';
                                } ?>
                    </ul>
                </div>

            </div></div>

        <!-- Note: this clears floating, set in previous elements -->
        <div class="clear"></div>


        <!-- Start: Footer -->
                <?php
                $handle = fopen(dirname( __FILE__ ) . "/bento/includes/footer.html", "rb");
                $content = stream_get_contents($handle);
                fclose($handle);
                if (isset($this->data['lastmod'])) {
                    $content = split('<p>', $content, 2);
                    echo $content[0];
                    echo '<p>';
                    $this->html('viewcount');
                    echo '</p><p>';
                    echo $content[1];
                } else {
                    echo $content;
                }
        ?>
        <!-- End: Footer -->

        <?php $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>
        <?php $this->html('reporttime') ?>

    </body>
</html>
        <?php
        wfRestoreWarnings();
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
<div class="portal" id='<?php echo Sanitizer::escapeId( "p-$name" ) ?>'<?php echo Linker::tooltip( 'p-' . $name ) ?>>
	<h5<?php $this->html( 'userlangattributes' ) ?>><?php $msgObj = wfMessage( $msg ); echo htmlspecialchars( $msgObj->exists() ? $msgObj->text() : $msg ); ?></h5>
	<div class="body">
<?php
		if ( is_array( $content ) ): ?>
		<ul>
<?php
			foreach( $content as $key => $val ): ?>
			<?php echo $this->makeListItem( $key, $val ); ?>

<?php
			endforeach;
			if ( $hook !== null ) {
				wfRunHooks( $hook, array( &$this, true ) );
			}
			?>
		</ul>
<?php
		else: ?>
		<?php echo $content; /* Allow raw HTML block to be defined by extensions */ ?>
<?php
		endif; ?>
	</div>
</div>
<?php
	}

}

