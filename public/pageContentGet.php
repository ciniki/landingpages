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
// business_id:     The ID of the business to get landingpages for.
//
// Returns
// -------
//
function ciniki_landingpages_pageContentGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'), 
        'content_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Content'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //  
    // Check access to business_id as owner, or sys admin. 
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'private', 'checkAccess');
    $rc = ciniki_landingpages_checkAccess($ciniki, $args['business_id'], 'ciniki.landingpages.pageContentGet');
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
    if( $args['content_id'] == 0 ) {
        //
        // Get the next sequence number
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'sequencesNext');
        $rc = ciniki_core_sequencesNext($ciniki, $args['business_id'], 'ciniki.landingpages.item', 'page_id', $args['page_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $sequence = $rc['sequence'];

        //
        // Default values for new content
        //
        $rsp = array('stat'=>'ok', 'content'=>array(
            'id'=>'0',
            'menu_title'=>'',
            'permalink'=>'',
            'sequence'=>$sequence,
            'item_type'=>'10',
            'content_id'=>'0',
            'redirect_url'=>'',
            'item_module'=>'',
            'title'=>'',
            'content_type'=>'10',
            'primary_image_id'=>'0',
            'primary_image_caption'=>'',
            'content'=>'',
            ));

        return $rsp;
    }

    //
    // Get the content
    //
    $rsp = array('stat'=>'ok');
    $strsql = "SELECT ciniki_landingpage_items.id AS item_id, "
        . "ciniki_landingpage_items.menu_title, "
        . "ciniki_landingpage_items.permalink, "
        . "ciniki_landingpage_items.sequence, "
        . "ciniki_landingpage_items.item_type, "
        . "ciniki_landingpage_items.redirect_url, "
        . "ciniki_landingpage_items.item_module, "
        . "ciniki_landingpage_content.title, "
        . "ciniki_landingpage_content.content_type, "
        . "ciniki_landingpage_content.primary_image_id, "
        . "ciniki_landingpage_content.primary_image_caption, "
        . "ciniki_landingpage_content.content "
        . "FROM ciniki_landingpage_items, ciniki_landingpage_content "
        . "WHERE ciniki_landingpage_items.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_landingpage_items.page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
        . "AND ciniki_landingpage_items.content_id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
        . "AND ciniki_landingpage_items.content_id = ciniki_landingpage_content.id "
        . "AND ciniki_landingpage_content.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.landingpages', 'content');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['content']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2680', 'msg'=>'Content does not exist'));
    }
    if( isset($rc['content']) ) {
        $rsp['content'] = $rc['content'];
    } else {
        $rsp['content'] = array();
    }

    return $rsp;
}
?>
