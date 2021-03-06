<?php
//
// Description
// -----------
// This method returns the page details, or the default page info.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get landingpages for.
//
// Returns
// -------
//
function ciniki_landingpages_pageGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //  
    // Check access to tnid as owner, or sys admin. 
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'private', 'checkAccess');
    $rc = ciniki_landingpages_checkAccess($ciniki, $args['tnid'], 'ciniki.landingpages.pageGet');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //  
    // Load the maps
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'private', 'maps');
    $rc = ciniki_landingpages_maps($ciniki);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $maps = $rc['maps'];

    //
    // Send back the default information
    //
    if( $args['page_id'] == 0 ) {
        $rsp = array('stat'=>'ok', 'page'=>array(
            'id'=>'0',
            'name'=>'',
            'permalink'=>'',
            'title'=>'',
            'short_title'=>'',
            'subtitle'=>'',
            'tagline'=>'',
            'status'=>'0',
            'flags'=>'0',
            'primary_image_id'=>'0',
            'redirect_url'=>'',
            'theme'=>'default',
            'layout'=>'default',
            'privatetheme-id'=>'0',
            'header-social-display'=>'yes',
            'header-image-display'=>'yes',
            'header-menu-display'=>'yes',
            'page-form'=>'0',
            'page-form-above'=>'no',
            'page-form-below'=>'yes',
            'footer-social-display'=>'yes',
            'items'=>array(),
            ));
    } else {

        //
        // Get the list of landing pages
        //
        $strsql = "SELECT ciniki_landingpages.id, "
            . "ciniki_landingpages.name, "
            . "ciniki_landingpages.permalink, "
            . "ciniki_landingpages.title, "
            . "ciniki_landingpages.short_title, "
            . "ciniki_landingpages.subtitle, "
            . "ciniki_landingpages.tagline, "
            . "ciniki_landingpages.status, "
            . "ciniki_landingpages.flags, "
            . "ciniki_landingpages.primary_image_id, "
            . "ciniki_landingpages.redirect_url "
            . "FROM ciniki_landingpages "
            . "WHERE ciniki_landingpages.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_landingpages.id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.landingpages', 'page');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['page']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.landingpages.10', 'msg'=>'Page does not exist'));
        }
        $page = $rc['page'];

        //
        // Get the settings for the page
        //
        $strsql = "SELECT detail_key, detail_value "
            . "FROM ciniki_landingpage_settings "
            . "WHERE ciniki_landingpage_settings.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_landingpage_settings.page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
        $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.landingpages', 'settings');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['settings']) ) {
            $page = array_merge($rc['settings'], $page);
        }

        //
        // Get the content for the page
        //
        $strsql = "SELECT ciniki_landingpage_items.id AS item_id, "
            . "ciniki_landingpage_items.menu_title, "
            . "ciniki_landingpage_items.permalink, "
            . "ciniki_landingpage_items.sequence, "
            . "ciniki_landingpage_items.item_type, "
            . "ciniki_landingpage_items.redirect_url, "
            . "ciniki_landingpage_items.item_module, "
            . "ciniki_landingpage_content.id, "
            . "ciniki_landingpage_content.title, "
            . "ciniki_landingpage_content.content_type "
            . "FROM ciniki_landingpage_items, ciniki_landingpage_content "
            . "WHERE ciniki_landingpage_items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_landingpage_items.page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
            . "AND ciniki_landingpage_items.content_id = ciniki_landingpage_content.id "
            . "AND ciniki_landingpage_content.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY ciniki_landingpage_items.sequence, ciniki_landingpage_items.menu_title "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.landingpages', array(
            array('container'=>'items', 'fname'=>'permalink', 
                'fields'=>array('id', 'item_id', 'menu_title', 'permalink', 'sequence', 'item_type', 'redirect_url', 'item_module',
                    'title', 'content_type')),
                ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['items']) ) {
            $page['items'] = array();
        } else {
            $page['items'] = $rc['items'];
        }
        
        $rsp = array('stat'=>'ok', 'page'=>$page);
    }

    //
    // Get the forms
    //
    $rsp['forms'] = array();
    foreach($ciniki['tenant']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'landingpageForms');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $args['tnid'], array());
            if( $rc['stat'] == 'ok' && isset($rc['forms']) ) {
                $rsp['forms'] = array_merge($rsp['forms'], $rc['forms']);
            }
        }
    }

    //
    // Get the private themes for the tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'hooks', 'privateThemes');
    $rc = ciniki_web_hooks_privateThemes($ciniki, $args['tnid'], array());
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['themes']) ) {
        $rsp['privatethemes'] = $rc['themes'];    
    }

    return $rsp;
}
?>
