<?php
//
// This is the override page generator for the landing pages module. 
// This module creates their own header and footer.
//
function ciniki_landingpages_web_pageLoad(&$ciniki, &$settings, $business_id, $permalink) {
    
    //
    // Get the landing page details
    //
    $strsql = "SELECT ciniki_landingpages.id, "
        . "ciniki_landingpages.name, "
        . "ciniki_landingpages.title, "
        . "ciniki_landingpages.short_title, "
        . "ciniki_landingpages.subtitle, "
        . "ciniki_landingpages.tagline, "
        . "ciniki_landingpages.status, "
        . "ciniki_landingpages.flags, "
        . "ciniki_landingpages.primary_image_id "
        . "FROM ciniki_landingpages "
        . "WHERE ciniki_landingpages.permalink = '" . ciniki_core_dbQuote($ciniki, $permalink) . "' "
        . "AND ciniki_landingpages.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
        . "AND ciniki_landingpages.status > 0 "
        . "AND ciniki_landingpages.status < 50 "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.landingpages', 'landingpage');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['landingpage']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.landingpages.14', 'msg'=>'Page does not exist'));
    }
    $page = $rc['landingpage'];
    
    //
    // Load the landing page settings
    //
    $strsql = "SELECT detail_key, detail_value "
        . "FROM ciniki_landingpage_settings "
        . "WHERE (page_id = '" . ciniki_core_dbQuote($ciniki, $page['id']) . "' OR page_id = 0) "
        . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
        . "ORDER BY page_id "       // Make sure page_id = 0 is first, and overwritten by same detail_key names for page
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.landingpages', 'settings');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['settings']) ) {
        $page['settings'] = $rc['settings'];
    } else {
        $page['settings'] = array();
    }

    //
    // Setup default settings if not specified
    //

    //
    // Load the theme settings and overrides for site-privatetheme settings
    //
    if( isset($page['settings']['page-privatetheme-id']) && $page['settings']['page-privatetheme-id'] > 0 ) {
        $settings['site-privatetheme-id'] = $page['settings']['page-privatetheme-id'];
        //
        // Get the theme details
        //
        $strsql = "SELECT id, permalink, last_updated "
            . "FROM ciniki_web_themes "
            . "WHERE ciniki_web_themes.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND ciniki_web_themes.id = '" . ciniki_core_dbQuote($ciniki, $settings['site-privatetheme-id']) . "' "
            . "ORDER BY date_added DESC "
            . "LIMIT 1 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'theme');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['theme']) ) {
            $page['settings']['page-privatetheme-permalink'] = $rc['theme']['permalink'];
            $settings['site-privatetheme-permalink'] = $rc['theme']['permalink'];
            $dt = new DateTime($rc['theme']['last_updated'], new DateTimeZone('UTC'));
            $page['settings']['page-privatetheme-last-updated'] = $dt->format('U');
        } else {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.landingpages.15', 'msg'=>'No theme specified'));
        }
        
        //
        // Load theme settings
        //
        $strsql = "SELECT detail_key, detail_value "
            . "FROM ciniki_web_theme_settings "
            . "WHERE ciniki_web_theme_settings.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
            . "AND ciniki_web_theme_settings.theme_id = '" . ciniki_core_dbQuote($ciniki, $settings['site-privatetheme-id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
        $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.web', 'settings');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['settings']) ) {
            $settings['theme'] = $rc['settings'];
        }
    }

    //
    // Load the landing page content
    //
    $strsql = "SELECT ciniki_landingpage_items.id, "
        . "ciniki_landingpage_items.menu_title, "
        . "ciniki_landingpage_items.permalink, "
        . "ciniki_landingpage_items.sequence, "
        . "ciniki_landingpage_items.item_type, "
        . "ciniki_landingpage_content.id AS content_id, "
        . "ciniki_landingpage_content.title, "
        . "ciniki_landingpage_content.content_type, "
        . "ciniki_landingpage_content.primary_image_id, "
        . "ciniki_landingpage_content.primary_image_caption, "
        . "ciniki_landingpage_content.content "
        . "FROM ciniki_landingpage_items, ciniki_landingpage_content "
        . "WHERE ciniki_landingpage_items.page_id = '" . ciniki_core_dbQuote($ciniki, $page['id']) . "' "
        . "AND ciniki_landingpage_items.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
        . "AND ciniki_landingpage_items.content_id = ciniki_landingpage_content.id "
        . "AND ciniki_landingpage_content.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
        . "ORDER BY ciniki_landingpage_items.sequence, ciniki_landingpage_items.menu_title "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.landingpages', array(
        array('container'=>'items', 'fname'=>'id',
            'fields'=>array('id', 'menu_title', 'permalink', 'sequence', 'item_type', 
                'content_id', 'title', 'content_type', 'primary_image_id', 'primary_image_caption', 'content')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['items']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.landingpages.16', 'msg'=>'Page does not exist'));
    }
    $page['items'] = $rc['items'];

    return array('stat'=>'ok', 'page'=>$page);
}
?>
