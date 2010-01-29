<?php

/*

 Version:
 	Hack v0.3.3 (DynamicPageList2 is based on DynamicPageList)
	
 Purpose:outputs a union of articles residing in a selection 
 				of categories and namespaces using configurable output- and
 				ordermethods

 Contributors: 
 	n:en:User:IlyaHaykinson n:en:User:Amgine w:de:Benutzer:Unendlich
 	http://en.wikinews.org/wiki/User:Amgine
 	http://en.wikinews.org/wiki/User:IlyaHaykinson
 	http://de.wikipedia.org/wiki/Benutzer:Unendlich
 
 Licence:
 	This program is free software; you can redistribute it and/or modify
 	it under the terms of the GNU General Public License as published by
 	the Free Software Foundation; either version 2 of the License, or 
 	(at your option) any later version.
 
 	This program is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 	GNU General Public License for more details.
 
 	You should have received a copy of the GNU General Public License along
 	with this program; if not, write to the Free Software Foundation, Inc.,
 	59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 	http://www.gnu.org/copyleft/gpl.html

 Installation:
 	To install, add following to LocalSettings.php
   include("extensions/intersection/DynamicPageList2.php");
*/


$wgDPL2MaxCategoryCount = 4;				// Maximum number of categories allowed in the Query
$wgDPL2MinCategoryCount = 0;				// Minimum number of categories needed in the Query
$wgDPL2MaxResultCount = 50;				// Maximum number of results to allow
$wgDPL2AllowUnlimitedCategories = true;			// Allow unlimited categories in the Query
$wgDPL2AllowUnlimitedResults = true;				// Allow unlimited results to be shown

$wgExtensionFunctions[] = "wfDynamicPageList2";

function wfDynamicPageList2() {
	global $wgParser, $wgMessageCache;
   
	$wgMessageCache->addMessages( array(
					'dpl2_toomanycats' 					=> 'DynamicPageList2: Too many categories!',
					'dpl2_toofewcats' 					=> 'DynamicPageList2: Too few categories!',
					'dpl2_noresults' 					=> 'DynamicPageList2: No results!',
					'dpl2_noincludedcatsorns' 			=> 'DynamicPageList2: You need to include at least one category or specify a namespace!',
					'dpl2_noincludedcatsbutcatdate' 		=> 'DynamicPageList2: You need to include at least one category if you want to use \'addfirstcategorydate=true\' or \'ordermethod=categoryadd\'!',
					'dpl2_morethanonecatbutcatdate'		=> 'DynamicPageList2: If you include more than one category you cannot use \'addfirstcategorydate=true\' or \'ordermethod=categoryadd\'!',
					'dpl2_catoutputwithwrongordermethod'	=> 'DynamicPageList2: You have to use \'ordermethod=title\' when using category-style output!',
					)
				  );
	$wgParser->setHook( "DPL", "DynamicPageList2" );
}

 
// The callback function for converting the input text to HTML output
function DynamicPageList2( $input, $params, &$parser ) {

	error_reporting(E_ALL);

	// INVALIDATE CACHE
	$parser->disableCache();
	
	global $wgTitle;
	global $wgOut;
	global $wgUser;
	global $wgLang;
	global $wgContLang;
	global $wgDPL2MaxCategoryCount, $wgDPL2MinCategoryCount, $wgDPL2MaxResultCount;
	global $wgDPL2AllowUnlimitedCategories, $wgDPL2AllowUnlimitedResults;

	$aParams = array();
	$bCountSet = false;
	
	// Default Values
	$sOrderMethod = 'title';
	$sOrder = 'descending';	
	$sOutputMode = 'unordered';	
	$sRedirects = 'exclude';
	$sInlSymbol = '-';
	$bShowNamespace = true;
	$bSuppressErrors = false;
	$bAddFirstCategoryDate = false;
	$bAddPageTouchedDate = false; 
	
	$aaIncludeCategories = array();		// $aaIncludeCategories is a two 2-dimensional array: Memberarrays are linked using 'AND'
	$aExcludeCategories = array();
	$aNamespaces = array();
	
// ###### PARSE PARAMETERS ######

	$aParams = explode("\n", $input);
	foreach($aParams as $sParam) {
		
		$aParam = explode("=", $sParam);
		if( count( $aParam ) < 2 )
			continue;
		$sType = trim($aParam[0]);
		$sArg = trim($aParam[1]);
		
		switch ($sType) {
			case 'category':
				$aCategories = array();			// Categories in one line separated by '|' are linked using 'OR'
				$aParams = explode("|", $sArg);
				foreach($aParams as $sParam) {
					$sParam=trim($sParam);
					$title = Title::newFromText( $sParam );
					if( $title != NULL )
						$aCategories[] = $title;
				}
				if (!empty($aCategories))
					$aaIncludeCategories[] = $aCategories;	
				break;
				
			case 'notcategory':
				$title = Title::newFromText( $sArg );
				if( $title != NULL )
					$aExcludeCategories[] = $title; 
				break;
				
			case 'namespace':
				$aParams = explode("|", $sArg);
				foreach($aParams as $sParam) {
					$sParam=trim($sParam);
					$sNS = $wgContLang->getNsIndex($sParam);
					if ( $sNS != NULL )
						$aNamespaces[] = $sNS;
					elseif (intval($sParam)>=0)
						$aNamespaces[] = intval($sParam);
				}
				break;
				
			case 'count':
				//ensure that $iCount is a number;
				$iCount = IntVal( $sArg );
		        	$bCountSet = true;
				break;
				
			case 'mode':
				if ( in_array($sArg, array('none','ordered','unordered','category','inline')) )
					$sOutputMode = $sArg;
				break;
			
			case 'inlinesymbol':
				$sInlSymbol = strip_tags($sArg);
				break;
			
			case 'order':
				if ( in_array($sArg, array('ascending','descending')) )
					$sOrder = $sArg;
				break;	
											
			case 'ordermethod':
				if ( in_array($sArg, array('lastedit','categoryadd','title')) )
					$sOrderMethod = $sArg;
				break;
				
			case 'redirects':
				if ( in_array($sArg, array('include','only','exclude')) )
					$sRedirects = $sArg;
				break;
			
			case 'suppresserrors':
				if ($sArg == 'true') $bSuppressErrors = true;
				if ($sArg == 'false') $bSuppressErrors = false;
				break;
				
			case 'addfirstcategorydate':
				if ($sArg == 'true') $bAddFirstCategoryDate = true;
				if ($sArg == 'false') $bAddFirstCategoryDate = false;
				break;
			
			case 'addpagetoucheddate':
				if ($sArg == 'true') $bAddPageTouchedDate = true;
				if ($sArg == 'false') $bAddPageTouchedDate = false;
				break;
			
			case 'shownamespace':
				if ($sArg == 'true') $bShowNamespace = true;
				if ($sArg == 'false') $bShowNamespace = false;
				break;
		}
	}
	
	$iIncludeCatCount = count($aaIncludeCategories);
	$iTotalIncludeCatCount = count($aaIncludeCategories,COUNT_RECURSIVE) - $iIncludeCatCount;
	$iExcludeCatCount = count($aExcludeCategories);
	$iTotalCatCount = $iIncludeCatCount + $iExcludeCatCount;

// ###### CHECKS ON PARAMETERS ######
	
	// no included categories or namespaces!!
	if ($iTotalCatCount == 0 && empty($aNamespaces) )
		return htmlspecialchars( wfMsg( 'dpl2_noincludedcatsorns' ) );	

	// too many categories!!
	if ( ($iTotalCatCount > $wgDPL2MaxCategoryCount) && (!$wgDPL2AllowUnlimitedCategories) )
		return htmlspecialchars( wfMsg( 'dpl2_toomanycats' ) );			

	// too few categories!!
	if ($iTotalCatCount < $wgDPL2MinCategoryCount)
		return htmlspecialchars( wfMsg( 'dpl2_toofewcats' ) );			

	// no included categories but ordermethod=categoryadd or addfirstcategorydate=true!!
	if ($iIncludeCatCount == 0 && ($sOrderMethod == 'categoryadd' || $bAddFirstCategoryDate == true) ) 
		return htmlspecialchars( wfMsg( 'dpl2_noincludedcatsbutcatdate' ) );

	// more than one included category but ordermethod=categoryadd or addfirstcategorydate=true!!
	if ($iTotalCatCount > 1 && ($sOrderMethod == 'categoryadd' || $bAddFirstCategoryDate == true) ) 
		return htmlspecialchars( wfMsg( 'dpl2_morethanonecatbutcatdate' ) );

	// category-style output requested but not ordermethod=title!!
	if ($sOutputMode == 'category' && $sOrderMethod != 'title')
		return htmlspecialchars( wfMsg( 'dpl2_catoutputwithwrongordermethod' ) );	

	// justify limits
    if ($bCountSet) {
      	if ($iCount > $wgDPL2MaxResultCount)
        	$iCount = $wgDPL2MaxResultCount;
    } else
      	if (!$wgDPL2AllowUnlimitedResults) {
        	$iCount = $wgDPL2MaxResultCount;
        	$bCountSet = true;
      	}

    
// ###### BUILD SQL QUERY ######

	$dbr =& wfGetDB( DB_SLAVE );
	$sPageTable = $dbr->tableName( 'page' );
	$sCategorylinksTable = $dbr->tableName( 'categorylinks' );
	
	// SELECT ... FROM
	if ($iTotalIncludeCatCount == 1) 
		$sSqlSelectFrom = "SELECT DISTINCT page_namespace, page_touched, page_title, c1.cl_timestamp FROM $sPageTable";
	else
		$sSqlSelectFrom = "SELECT DISTINCT page_namespace, page_touched, page_title FROM $sPageTable";
	
	// JOIN ...	
	$iCurrentTableNumber = 0;
	for ($i = 0; $i < $iIncludeCatCount; $i++) {
		$sSqlSelectFrom .= " INNER JOIN $sCategorylinksTable AS c" . ($iCurrentTableNumber+1);
		$sSqlSelectFrom .= ' ON page_id = c' . ($iCurrentTableNumber+1) . '.cl_from';
		$sSqlSelectFrom .= ' AND (c' . ($iCurrentTableNumber+1) . '.cl_to=' . $dbr->addQuotes( $aaIncludeCategories[$i][0]->getDbKey() );
		for ($j = 1; $j < count($aaIncludeCategories[$i]); $j++)
			$sSqlSelectFrom .= ' OR c' . ($iCurrentTableNumber+1) . '.cl_to=' . $dbr->addQuotes( $aaIncludeCategories[$i][$j]->getDbKey() );
		$sSqlSelectFrom .= ') ';
		$iCurrentTableNumber++;
	}
	$sSqlWhere = ' WHERE 1=1 ';
	for ($i = 0; $i < $iExcludeCatCount; $i++) {
		$sSqlSelectFrom .= " LEFT OUTER JOIN $sCategorylinksTable AS c" . ($iCurrentTableNumber+1);
		$sSqlSelectFrom .= ' ON page_id = c' . ($iCurrentTableNumber+1) . '.cl_from';
		$sSqlSelectFrom .= ' AND c' . ($iCurrentTableNumber+1) . '.cl_to='.
		$dbr->addQuotes( $aExcludeCategories[$i]->getDbKey() );
		$sSqlWhere .= ' AND c' . ($iCurrentTableNumber+1) . '.cl_to IS NULL';
		$iCurrentTableNumber++;
	}

	// WHERE ...
	// Namespace IS ...
	if ( !empty($aNamespaces)) {
		$sSqlWhere .= ' AND (page_namespace='.$aNamespaces[0];
		for ($i = 1; $i < count($aNamespaces); $i++)
			$sSqlWhere .= ' OR page_namespace='.$aNamespaces[$i];
		$sSqlWhere .= ') ';
	}
	// is_Redirect IS ...	
	switch ($sRedirects) {
		case 'only':
			$sSqlWhere .= ' AND page_is_redirect = 1 ';
			break;
		case 'exclude':
			$sSqlWhere .= ' AND page_is_redirect = 0 ';
			break;
	}
	
	// ORDER BY ...
	switch ($sOrderMethod) {
		case 'lastedit':
			$sSqlWhere .= ' ORDER BY page_touched ';
			break;
		case 'categoryadd':
			$sSqlWhere .= ' ORDER BY c1.cl_timestamp ';
			break;
		case 'title':
			$sSqlWhere .= ' ORDER BY page_title ';
			break;
	}
	if ($sOrder == 'descending')
		$sSqlWhere .= 'DESC';
	else
		$sSqlWhere .= 'ASC';

	// LIMIT ....
    if ($bCountSet)
		$sSqlWhere .= ' LIMIT ' . $iCount;



// ###### PROCESS SQL QUERY ######

	//DEBUG: output SQL query 
	//$output = 'QUERY: [' . $sSqlSelectFrom . $sSqlWhere . "]<br />";    
	//echo 'QUERY: [' . $sSqlSelectFrom . $sSqlWhere . "]<br />";    
	
	$res = $dbr->query($sSqlSelectFrom . $sSqlWhere);
	$sk =& $wgUser->getSkin();
	if ($dbr->numRows( $res ) == 0) {
		if (!$bSuppressErrors)
			return htmlspecialchars( wfMsg( 'dpl2_noresults' ) );
		else
			return '';
	}
	while( $row = $dbr->fetchObject ( $res ) ) {
		$title = Title::makeTitle( $row->page_namespace, $row->page_title);
		if ($bShowNamespace)
			$sLink = $sk->makeKnownLinkObj($title);
		else
			$sLink = $sk->makeKnownLinkObj($title, $wgContLang->convertHtml($title->getText()));
		if ($bAddFirstCategoryDate)
			$aCatAddDates[] = $wgLang->date($row->cl_timestamp) . ': ';	
		else 
			$aCatAddDates[] = '';
		$aArticles[] = $sLink;
		$aArticles_start_char[] = $wgContLang->convert( $wgContLang->firstChar($row->page_title) );
	}
	$dbr->freeResult( $res );
	
	
// ###### SHOW OUTPUT ######

	if ($sOutputMode == 'category')
		$output = DPL2OutputCategoryStyle( $aArticles, $aArticles_start_char);
	else
		$output = DPL2OutputListStyle( $aArticles, $aCatAddDates, $sOutputMode, $sInlSymbol ); 
		
	return $output;
}

function DPL2OutputListStyle ( $aArticles, $aCatAddDates, $sOutputMode, $sInlSymbol ) {
	
	switch ($sOutputMode) {
		case 'none':
			$sStartList = '';
			$sEndList = '';
			$sStartItem = '';
			$sEndItem = '<br/>';
			$bAddLastEndItem = false;
			break;
		case 'inline':
			$sStartList = '';
			$sEndList = '';
			$sStartItem = '';
			$sEndItem = ' ' . $sInlSymbol . ' ';
			$bAddLastEndItem=false;
			break;
		case 'ordered':
			$sStartList = '<ol>';
			$sEndList = '</ol>';
			$sStartItem = '<li>';
			$sEndItem = '</li>';
			$bAddLastEndItem=true;
			break;
		case 'unordered':
		default:
			$sStartList = '<ul>';
			$sEndList = '</ul>';
			$sStartItem = '<li>';
			$sEndItem = '</li>';
			$bAddLastEndItem=true;
			break;
	}		
	
	//process results of query, outputing equivalent of <li>[[Article]]</li> for each result,
	//or something similar if the list uses other startlist/endlist;
	$r = $sStartList . "\n";
	for ($i=0; $i<count($aArticles); $i++) {
		$r .= $sStartItem . $aCatAddDates[$i] . $aArticles[$i];
	   	if ($i<count($aArticles)-1 || $bAddLastEndItem==true)
			$r .= $sEndItem;
		$r .= "\n";
	}
	$r .= $sEndList . "\n";

	return $r;
}

function DPL2OutputCategoryStyle( $aArticles, $aArticles_start_char) { 

	require_once ('CategoryPage.php');
	
	$ret = CategoryPage::formatCount( $aArticles, 'categoryarticlecount' );
	if ( count ($aArticles) > 0 )
		$ret .= CategoryPage::columnList( $aArticles, $aArticles_start_char );
	elseif ( count($aArticles) > 0)
		$ret .= CategoryPage::shortList( $aArticles, $aArticles_start_char );

	return $ret;
}
	
?>
