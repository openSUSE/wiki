<?php
/**
 * OpenSUSE bento skin
 */

if( !defined( 'MEDIAWIKI' ) ) die();

class SkinBento extends SkinTemplate {
    function initPage( OutputPage $out ) {
        parent::initPage( $out );
        $this->skinname  = 'bento';
        $this->stylename = 'bento';
        $this->template  = 'BentoTemplate';

    }
    function setupSkinUserCss( OutputPage $out ) {
        parent::setupSkinUserCss( $out );
        // Append to the default screen common & print styles...
        $out->addStyle( 'https://static.opensuse.org/themes/bento/css/style.css', 'screen' );
    }
}

class BentoTemplate extends QuickTemplate {
    var $skin;
    function execute() {
        global $wgRequest;
        $this->skin = $skin = $this->data['skin'];
        $action = $wgRequest->getText( 'action' );

        // Suppress warnings to prevent notices about missing indexes in $this->data
        wfSuppressWarnings();
        ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="<?php $this->text('xhtmldefaultnamespace') ?>" <?php foreach($this->data['xhtmlnamespaces'] as $tag => $ns) { ?>xmlns:<?php echo "{$tag}=\"{$ns}\" ";
        } ?>xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
    <head>
        <meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
        <?php $this->html('headlinks') ?>
        <title><?php $this->text('pagetitle') ?></title>
        <?php $this->html('csslinks') ?>
        <link rel="stylesheet" href="/skins/bento/css_local/style.css" type="text/css" media="screen" />
        <!--[if lt IE 7]>
        <meta http-equiv="imagetoolbar" content="no" />
        <![endif]-->
        <?php print Skin::makeGlobalVariablesScript( $this->data ); ?>
        <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"><!-- wikibits js --></script>
        <script type="<?php $this->text('jsmimetype') ?>" src="https://static.opensuse.org/stage/themes/bento/js/jquery.js"></script>
        <script type="<?php $this->text('jsmimetype') ?>" src="https://static.opensuse.org/stage/themes/bento/js/script.js"></script>
        <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath') ?>/bento/js_local/script.js"></script>

        <?php $avail_bento_langs = array("cs", "de", "el", "en", "es", "fi", "fr", "hu", "it", "ja", "nl", "pl", "pt", "ru", "sv", "tr", "vi", "zh_tw");
           $lang = "en_US";
           $wiki_lang = substr($_SERVER['SERVER_NAME'], 0, strpos($_SERVER['SERVER_NAME'], '.') );
           $wiki_lang = str_replace( 'stage', '', $wiki_lang );
           $wiki_lang = str_replace( 'test', '', $wiki_lang );
           $wiki_lang = str_replace( 'wiki', '', $wiki_lang );
           $wiki_lang = str_replace( 'cz', 'cs', $wiki_lang );
           $wiki_lang = str_replace( 'ch_tw', 'ch_TW', $wiki_lang );
           if ( in_array( $wiki_lang , $avail_bento_langs ) ) { $lang = $wiki_lang; }
        ?>
        <script type="<?php $this->text('jsmimetype') ?>" src="https://static.opensuse.org/themes/bento/js/l10n/global-navigation-data-<?php echo $lang; ?>.js"></script>
        <script type="<?php $this->text('jsmimetype') ?>" src="https://static.opensuse.org/themes/bento/js/global-navigation.js"></script>

        <link rel="icon" type="image/png" href="http://static.opensuse.org/themes/bento/images/favicon.png" />

        <!-- Head Scripts -->
                <?php $this->html('headscripts') ?>
                <?php if($this->data['jsvarurl']) { ?><script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('jsvarurl') ?>"><!-- site js --></script><?php }?>
                <?php if($this->data['pagecss']) { ?> <style type="text/css"><?php $this->html('pagecss') ?></style><?php }?>
                <?php if($this->data['usercss']) { ?><style type="text/css"><?php $this->html('usercss') ?></style><?php }?>
                <?php if($this->data['userjs']) { ?><script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs' ) ?>"></script><?php }?>
                <?php if($this->data['userjsprev']) { ?><script type="<?php $this->text('jsmimetype') ?>"><?php $this->html('userjsprev') ?></script><?php }?>
        <?php if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>

        <script type="text/javascript">
          var _gaq = _gaq || [];
          _gaq.push(['_setAccount', 'UA-3367212-3']);
          _gaq.push(['_setDomainName', '.opensuse.org']);
          _gaq.push(['_trackPageview']);
          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();
        </script>

    </head>

    <body<?php if($this->data['body_ondblclick']) { ?> ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
        <?php if($this->data['body_onload']) { ?> onload="<?php $this->text('body_onload') ?>"<?php } ?>
                                                       class="mediawiki <?php $this->text('dir') ?> <?php $this->text('pageclass') ?> <?php $this->text('skinnameclass') ?>">

        <!-- Start: Header -->
                <?php
                $handle = fopen(dirname( __FILE__ ) . "/bento/includes/header.html","rb");
                $content = stream_get_contents($handle);
                fclose($handle);
                $lastsearch = "Search";
                if( isset( $this->data['search'] ) && ( $this->data['search'] != "" ) ) {
                    $lastsearch = $this->data['search'];
                }
                $search_form = '<input type="text" name="search" id="search" ' .
                        $this->skin->tooltipAndAccesskey('search') .
                        ' value="' . $lastsearch . '" ' .
                        "onFocus='this.value = \"\"'" . '/>';
                $search_button = '<input type="submit" name="go" class="hidden" ' .
                        ' value="Search" ' .
                        $this->skin->tooltipAndAccesskey( 'search-go' ) .
                        '/>';
                $content = str_replace( array(
                        '<input type="text" name="q" value="search" id="search" />' ,
                        '<input type="submit" value="Search" class="hidden" accesskey="" />'
                        ), array ( $search_form, $search_button ), $content );
                echo $content;
        ?>
        <!-- End: Header -->

        <div id="subheader" class="container_16">
            <div id="breadcrump" class="grid_12 alpha">
                <a href="/" title="Home"><img src="<?php $this->text('stylepath' ) ?>/bento/home_grey.png" width="16" height="16" alt="Home" /> Wiki</a> &gt; <a href="" title=""><?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?></a>
            </div>


        <?php if( $this->data['username'] == NULL ) { ?>
            <div id="login-wrapper" class="grid_4 omega">
                <a href="<?php echo $this->data['personal_urls'][login][href] ?>">Sign up</a> | <a id="login-trigger" href="#login">Login</a>

                <div id="login-form">
                    <form action="https://<?php echo $_SERVER['SERVER_NAME'] ?>/ICSLogin/auth-up" method="post" enctype="application/x-www-form-urlencoded" id="login_form">
                        <input name="url" value="http://<?php echo $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ?>" type="hidden"/>
                        <input name="context" value="default" type="hidden"/>
                        <input name="proxypath" value="reverse" type="hidden"/>
                        <input name="message" value="Please log In" type="hidden"/>
                        <p><label class="inlined" for="username">Username</label><input type="text" class="inline-text" name="username" value="" id="username" /></p>
                        <p><label class="inlined" for="password">Password</label><input type="password" class="inline-text" name="password" value="" id="password" /></p>

                        <p><input type="submit" value="Login" /></p>
                        <p class="slim-footer"><a id="close-login" href="#cancel">Cancel</a></p>
                    </form>
                </div>
            </div>
            <?php } else { ?>

            <div id="local-user-actions" class="grid_4 omega">
                <ul id="pt-personal">
                    <!-- Begin Personal links (Login, etc.) xx-->
            <?php foreach($this->data['personal_urls'] as $key => $item) { ?>
                    <li id="<?php echo Sanitizer::escapeId( "pt-$key" ) ?>"<?php
                                                                if ($item['active']) { ?> class="active"<?php } ?>><a href="<?php
                                                                echo htmlspecialchars($item['href']) ?>"<?php echo $skin->tooltipAndAccesskey('pt-'.$key) ?><?php
                                                                if(!empty($item['class'])) { ?> class="<?php
                                                echo htmlspecialchars($item['class']) ?>"<?php } ?>><?php
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
               <?php foreach ($this->data['sidebar'] as $bar => $cont) { ?>
               <div id="some_other_content" class="box box-shadow alpha clear-both navigation">
               <h2 class="box-header">
                  <?php $out = wfMsg( $bar ); if (wfEmptyMsg($bar, $out)) echo $bar; else echo $out;?>
               </h2>
                 <ul class="navigation">
                  <?php foreach($cont as $key => $val) { ?>
                  <li><a href="<?php echo htmlspecialchars($val['href']) ?>"><?php echo htmlspecialchars($val['text'])?></a></li>
                  <?php } ?>
                 </ul>          
               </div>
               <?php } ?>


                <div id="some_other_content" class="box box-shadow alpha clear-both navigation">
                    <h2 class="box-header"><?php $this->msg('toolbox') ?></h2>
                    <ul class="navigation">
                                <?php if($this->data['notspecialpage']) { ?><li id="t-whatlinkshere"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href']) ?>"<?php echo $this->skin->tooltipAndAccesskey('t-whatlinkshere') ?>><?php $this->msg('whatlinkshere') ?></a></li>
                                    <?php if( $this->data['nav_urls']['recentchangeslinked'] ) { ?><li id="t-recentchangeslinked"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href']) ?>"<?php echo $this->skin->tooltipAndAccesskey('t-recentchangeslinked') ?>><?php $this->msg('recentchangeslinked') ?></a></li>
                <?php }
                                }
        ?>

                                <?php if(isset($this->data['nav_urls']['trackbacklink'])) { ?>
                        <li id="t-trackbacklink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href']) ?>"><?php echo $this->msg('trackbacklink') ?></a></li>
                                    <?php } ?>
                                <?php if($this->data['feeds']) { ?>
                        <li id="feedlinks"><?php foreach($this->data['feeds'] as $key => $feed) { ?><span id="feed-<?php echo htmlspecialchars($key) ?>"><a href="<?php echo htmlspecialchars($feed['href']) ?>"><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;</span><?php } ?></li>
                                    <?php } ?>
        <?php foreach( array('contributions', 'emailuser', 'upload', 'specialpages') as $special ) { ?><?php if($this->data['nav_urls'][$special]) { ?>
                        <li id="t-<?php echo $special ?>"><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href']) ?>"><?php $this->msg($special) ?></a></li>
                <?php } ?><?php } ?>
                    </ul>
                </div>

                <div id="some_other_content" class="box box-shadow alpha clear-both navigation">
                    <h2 class="box-header">Sponsors</h2>
                    <?php $arr = array("sponsor_amd.png", 'sponsor_b1-systems.png', 'sponsor_ip-exchange2.png'); ?>
                    <a href="/Sponsors"><img src="https://static.opensuse.org/themes/bento/images/sponsors/<?php echo $arr[rand(0, count($arr)-1)] ?>" alt="Sponsor"/></a>
                </div>


        <?php if( $this->data['language_urls'] ) { ?>
                <div id="language_box" class="box box-shadow alpha clear-both navigation">
                    <h2 class="box-header">In other languages</h2>
                    <ul class="navigation">
            <?php foreach($this->data['language_urls'] as $langlink) { ?>
                        <li><a href='<?php echo htmlspecialchars($langlink['href']) ?>'><?php echo $langlink['text'] ?></a></li>
                                <?php } ?>
                    </ul>
                </div>
            <?php } ?>


            </div>


                        <?php if($this->data['sitenotice']) { ?>
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
                                foreach($this->data['content_actions'] as $key => $tab) {

                                    echo '<li>';
                                    echo'<a href="'.htmlspecialchars($tab['href']).'"';
                                    if( $tab['class'] ) {
                                        echo ' class="'.htmlspecialchars($tab['class']).'"';
                                    }
                                    # We don't want to give the watch tab an accesskey if the
                                    # page is being edited, because that conflicts with the
                                    # accesskey on the watch checkbox.  We also don't want to
                                    # give the edit tab an accesskey, because that's fairly su-
                                    # perfluous and conflicts with an accesskey (Ctrl-E) often
                                    # used for editing in Safari.
                                    if( in_array( $action, array( 'edit', 'submit' ) )
                                            && in_array( $key, array( 'edit', 'watch', 'unwatch' ))) {
                                        echo $skin->tooltip( "ca-$key" );
                                    } else {
                                        echo $skin->tooltipAndAccesskey( "ca-$key" );
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
                            <?php if($this->data['undelete']) { ?>
                    <div id="contentSub2">undelete: <?php $this->html('undelete') ?></div>
                                <?php } ?>
        <?php if($this->data['newtalk'] ) { ?>
                    <div class="usermessage">usermessage: <?php $this->html('newtalk')  ?></div>
                                <?php } ?>

                    <!-- Begin Content Area -->
                            <?php $this->html('bodytext') ?>

        <?php if($this->data['catlinks']) {
            $this->html('catlinks');
        } ?>
        <?php if($this->data['dataAfterContent']) {
            $this->html ('dataAfterContent');
                                } ?>
                    <!-- End Content Area -->

                </div>
                <br/>

                <div class="box-footer header-tabs">
                    <ul>
                                <?php $check=false;
                                foreach($this->data['content_actions'] as $key => $tab) {

                                    echo '<li>';
                                    echo'<a href="'.htmlspecialchars($tab['href']).'"';
                                    if( $tab['class'] ) {
                                        echo ' class="'.htmlspecialchars($tab['class']).'"';
                                    }
                                    # We don't want to give the watch tab an accesskey if the
                                    # page is being edited, because that conflicts with the
                                    # accesskey on the watch checkbox.  We also don't want to
                                    # give the edit tab an accesskey, because that's fairly su-
                                    # perfluous and conflicts with an accesskey (Ctrl-E) often
                                    # used for editing in Safari.
                                    if( in_array( $action, array( 'edit', 'submit' ) )
                    && in_array( $key, array( 'edit', 'watch', 'unwatch' ))) {
                echo $skin->tooltip( "ca-$key" );
            } else {
                echo $skin->tooltipAndAccesskey( "ca-$key" );
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
                $handle = fopen(dirname( __FILE__ ) . "/bento/includes/footer.html","rb");
                $content = stream_get_contents($handle);
                fclose($handle);
                if(isset($this->data['lastmod'])) {
                    $content = split('<p>',$content,2);
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
}
?>
