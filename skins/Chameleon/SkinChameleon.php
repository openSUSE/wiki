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

<!-- Navbars -->
<?php include(__DIR__ . '/parts/cross-site-navbar.php'); ?>
<?php include(__DIR__ . '/parts/navbar.php'); ?>

<!-- Main Wrap -->
<div id="main-wrap">
	<div class="container">
		<div class="row">
				<div id="main" class="col-12">
				<?php include(__DIR__ . '/parts/header.php'); ?>
				<?php include(__DIR__ . '/parts/content.php'); ?>
			</div><!-- /.col -->
				<div id="toc-sidebar" class="d-none noprint">
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div><!-- /.main-wrap -->

<?php include(__DIR__ . '/parts/footer.php'); ?>
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
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" <?php $this->html( 'userlangattributes' ) ?>>
		<?php
		 	$msgObj = wfMessage( $msg );
			echo htmlspecialchars( $msgObj->exists() ? $msgObj->text() : $msg );
		?>
	</a>
    <?php if (is_array( $content )) : ?>
        <div class="dropdown-menu">
			<?php
				foreach ($content as $key => $val) {
					$val['class'] = 'dropdown-item';
                 	echo $this->makeLink( $key, $val );
				}
			?>
            <?php
            if ($hook !== null) {
                wfRunHooks( $hook, array( &$this, true ) );
            }
            ?>
        </div>
    <?php endif; ?>
</li>
<?php
    }
}

# vim:expandtab
