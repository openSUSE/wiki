<?php

/*
 * Nice Category List extension.
 * Generates a list of all pages in the category, including subcategories.
 * Unlike the default category list, the generated list is flat and shows
 * well, even with long page names.
 *
 * Usage:
 *   <ncl>Category:Some Category</ncl>
 */

$wgExtensionFunctions[] = 'wfNiceCategoryList';

/*
 * Setup Nice Category List extension.
 * Sets a parser hook for <ncl></ncl>.
 */
function wfNiceCategoryList() {
    global $wgParser;
    $wgParser->setHook('ncl', hookNiceCategoryList);
}

/*
 * The hook function. Handles <ncl></ncl>.
 * Receives the category name as a parameter.
 */
function hookNiceCategoryList($category) {
    $dbr =& wfGetDB(DB_SLAVE);

    $title = Title::newFromText($category);
    if (!$title) return '';

    $ct = fetchCategoryLinks($dbr, $title);

    global $wgOut;
    return $wgOut->parse(outputCategory($ct));
}

/*
 * Get all links in a category.
 */
function getCategoryLinks($dbr, $title) {
    return $dbr->select(
            array( 'page', 'categorylinks' ),
            array( 'page_title', 'page_namespace', 'page_len', 'cl_sortkey' ),
            array( 'cl_from          =  page_id',
                   'cl_to'           => $title->getDBKey()),
                   #'page_is_redirect' => 0),
            #+ $pageCondition,
            $fname,
            array( 'ORDER BY' => $flip ? 'cl_sortkey DESC' : 'cl_sortkey' ) );
}

/*
 * Title comparison function
 */
function titleCmp($a, $b) {
    return $a->getText() > $b->getText();
}

/*
 * CategoryLinks comparison function
 */
function categoryCmp($a, $b) {
    return titleCmp($a->title, $b->title);
}

/*
 * Simple class to hold category's title, links list,
 * and categories list.
 */
class CategoryLinks {
    var $title;
    var $links = array();
    var $categories = array();

    function CategoryLinks($title) {
        $this->title = $title;
    }

    /*
     * Sort links and categories alphabetically.
     */
    function sort() {
        usort($this->links, titleCmp);
        usort($this->categories, categoryCmp);
    }
}

/*
 *
 */
function fetchCategoryLinks($dbr, $category_title, $processed = array()) {
    // avoid recursion
    if (in_array($category_title->getText(), $processed))
        return NULL;
    $processed[] = $category_title->getText();

    $cl = new CategoryLinks($category_title);

    // get category links from db
    $res = getCategoryLinks($dbr, $category_title);

    // process all category links
    while($x = $dbr->fetchObject($res)) {
        $title = Title::makeTitle($x->page_namespace, $x->page_title);

        if($title->getNamespace() == NS_CATEGORY) {
            // if category, recurse
            $fc = fetchCategoryLinks($dbr, $title, $processed);
            if ($fc) $cl->categories[] = $fc;
        } else {
            // if regular page, add to list
            $cl->links[] = $title;
        }
    }

    // sort
    $cl->sort();

    return $cl;
}

/*
 * Generate output for the list.
 */
function outputCategory($category, $level = 0) {
    global $wgContLang, $wgUser;
    $sk =& $wgUser->getSkin();

    if ($level == 0) {
        // no need for TOC
        $output = "__NOTOC__\n";
    } else {
        // second level and onwards, has a heading.
        // the heading gets smaller as the leve grows.

        $title = $category->title;
        $title = $wgContLang->convert($title->getText());
        $heading = str_repeat('=', $level);

        $output = $heading . $title . $heading . "\n";
    }

    // output each link
    foreach ($category->links as $link) {
        $title = $link->getPrefixedText();
        $disp = $link->getText();
        $output .= "* [[" . $title . "|" . $disp . "]]\n";
    }

    // recurse into each subcategory
    foreach ($category->categories as $cat) {
        $output .= outputCategory($cat, $level + 1);
    }

    return $output;
}

?>
