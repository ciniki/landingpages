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
    // Check for form
    //
    $form_content = '';
    if( isset($page['settings']['page-form']) && $page['settings']['page-form'] != '' && preg_match('/.*\..*\..*/', $page['settings']['page-form']) ) {
        list($pkg, $mod, $form) = explode('.', $page['settings']['page-form']);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'landingpageForm');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $settings, $ciniki['request']['business_id'], array('form'=>$form, 'landingpage_id'=>$page['id']));
            if( $rc['stat'] == 'ok' && isset($rc['redirect_url']) ) {
                header('HTTP/1.1 303 See Other');
                header('Location: ' . $rc['redirect_url']);
                return array('stat'=>'exit', 'content'=>'');
            } elseif( $rc['stat'] == 'ok' ) {
                $form_content = $rc['form_content'];
            }
        }
    }
    $form2_content = preg_replace("/above/", 'below', $form_content);

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
    // Output the page subtitle
    //
    $content .= "<div class='page-subtitle-image-wrap'>";
    if( isset($page['subtitle']) && $page['subtitle'] != '' ) {
        $content .= "<div class='page-subtitle'><div class='page-subtitle-wrap'><h2>" . $page['subtitle'] . "</h2>";
        if( isset($page['tagline']) && $page['tagline'] != '' ) {
            $content .= "<h3>" . $page['tagline'] . "</h3>";
        }
        $content .= "</div></div>";
    }

    //
    // Image
    //
    if( isset($page['primary_image_id']) && $page['primary_image_id'] > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        $rc = ciniki_web_getScaledImageURL($ciniki, $page['primary_image_id'], 'original', '500', 0);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $content .= "<div class='page-image'><div class='page-image-wrap'>";
        $content .= "<img title='' alt='" . (isset($page['subtitle'])?$page['subtitle']:'') . "' src='" . $rc['url'] . "' />";
        $content .= "</div></div>";
    }
    $content .= "</div>";

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
    $item_menu_items = '';
    $dropdown_menu_items = '';
    $menu_active = ' item-menu-active';
    $content_active = ' item-content-active';
    $cur_item = '';
    foreach($page['items'] as $item) {
        if( $cur_item == '' ) {
            $cur_item = $item['permalink'];
        }
        $item_menu_items .= "<li id='i-" . $item['permalink'] . "' class='item-menu-item$menu_active'>" 
            . "<a class='' onclick='switchContent(\"" . $item['permalink'] . "\");'>"
            . $item['menu_title'] 
            . "</a>"
            . "</li>";
        $dropdown_menu_items .= "<li id='d-" . $item['permalink'] . "' class='item-menu-item$menu_active'>" 
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
    if( count($page['items']) > 1 ) {
        $item_menu_dropdown = '';
        if( count($page['items']) > 2 ) {
            $item_menu_dropdown = "<div id='item-menu-dropdown' class='item-menu-dropdown'><ul class='item-menu-dropdown'>";
            $item_menu_dropdown .= $dropdown_menu_items;
            $item_menu_dropdown .= "</ul></div>";
            $item_menu_items .= "<li id='moreinfo' class='item-menu-more'>" 
                . "<a class='' onclick='showDropdown();'>More Info ...</a>"
                . "</li>";
        }

        $content .= "<div class='item-menu-container'><ul class='item-menu-container'>";
        $content .= $item_menu_items;
        $content .= "</ul>";
        $content .= $item_menu_dropdown; 
        $content .= "</div>";

        $page['javascript'] .= ""
            . "var curItem = '" . $cur_item . "';"
			. "function switchContent(p) {"
                . "document.getElementById('i-'+curItem).className='item-menu-item';"
                . "document.getElementById('d-'+curItem).className='item-menu-item';"
                . "document.getElementById('c-'+curItem).className='item-content';"
                . "document.getElementById('i-'+p).className='item-menu-item item-menu-active';"
                . "document.getElementById('d-'+p).className='item-menu-item item-menu-active';"
                . "document.getElementById('c-'+p).className='item-content item-content-active';"
                . "document.getElementById('item-menu-dropdown').className='item-menu-dropdown';"
                . "curItem = p;"
            . "}"
            . "function showDropdown() {"
                . "document.getElementById('item-menu-dropdown').className='item-menu-dropdown item-menu-dropdown-show';"
            . "}"
            . "";
    }
    $content .= $item_content;

    $content .= "</div>";
    $content .= "</article>";

    //
    // Check if form is to be below content
    //
    if( $form2_content != '' && isset($page['settings']['page-form-below']) && $page['settings']['page-form-below'] == 'yes') {
        $content .= "<div class='page-form page-form-below'><div class='page-form-wrap'>";
        $content .= $form2_content;
        $content .= "</div></div>";
    }

    $content .= "</div>";

	//
	// Generate the footer
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'web', 'footer');
	$rc = ciniki_landingpages_web_footer($ciniki, $settings, $page);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$content .= $rc['content'];

    //
    // Log the access
    //
    $referrer = '';
    if( isset($_SERVER['HTTP_REFERER']) ) {
        $referrer = $_SERVER['HTTP_REFERER'];
    }
    $query_string = '';
    if( isset($_SERVER['QUERY_STRING']) ) {
        $query_string = $_SERVER['QUERY_STRING']; 
    }
    $user_agent = '';
    if( isset($_SERVER['HTTP_USER_AGENT']) ) {
        $user_agent = $_SERVER['HTTP_USER_AGENT']; 
    }
    $strsql = "INSERT INTO ciniki_landingpage_log (uuid, business_id, landingpage_id, log_date, query_string, referrer, user_agent, date_added, last_updated) VALUES ("
        . "UUID(), "
        . "'" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "', "
        . "'" . ciniki_core_dbQuote($ciniki, $page['id']) . "', "
        . "UTC_TIMESTAMP(), "
        . "'" . ciniki_core_dbQuote($ciniki, $query_string) . "', "
        . "'" . ciniki_core_dbQuote($ciniki, $referrer) . "', "
        . "'" . ciniki_core_dbQuote($ciniki, $user_agent) . "', "
        . "UTC_TIMESTAMP(), "
        . "UTC_TIMESTAMP() "
        . ")";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
    $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.landingpages');
    if( $rc['stat'] != 'ok' ) {
        error_log("WEB: unable to log landingpage visit: " . print_r($rc['err'], true));
    }

	return array('stat'=>'ok', 'content'=>$content);
}
?>
