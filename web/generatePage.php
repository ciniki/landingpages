<?php
//
// This is the override page generator for the landing pages module. 
// This module creates their own header and footer.
//
function ciniki_landingpages_web_generatePage(&$ciniki, $settings) {
	
	//
	// Check for page url
	//
	if( !isset($ciniki['request']['uri_split'][0]) || $ciniki['request']['uri_split'][0] == '' ) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2652', 'msg'=>"I'm sorry, but we can't seem to find the page you requested."));
	}

	$landingpage_permalink = $ciniki['request']['uri_split'][0];

	//
	// Load the landing page, settings, content, etc
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'web', 'pageLoad');
	$rc = ciniki_landingpages_web_pageLoad($ciniki, $settings, $ciniki['request']['business_id'], $landingpage_permalink);
	if( $rc['stat'] != 'ok' ) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2653', 'msg'=>"I'm sorry, but we can't seem to find the page you requested.", 'err'=>$rc['err']));
	}
	$page = $rc['page'];

    $page['javascript'] = '';

    //
    // Find out if form
    //
    $form_content = '';
//    if( isset($page['settings']['page-form']) && $page['settings']['page-form'] != '' ) {
        $form_content .= "<form >";
        $form_content .= "<input type='email' name='email' placeholder='Enter your email address'>";
        $form_content .= "</form>";
 //   }


	//
	// Generate the page content
	//
	$content = '';	

	//
	// Generate the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'web', 'header');
	$rc = ciniki_landingpages_web_header($ciniki, $settings, $page);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$content .= $rc['content'];

    $content .= "<div id='content'>";

    //
    // Output the page title
    //
    if( isset($page['title']) && $page['title'] != '' ) {
        $content .= "<div class='page-title'><div class='page-title-wrap'><h1>" . $page['title'] . "</h1></div></div>";
    }

    //
    // Check if form is to be above content
    //
    if( $form_content != '' && isset($page['settings']['page-form-above']) && $page['settings']['page-form-above'] == 'yes') {
        $content .= "<div class='page-form page-form-above'><div class='page-form-wrap'>";
        $content .= $form_content;
        $content .= "</div></div>";
    }

	//
	// Add the content
    //
    $content .= "<article>";
    $content .= "<div class='entry-content'>";

    $item_content = '';
    $item_menu = "<div class='item-menu-container'><ul class='item-menu'>";
    $menu_active = ' item-menu-active';
    $content_active = ' item-content-active';
    $cur_item = '';
    foreach($page['items'] as $item) {
        if( $cur_item == '' ) {
            $cur_item = $item['permalink'];
        }
        $item_menu .= "<li id='i-" . $item['permalink'] . "' class='item-menu-item$menu_active'>" 
            . "<a class='' onclick='switchContent(\"" . $item['permalink'] . "\");'>"
            . $item['menu_title'] 
            . "</a>"
            . "</li>";

        $item_content .= "<div id='c-" . $item['permalink'] . "' class='item-content$content_active'>";

        //
        // Title
        //
        $item_content .= "<div class='item-content-title'><h2>" . $item['title'] . "</h2></div>";
       
        //
        // Image
        //
        if( isset($item['primary_image_id']) && $item['primary_image_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $item['primary_image_id'], 'original', '500', 0);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $item_content .= "<div class='image'><div class='item-content-image'>";
            $item_content .= "<img title='' alt='" . (isset($item['title'])?$item['title']:'') . "' src='" . $rc['url'] . "' />";
            $item_content .= "</div></div>";
        }

        //
        // Content
        //
        $item_content .= "<div class='item-content-wrap'>";
        $item_content .= $item['content'];
        $item_content .= "</div>";
        $item_content .= "</div>";
        $menu_active = '';
        $content_active = '';
    }
    $item_menu .= "</ul></div>";

    if( count($page['items']) > 1 ) {
        $content .= $item_menu;
        $page['javascript'] .= ""
            . "var curItem = '" . $cur_item . "';"
			. "function switchContent(p) {"
                . "document.getElementById('i-'+curItem).className='item-menu-item';"
                . "document.getElementById('c-'+curItem).className='item-content';"
                . "document.getElementById('i-'+p).className='item-menu-item item-menu-active';"
                . "document.getElementById('c-'+p).className='item-content item-content-active';"
                . "curItem = p;"
            . "}"
            . "";
    }
    $content .= $item_content;

    $content .= "</div>";
    $content .= "</article>";

    //
    // Check if form is to be below content
    //
    if( $form_content != '' && isset($page['settings']['page-form-below']) && $page['settings']['page-form-below'] == 'yes') {
        $content .= "<div class='page-form page-form-below'><div class='page-form-wrap'>";
        $content .= $form_content;
        $content .= "</div></div>";
    }

    $content .= "</content>";

	//
	// Generate the footer
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'web', 'footer');
	$rc = ciniki_landingpages_web_footer($ciniki, $settings, $page);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$content .= $rc['content'];


	return array('stat'=>'ok', 'content'=>$content);
}
?>
