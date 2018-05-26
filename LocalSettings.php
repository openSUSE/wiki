<?php
# GlobalSettings for both production server and localhost development.
#
# You still need a LocalSettings.php to make it work. Do NOT save any secrets,
# passwords, and other senstive data here, because it will be public accessable
# on Github.

# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

# Include production server configuration if file exists
if ( file_exists( '/srv/settings/wiki_settings.php' ) ) {
	require_once '/srv/settings/wiki_settings.php';
	$is_production = true;
}
# Include local development configuration if file exists
elseif ( file_exists( 'wiki_settings.php' ) ) {
	require_once 'wiki_settings.php';
	$is_production = false;
} else {
	exit('Please create wiki_settings.php file.');
}

if ( $wgCommandLineMode ) {
    if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
        die( "This script must be run from the command line\n" );
    }
} elseif ( empty( $wgNoOutputBuffer ) ) {
    ## Compress output if the browser supports it
    if( !ini_get( 'zlib.output_compression' ) ) @ob_start( 'ob_gzhandler' );
}

$wgSitename = "openSUSE";
$wgMetaNamespace = "OpenSUSE";

$wgAllowDisplayTitle = true;

## The URL base path to the directory containing the wiki;
## defaults for all runtime URL paths are based off of this.
## For more information on customizing the URLs
## (like /w/index.php/Page_title to /wiki/Page_title) please see:
## http://www.mediawiki.org/wiki/Manual:Short_URL
$wgScriptPath = "";
$wgScriptExtension = ".php";
$wgArticlePath = "/$1";
$wgUsePathInfo = true;

$wgStylePath = "$wgScriptPath/skins";

## The relative URL path to the logo.  Make sure you change this from the default,
## or else you'll overwrite your logo when you upgrade!
$wgLogo = "$wgStylePath/common/images/wiki.png";

$wgUploadPath = "$wgScriptPath/images";
$wgUploadDirectory = "$IP/images";

$wgEnableEmail = true;
$wgEnableUserEmail = false;

#$wgEmergencyContact = "webmaster@novell.com";
$wgEmergencyContact = "noreply@novell.com";
$wgPasswordSender   = "webmaster@novell.com";

## For a detailed description of the following switches see
## http://meta.wikimedia.org/Enotif and http://meta.wikimedia.org/Eauthent
## There are many more options for fine tuning available see
## /includes/DefaultSettings.php
## UPO means: this is also a user preference option
$wgEnotifUserTalk = true; # UPO
$wgEnotifWatchlist = true; # UPO
$wgEmailAuthentication = false;

# If you're on MySQL 3.x, this next line must be FALSE:
$wgDBmysql4 = true;

# Experimental charset support for MySQL 4.1/5.0.
$wgDBmysql5 = true;

## Cache
if ( $is_production ) {
	# File Cache
	#$wgUseFileCache = true; /* default: false */
	#$wgFileCacheDirectory = "/srv/www/htdocs/cache";
	$wgShowIPinHeader = false;

	$wgMemCachedServers = array( 0 => '127.0.0.1:11211' );
	$wgMainCacheType = CACHE_MEMCACHED;
	$wgMessageCacheType = CACHE_ANYTHING;
	$wgParserCacheType = CACHE_MEMCACHED;
	$configdate = gmdate( 'YmdHis', @filemtime( __FILE__ ) );
	$wgCacheEpoch = max( $wgCacheEpoch, $configdate );
	$wgEnableSidebarCache = true;
	$wgCacheDirectory = "/srv/www/cache";
} else {
	$wgMainCacheType = CACHE_NONE;
	$wgCacheDirectory = "$IP/cache";
	$wgCachePages = false;
}

## To enable image uploads, make sure the 'images' directory
## is writable, then uncomment this:
$wgEnableUploads  = true;
$wgUseImageResize = true;
$wgUseImageMagick = false;
#$wgImageMagickConvertCommand = "/usr/bin/convert";

# InstantCommons allows wiki to use images from http://commons.wikimedia.org
$wgUseInstantCommons = true;

## If you want to use image uploads under safe mode,
## create the directories images/archive, images/thumb and
## images/temp, and make them all writable. Then uncomment
## this, if it's not already uncommented:
# $wgHashedUploadDirectory = false;

## If you have the appropriate support software installed
## you can enable inline LaTeX equations:
# $wgUseTeX = true;
$wgMathPath         = "{$wgUploadPath}/math";
$wgMathDirectory    = "{$wgUploadDirectory}/math";
$wgTmpDirectory     = "{$wgUploadDirectory}/temp";

$wgLocalInterwiki   = $wgSitename;

$wgCookieDomain = "opensuse.org";

## For attaching licensing metadata to pages, and displaying an
## appropriate copyright notice / icon. GNU Free Documentation
## License and Creative Commons licenses are supported so far.
# $wgEnableCreativeCommonsRdf = true;
$wgRightsPage = ""; # Set to the title of a wiki page that describes your license/copyright
$wgRightsUrl = "";
$wgRightsText = "";
$wgRightsIcon = "";
# $wgRightsCode = ""; # Not yet used
$wgWhitelistEdit = true;
$wgLocalTZoffset = date("Z") / 3600;
$wgGroupPermissions['*'    ]['edit']            = false;
$wgFavicon = "//www.opensuse.org/favicon.ico";
$wgDiff3 = "/usr/bin/diff3";

# used for mysql/search settings
$tmarray = getdate(time());
$hour = $tmarray['hours'];
$day = $tmarray['wday'];

# Ugly hack warning! This needs smoothing out.
if($wgLocaltimezone) {
	$oldtz = getenv('TZ');
	putenv("TZ=$wgLocaltimezone");
	$wgLocalTZoffset = date('Z') / 3600;
	putenv("TZ=$oldtz");
}

## Debug

$wgShowExceptionDetails = true;

# increase the time frame for recent changes to 180 days for cleanup. this is a
# temporary change and can be reverted after the cleanup is completed
$wgRCMaxAge = 180 * 24 * 60 * 60;

#-------------------------------------------------------------------------------
# Custom config section
#-------------------------------------------------------------------------------

##### Namespace configuration #####

# Custom namespaces

define( 'NS_SDB', 100 );
define( 'NS_SDB_TALK', 101 );
define( 'NS_PORTAL', 102 );
define( 'NS_PORTAL_TALK', 103 );
define( 'NS_ARCHIVE', 104 );
define( 'NS_ARCHIVE_TALK', 105 );
define( 'NS_HCL', 106 );
define( 'NS_HCL_TALK', 107 );
define( 'NS_BOOK', 110 );
define( 'NS_BOOK_TALK', 111 );

$wgExtraNamespaces[NS_SDB] = 'SDB';
$wgExtraNamespaces[NS_SDB_TALK] = 'SDB_Talk';
$wgExtraNamespaces[NS_PORTAL] = 'Portal';
$wgExtraNamespaces[NS_PORTAL_TALK] = 'Portal_Talk';
$wgExtraNamespaces[NS_ARCHIVE] = 'Archive';
$wgExtraNamespaces[NS_ARCHIVE_TALK] = 'Archive_Talk';
$wgExtraNamespaces[NS_HCL] = 'HCL';
$wgExtraNamespaces[NS_HCL_TALK] = 'HCL_Talk';
$wgExtraNamespaces[NS_BOOK] = 'Book';
$wgExtraNamespaces[NS_BOOK_TALK] = 'Book_Talk';

# Enable/Disable subpages
$wgNamespacesWithSubpages[NS_SPECIAL] = false;
$wgNamespacesWithSubpages[NS_MAIN] = true;
$wgNamespacesWithSubpages[NS_TALK] = true;
$wgNamespacesWithSubpages[NS_USER] = true;
$wgNamespacesWithSubpages[NS_USER_TALK] = true;
$wgNamespacesWithSubpages[NS_PROJECT] = true;
$wgNamespacesWithSubpages[NS_PROJECT_TALK] = true;
$wgNamespacesWithSubpages[NS_FILE] = false;
$wgNamespacesWithSubpages[NS_FILE_TALK] = true;
$wgNamespacesWithSubpages[NS_MEDIAWIKI] = false;
$wgNamespacesWithSubpages[NS_MEDIAWIKI_TALK] = true;
$wgNamespacesWithSubpages[NS_TEMPLATE] = true;
$wgNamespacesWithSubpages[NS_TEMPLATE_TALK] = true;
$wgNamespacesWithSubpages[NS_SDB] = true;
$wgNamespacesWithSubpages[NS_SDB_TALK] = true;
$wgNamespacesWithSubpages[NS_PORTAL] = true;
$wgNamespacesWithSubpages[NS_PORTAL_TALK] = true;
$wgNamespacesWithSubpages[NS_ARCHIVE] = true;
$wgNamespacesWithSubpages[NS_ARCHIVE_TALK] = true;
$wgNamespacesWithSubpages[NS_BOOK] = true;

$wgContentNamespaces = array (NS_MAIN, NS_PROJECT, NS_HELP, NS_SDB, NS_PORTAL, NS_ARCHIVE, NS_HCL, NS_BOOK);

$wgAllowCategorizedRecentChanges = true;

$wgNamespacesToBeSearchedDefault = [
	NS_MAIN => true,
	NS_PORTAL => true
];

##### Misc #####

$wgUseAjax = true; // Enable Ajax
$wgAllowExternalImages = true; // Enable links to external images
# Allow upload of files with the following extensions
$wgFileExtensions = array( 'doc', 'docx', 'gif', 'jpg', 'jpeg', 'odp', 'ods', 'odt', 'pdf', 'png', 'ppt', 'pptx', 'sxc', 'sxw', 'xls', 'xlsx' );
# Add XMPP functionality
$wgUrlProtocols[] = 'xmpp:';

# To be removed once the wiki transition is finished
$wgGroupPermissions['user']['import'] = true;
$wgGroupPermissions['user']['importupload'] = true;
$wgGroupPermissions['sysop']['deleterevision']  = true;
$wgGroupPermissions['user']['move'] = true;

# make the real IPs visible to the wiki instead of the auth proxy (AccessManager) IPs. Without this, IP blocking blocks the proxy IP and therefore edits from everywhere.
if ( $is_production ) {
	$wgUseSquid = true;
	$wgSquidServers = array();
	$wgSquidServers[] = "137.65.227.73";
	$wgSquidServers[] = "137.65.227.74";
	$wgSquidServers[] = "137.65.227.75";
	$wgSquidServers[] = "137.65.227.76";
}

# Category watching ----------------------------------
# see https://www.mediawiki.org/wiki/Manual:CategoryMembershipChanges
$wgRCWatchCategoryMembership = true;
$wgDefaultUserOptions['hidecategorization'] = 0;
$wgDefaultUserOptions['watchlisthidecategorization'] = 0;

#-------------------------------------------------------------------------------
# Skins
#-------------------------------------------------------------------------------

wfLoadSkin( 'bento' );
wfLoadSkin( 'Chameleon' );

$wgDefaultSkin = "Chameleon";

#-------------------------------------------------------------------------------
# Extensions
#-------------------------------------------------------------------------------

##### Login proxy / Auth_remoteuser

wfLoadExtension( 'Auth_remoteuser' );

$wgAuthRemoteuserUserUrls = [ 'logout' => '/cmd/ICSLogout/?url=' . htmlentities($_SERVER['REQUEST_URI']) ];

if (isset($_SERVER['HTTP_X_USERNAME'])) { # avoid logging 'undefined index' warnings
    $wgAuthRemoteuserUserName = [ $_SERVER['HTTP_X_USERNAME'] ];
    $wgAuthRemoteuserUserPrefsForced = [ 'email' => $_SERVER['HTTP_X_EMAIL'] ];
} else {
    $wgAuthRemoteuserUserName = [ '' ];
    $wgAuthRemoteuserUserPrefsForced = [ 'email' => '' ];
}


##### UserMerge

require_once "$IP/extensions/UserMerge/UserMerge.php";

# By default nobody can use this function, enable for bureaucrat?
$wgGroupPermissions['bureaucrat']['usermerge'] = true;


##### WikiEditor

require_once "$IP/extensions/WikiEditor/WikiEditor.php";

# Default user options for WikiEditor. Otherwise it is not enabled by default.
$wgDefaultUserOptions['usebetatoolbar'] = 1;
$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1;
$wgDefaultUserOptions['wikieditor-preview'] = 1;


##### Intersection

require_once "$IP/extensions/intersection/intersection.php";


##### RSS

require_once "$IP/extensions/RSS/RSS.php";

$wgRSSUrlWhitelist = array('*');


##### InputBox

require_once "$IP/extensions/InputBox/InputBox.php";


##### ParserFunctions

require_once "$IP/extensions/ParserFunctions/ParserFunctions.php";


##### CategoryTree.php

require_once "$IP/extensions/CategoryTree/CategoryTree.php";

$wgCategoryTreeMaxDepth = array(CT_MODE_PAGES => 2, CT_MODE_ALL => 2, CT_MODE_CATEGORIES => 3);


##### EventCountdown

require_once("$IP/extensions/EventCountdown.php");


##### SemanticMediaWiki

if ($is_production) {
	$smwgNamespaceIndex=120;
	require_once "$IP/extensions/SemanticMediaWiki/SemanticMediaWiki.php";
	enableSemantics('wiki.opensuse.org');

	### Validator
	require_once "$IP/extensions/Validator/Validator.php";

	### Maps
	require_once "$IP/extensions/Maps/Maps.php";
	require_once '/srv/settings/map_settings.php';

	### Semantic Forms
	require_once "$IP/extensions/SemanticForms/SemanticForms.php";

	### Semantic Maps
	require_once "$IP/extensions/SemanticMaps/SemanticMaps.php";
}


##### MultiBoilerplate

require_once "$IP/extensions/MultiBoilerplate/MultiBoilerplate.php";

$wgMultiBoilerplateOptions = false;
$wgMultiBoilerplatePerNamespace = true;


##### Replace Text

require_once "$IP/extensions/ReplaceText/ReplaceText.php";


##### Interwiki

require_once "$IP/extensions/Interwiki/Interwiki.php";

$wgInterwikiMagic=true;
$wgHideInterlanguageLinks=false;
$wgGroupPermissions['*']['interwiki'] = false;
$wgGroupPermissions['sysop']['interwiki'] = true;


##### videoflash

require_once "$IP/extensions/videoflash.php";


##### SyntaxHighligh

require_once "$IP/extensions/SyntaxHighlight_GeSHi/SyntaxHighlight_GeSHi.php";


##### Hide page title

require_once "$IP/extensions/notitle.php";


##### Smooth Gallery... it is not working right now

#require_once "$IP/extensions/SmoothGallery/SmoothGallery.php";

#$wgSmoothGalleryExtensionPath = "/extensions/SmoothGallery";
#$wgSmoothGalleryDelimiter = "\n";


##### UserPageEditProtection

require_once "$IP/extensions/UserPageEditProtection/UserPageEditProtection.php";

# Only users themselves can edit their own user pages
$wgOnlyUserEditUserPage = true;
# Sysops can edit all user pages
$wgGroupPermissions['sysop']['editalluserpages'] = true;


##### Google Coop

require_once "$IP/extensions/google-coop.php";


##### Nuke - mass deletion

require_once "$IP/extensions/Nuke/Nuke.php";


##### AbuseFilter - spamfilter

require_once "$IP/extensions/AbuseFilter/AbuseFilter.php";

# set higher EmergencyDisable limits to prevent spam filter from getting disabled with
# "Warning: This filter was automatically disabled as a safety measure. It reached the limit of matching more than 5.00% of actions."
$wgAbuseFilterEmergencyDisableThreshold['default'] = 0.50; # default 0.05
$wgAbuseFilterEmergencyDisableCount['default'] = 50; # default 2

## Group permission
$wgGroupPermissions['sysop']['abusefilter-modify'] = true;
$wgGroupPermissions['*']['abusefilter-log-detail'] = true;
$wgGroupPermissions['*']['abusefilter-view'] = true;
$wgGroupPermissions['*']['abusefilter-log'] = true;
$wgGroupPermissions['sysop']['abusefilter-private'] = true;
$wgGroupPermissions['sysop']['abusefilter-modify-restricted'] = true;
$wgGroupPermissions['sysop']['abusefilter-revert'] = true;

