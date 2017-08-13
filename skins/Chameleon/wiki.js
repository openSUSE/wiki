// .card unnecessary <p> tag by MediaWiki
jQuery('.card > p, .card-block > p:not(.card-title):not(.card-text)').each(function () {
    jQuery(this).after(jQuery(this).html());
    jQuery(this).remove();
});

// .card-image
jQuery('.card img').addClass('card-img-top img-fluid');
// .card-link
jQuery('.card .card-block')

// .mw-editsection
jQuery('.mw-editsection a').addClass('btn btn-secondary btn-sm');

// table
jQuery('#bodyContent table').addClass('table').addClass('table-responsive');

// Remove some MediaWiki classes that override skin styles
jQuery('.mw-content-ltr').removeClass('mw-content-ltr');
