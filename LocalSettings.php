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


#-------------------------------------------------------------------------------
# Load instance custom settings in wiki_settings.php
#-------------------------------------------------------------------------------

# Include production server configuration if file exists
if ( file_exists( '../wiki_settings.php' ) ) {
	require_once '../wiki_settings.php';
	$wgIsProduction = true;
}
# Include local development configuration if file exists
elseif ( file_exists( 'wiki_settings.php' ) ) {
	require_once 'wiki_settings.php';
	$wgIsProduction = false;
} else {
	exit('Please create wiki_settings.php file.');
}


#-------------------------------------------------------------------------------
# PHP INI
#-------------------------------------------------------------------------------

# Include path
ini_set( "include_path", ".:$IP:$IP/includes:$IP/languages" );
# If PHP's memory limit is very low, some operations may fail.
ini_set( 'memory_limit', '64M' );
# Maximum allowed size for uploaded files.
ini_set( 'upload_max_filesize', '8M');
# Must be greater than upload_max_filesize
ini_set( 'post_max_size', '8M');


#-------------------------------------------------------------------------------
# CommandLineMode / HTTPMode
#-------------------------------------------------------------------------------

if ( $wgCommandLineMode ) {
    if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
        die( "This script must be run from the command line\n" );
    }
} elseif ( empty( $wgNoOutputBuffer ) ) {
    ## Compress output if the browser supports it
    if( !ini_get( 'zlib.output_compression' ) ) @ob_start( 'ob_gzhandler' );
}


#-------------------------------------------------------------------------------
# Basics
#-------------------------------------------------------------------------------

$wgSitename = "openSUSE Wiki";
$wgMetaNamespace = "OpenSUSE";

# Allow to display title different than actual page title
# e.g. Main Page --> Welcome to openSUSE
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
$wgLogo = "$wgStylePath/Chameleon/dist/images/logo/logo-white.svg";


#-------------------------------------------------------------------------------
# Emails & Notifications
#-------------------------------------------------------------------------------

$wgEnableEmail = true;
$wgEnableUserEmail = false;

$wgEmergencyContact = "noreply@opensuse.org";
$wgPasswordSender   = "noreply@opensuse.org";

## For a detailed description of the following switches see
## http://meta.wikimedia.org/Enotif and http://meta.wikimedia.org/Eauthent
## There are many more options for fine tuning available see
## /includes/DefaultSettings.php
## UPO means: this is also a user preference option
$wgEnotifUserTalk = true; # UPO
$wgEnotifWatchlist = true; # UPO
$wgEmailAuthentication = false;


#-------------------------------------------------------------------------------
# Database
#-------------------------------------------------------------------------------

# If you're on MySQL 3.x, this next line must be FALSE:
$wgDBmysql4 = true;

# Experimental charset support for MySQL 4.1/5.0.
$wgDBmysql5 = true;


#-------------------------------------------------------------------------------
# Caching
#-------------------------------------------------------------------------------

if ( $wgIsProduction ) {
	# File Cache
	#$wgUseFileCache = true; /* default: false */
	#$wgFileCacheDirectory = "/srv/www/htdocs/cache";
	$wgShowIPinHeader = false;

	# Use MemCache as main cache type
	$wgMemCachedServers = [ 0 => '127.0.0.1:11211' ];
	$wgMainCacheType = CACHE_MEMCACHED;

	# Session Cache
	# session cache needs to be persistent, see
	# https://www.mediawiki.org/wiki/Topic:T75cloz7981b8i92
	$wgSessionCacheType = CACHE_DB;

	# Cache Expiration
	# Cache older than LocalSettings.php modification time will expire
	$configdate = gmdate( 'YmdHis', @filemtime( __FILE__ ) );
	$wgCacheEpoch = max( $wgCacheEpoch, $configdate );

	$wgEnableSidebarCache = true;

	# Make the real IPs visible to the wiki instead of the auth proxy
	# (AccessManager) IPs. Without this, IP blocking blocks the proxy IP and
	# therefore edits from everywhere.
	$wgUseSquid = true;
	$wgSquidServers = [];
	$wgSquidServers[] = '192.168.47.101';  # elsa.infra.o.o
	$wgSquidServers[] = '192.168.47.102';  # anna.infra.o.o

} else {
	$wgMainCacheType = CACHE_NONE;
	$wgCachePages = false;
}


#-------------------------------------------------------------------------------
# Upload
#-------------------------------------------------------------------------------

## To enable image uploads, make sure the 'images' directory
## is writable, then uncomment this:
$wgEnableUploads  = true;
$wgUseImageResize = true;
$wgUseImageMagick = false;
#$wgImageMagickConvertCommand = "/usr/bin/convert";

# InstantCommons allows wiki to use images from http://commons.wikimedia.org
#$wgUseInstantCommons = true;

## If you want to use image uploads under safe mode,
## create the directories images/archive, images/thumb and
## images/temp, and make them all writable. Then uncomment
## this, if it's not already uncommented:
# $wgHashedUploadDirectory = false;

# Allow upload of files with the following extensions
$wgFileExtensions = [
	'doc',
	'docx',
	'gif',
	'jpg',
	'jpeg',
	'odp',
	'ods',
	'odt',
	'pdf',
	'png',
	'ppt',
	'pptx',
	'svg',
	'sxc',
	'sxw',
	'xls',
	'xlsx'
];

#-------------------------------------------------------------------------------
# Math
#-------------------------------------------------------------------------------

## If you have the appropriate support software installed
## you can enable inline LaTeX equations:
# $wgUseTeX = true;
$wgMathPath         = "$wgUploadPath/math";
$wgMathDirectory    = "$wgUploadDirectory/math";
$wgTmpDirectory     = "$wgUploadDirectory/temp";

$wgLocalInterwiki   = $wgSitename;


#-------------------------------------------------------------------------------
# Copyright/License
#-------------------------------------------------------------------------------

## For attaching licensing metadata to pages, and displaying an
## appropriate copyright notice / icon. GNU Free Documentation
## License and Creative Commons licenses are supported so far.
# $wgEnableCreativeCommonsRdf = true;
$wgRightsPage = "";
$wgRightsUrl  = "https://www.gnu.org/copyleft/fdl.html";
$wgRightsText = "";
$wgRightsIcon = "$wgScriptPath/resources/assets/licenses/gnu-fdl.png";
# $wgRightsCode = ""; # Not yet used


#-------------------------------------------------------------------------------
# Logo & Icon
#-------------------------------------------------------------------------------

$wgFavicon = "//www.opensuse.org/favicon.ico";


#-------------------------------------------------------------------------------
# Misc
#-------------------------------------------------------------------------------

$wgDiff3 = "/usr/bin/diff3";

$wgUseAjax = true; // Enable Ajax

# Enable links to external images
$wgAllowExternalImages = true;

# Add XMPP functionality
$wgUrlProtocols[] = 'xmpp:';

# Category watching
# see https://www.mediawiki.org/wiki/Manual:CategoryMembershipChanges
$wgRCWatchCategoryMembership = true;
$wgDefaultUserOptions['hidecategorization'] = 0;
$wgDefaultUserOptions['watchlisthidecategorization'] = 0;


#-------------------------------------------------------------------------------
# Debug
#-------------------------------------------------------------------------------

if (!$wgIsProduction) {
	$wgShowExceptionDetails = true;
	$wgDebugToolbar=true;
}


#-------------------------------------------------------------------------------
# Permissions
#-------------------------------------------------------------------------------

# Only login user can edit/create pages
$wgGroupPermissions['*'    ]['edit']              = false;

# To be removed once the wiki transition is finished
$wgGroupPermissions['user' ]['import']            = true;
$wgGroupPermissions['user' ]['importupload']      = true;
$wgGroupPermissions['user' ]['move']              = true;
$wgGroupPermissions['sysop']['deleterevision']    = true;

# Don't allow account creating in MediaWiki. Only authenticate with SUSE SSO.
$wgGroupPermissions['*'    ]['createaccount']     = false;
$wgGroupPermissions['*'    ]['autocreateaccount'] = true;


#-------------------------------------------------------------------------------
# Namespaces
#-------------------------------------------------------------------------------

# Project (meta) namespace
$wgMetaNamespace = 'openSUSE';

# Define namespace constants
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

# Define namespaces
$wgExtraNamespaces[NS_SDB]          = 'SDB';
$wgExtraNamespaces[NS_SDB_TALK]     = 'SDB_Talk';
$wgExtraNamespaces[NS_PORTAL]       = 'Portal';
$wgExtraNamespaces[NS_PORTAL_TALK]  = 'Portal_Talk';
$wgExtraNamespaces[NS_ARCHIVE]      = 'Archive';
$wgExtraNamespaces[NS_ARCHIVE_TALK] = 'Archive_Talk';
$wgExtraNamespaces[NS_HCL]          = 'HCL';
$wgExtraNamespaces[NS_HCL_TALK]     = 'HCL_Talk';
$wgExtraNamespaces[NS_BOOK]         = 'Book';
$wgExtraNamespaces[NS_BOOK_TALK]    = 'Book_Talk';

# Enable/Disable subpages
$wgNamespacesWithSubpages[NS_SPECIAL]        = false;
$wgNamespacesWithSubpages[NS_MAIN]           = true;
$wgNamespacesWithSubpages[NS_TALK]           = true;
$wgNamespacesWithSubpages[NS_USER]           = true;
$wgNamespacesWithSubpages[NS_USER_TALK]      = true;
$wgNamespacesWithSubpages[NS_PROJECT]        = true;
$wgNamespacesWithSubpages[NS_PROJECT_TALK]   = true;
$wgNamespacesWithSubpages[NS_FILE]           = false;
$wgNamespacesWithSubpages[NS_FILE_TALK]      = true;
$wgNamespacesWithSubpages[NS_MEDIAWIKI]      = false;
$wgNamespacesWithSubpages[NS_MEDIAWIKI_TALK] = true;
$wgNamespacesWithSubpages[NS_TEMPLATE]       = true;
$wgNamespacesWithSubpages[NS_TEMPLATE_TALK]  = true;
$wgNamespacesWithSubpages[NS_SDB]            = true;
$wgNamespacesWithSubpages[NS_SDB_TALK]       = true;
$wgNamespacesWithSubpages[NS_PORTAL]         = true;
$wgNamespacesWithSubpages[NS_PORTAL_TALK]    = true;
$wgNamespacesWithSubpages[NS_ARCHIVE]        = true;
$wgNamespacesWithSubpages[NS_ARCHIVE_TALK]   = true;
$wgNamespacesWithSubpages[NS_BOOK]           = true;
$wgNamespacesWithSubpages[NS_BOOK_TALK]      = true;

# Content namespaces will be listed in search result by default
$wgContentNamespaces = [
	NS_MAIN,
	NS_PROJECT,
	NS_HELP,
	NS_SDB,
	NS_PORTAL,
	NS_ARCHIVE,
	NS_HCL,
	NS_BOOK
];


$wgAllowCategorizedRecentChanges = true;

$wgNamespacesToBeSearchedDefault = [
	NS_MAIN     => true,
	NS_USER     => true,
	NS_PROJECT  => true,
	NS_FILE     => true,
	NS_TEMPLATE => true,
	NS_HELP     => true,
	NS_CATEGORY => true,
	NS_SDB      => true,
	NS_PORTAL   => true,
	NS_ARCHIVE  => true,
	NS_HCL      => true,
];


#-------------------------------------------------------------------------------
# Skins
#-------------------------------------------------------------------------------

wfLoadSkin( 'bento' );
wfLoadSkin( 'Chameleon' );

$wgDefaultSkin = "Chameleon";

#-------------------------------------------------------------------------------
# Extensions
#-------------------------------------------------------------------------------

##### Leap version variable provider

wfLoadExtension( 'LeapVersion' );

##### Login proxy / Auth_remoteuser

wfLoadExtension( 'Auth_remoteuser' );

$wgAuthRemoteuserUserUrls = [
	'logout' => '/cmd/ICSLogout/?url=' . htmlentities($_SERVER['REQUEST_URI'])
];

if (isset($_SERVER['HTTP_X_USERNAME'])) {
	# avoid logging 'undefined index' warnings
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

$wgRSSUrlWhitelist = [ '*' ];


##### InputBox

require_once "$IP/extensions/InputBox/InputBox.php";


##### ParserFunctions

require_once "$IP/extensions/ParserFunctions/ParserFunctions.php";


##### CategoryTree.php

require_once "$IP/extensions/CategoryTree/CategoryTree.php";

$wgCategoryTreeMaxDepth = [
	CT_MODE_PAGES => 2,
	CT_MODE_ALL => 2,
	CT_MODE_CATEGORIES => 3,
];


##### EventCountdown

require_once("$IP/extensions/EventCountdown.php");


##### Semantic Maps

if ($wgIsProduction) {
	require_once("$IP/extensions/maps-vendor/autoload.php");
	$GLOBALS['egMapsGMaps3ApiKey'] = $google_maps_key;
	#$GLOBALS['egMapsDefaultService'] = 'openlayers';
	#$GLOBALS['egMapsDefaultService'] = 'leaflet';
}

##### MultiBoilerplate

require_once "$IP/extensions/MultiBoilerplate/MultiBoilerplate.php";

$wgMultiBoilerplateOptions = false;
$wgMultiBoilerplatePerNamespace = true;


##### Replace Text

require_once "$IP/extensions/ReplaceText/ReplaceText.php";


##### Interwiki

require_once "$IP/extensions/Interwiki/Interwiki.php";

$wgInterwikiMagic = true;
$wgHideInterlanguageLinks = false;

$wgGroupPermissions['*'    ]['interwiki'] = false;
$wgGroupPermissions['sysop']['interwiki'] = true;


##### videoflash

require_once "$IP/extensions/videoflash.php";


##### SyntaxHighligh

require_once "$IP/extensions/SyntaxHighlight_GeSHi/SyntaxHighlight_GeSHi.php";


##### Hide page title

require_once "$IP/extensions/notitle.php";


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


##### AbuseFilter - spam filter

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


##### Hit counter

require_once "$IP/extensions/HitCounters/HitCounters.php";


##### GitHub: include READMEs etc. from GitHub

require_once "$IP/extensions/GitHub/GitHub.php";


##### Elastica search

if ($wgIsProduction) {

	require_once "$IP/extensions/Elastica/Elastica.php";

	require_once "$IP/extensions/CirrusSearch/CirrusSearch.php";

	$wgCirrusSearchServers = [ $elasticsearch_server ];

	$wgSearchType = 'CirrusSearch';

	$wgCirrusSearchNamespaceWeights = [
		NS_MAIN     => 1,
		NS_USER     => 0.05, # default
		NS_PROJECT  => 0.6,
		NS_MEDIAWIKI => 0.05, # default
		NS_FILE     => 0.02,
		NS_TEMPLATE => 0.005, # default
		NS_HELP     => 0.1, # default
		NS_CATEGORY => 0.02,
		NS_SDB      => 0.6,
		NS_PORTAL   => 1,
		NS_ARCHIVE  => 0.2,
		NS_HCL      => 0.2,
	];
}
