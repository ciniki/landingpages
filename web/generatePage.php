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
        $form_content .= "<div id='visible-fields'>";
        $form_content .= "<div class='form-textfield'>";
        $form_content .= "<label for='email'>Email Address *</label>";
        $form_content .= "<input type='email' id='email' name='email' placeholder='Enter your email address' onfocus='document.getElementById(\"hidden-fields-above\").className=\"\";'>";
        $form_content .= "</div>";
        $form_content .= "</div>";
        $form_content .= "<div id='hidden-fields-above' class='hidden-fields'>";
        $form_content .= "<div class='form-radio'>";
        $form_content .= "<div class='label'>Service Level *</div>";
        $form_content .= "<div class='form-radio-item'>";
        $form_content .= "<input type='radio' name='service-level' id='service-level-investor'/><label for='service-level-investor'>Investor</label>";
        $form_content .= "</div>";
        $form_content .= "<div class='form-radio-item'>";
        $form_content .= "<input type='radio' name='service-level' id='service-level-trader'/><label for='service-level-trader'>Trader</label>";
        $form_content .= "</div>";
        $form_content .= "</div>";
        $form_content .= "<div class='form-radio'>";
        $form_content .= "<div class='label'>Would you like to receive SMS notifications? *</div>";
        $form_content .= "<div class='form-radio-item'>";
        $form_content .= "<input type='radio' name='sms-notifications' id='sms-notifications-yes'/><label for='sms-notifications-yes'>Yes</label>";
        $form_content .= "</div>";
        $form_content .= "<div class='form-radio-item'>";
        $form_content .= "<input type='radio' name='sms-notifications' id='sms-notifications-no'/><label for='sms-notifications-no'>No</label>";
        $form_content .= "</div>";
        $form_content .= "</div>";
        $form_content .= "<div class='form-textfield'>";
        $form_content .= "<label for='cellphone'>Cell Phone Number</label>";
        $form_content .= "<input type='text' id='cellphone' name='cellphone' placeholder=''>";
        $form_content .= "</div>";
        $form_content .= "<p>By choosing to proceed, you agree to the <a href='javascript: popupShow(\"subscription-agreement\");'>Subscription Agreement</a> and to receive emails from Trend Alerts. You may opt out of the service and receipt of emails at any time.</p>";
        $form_content .= "<div class='form-submit'>";
        $form_content .= "<input type='submit' value='Start Your Free Trial Now' />";
        $form_content .= "</div>";

        $form_content .= "</div>";
        $form_content .= "</form>";
        $form2_content = preg_replace("/above/", 'below', $form_content);
        $page['javascript'] .= ""
			. "function updateForm() {"
                . "console.log('test');"
            . "}"
            . "";
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
    // Output the page subtitle
    //
    $content .= "<div class='page-subtitle-image-wrap'>";
    if( isset($page['subtitle']) && $page['subtitle'] != '' ) {
        $content .= "<div class='page-subtitle'><div class='page-subtitle-wrap'><h2>" . $page['subtitle'] . "</h2></div></div>";
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


	return array('stat'=>'ok', 'content'=>$content);
}
?>
