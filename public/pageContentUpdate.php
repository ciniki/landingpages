<?php
//
// Description
// -----------
// This method will add a new landing page for the business.
//
// Arguments
// ---------
//
// Returns
// -------
// <rsp stat="ok" id="42">
//
function ciniki_landingpages_pageContentUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'), 
        'content_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Content'), 
        'menu_title'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Menu Title'), 
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Permalink'), 
        'sequence'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Sequence'), 
        'item_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'), 
        'redirect_url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Redirect URL'), 
        'item_module'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Module'), 
        'title'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Title'), 
        'content_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Content Type'), 
        'primary_image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'),
        'primary_image_caption'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image Caption'),
        'content'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Content'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to business_id as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'private', 'checkAccess');
    $rc = ciniki_landingpages_checkAccess($ciniki, $args['business_id'], 'ciniki.landingpages.pageContentUpdate');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current item
    //
    $strsql = "SELECT ciniki_landingpage_items.id, "
        . "ciniki_landingpage_items.sequence, "
        . "ciniki_landingpage_items.content_id "
        . "FROM ciniki_landingpage_items "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' " 
        . "AND page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' " 
        . "AND content_id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' " 
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.landingpages', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['item']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.landingpages.8', 'msg'=>'The content does not exist'));
    }
    $item = $rc['item'];
    $args['item_id'] = $rc['item']['id'];

    //
    // Check the permalink doesn't already exist for this page
    //
    if( isset($args['menu_title']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['menu_title']);
        $strsql = "SELECT id "
            . "FROM ciniki_landingpage_items "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' " 
            . "AND page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' " 
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['item_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.landingpages', 'page');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['num_rows'] > 0 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.landingpages.9', 'msg'=>'You already have an page with this permalink, please choose another name'));
        }
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.landingpages');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Add the item to the database
    //
    $rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.landingpages.item', $args['item_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.landingpages');
        return $rc;
    }

    //
    // Add the content to the database
    //
    $rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.landingpages.content', $args['content_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.landingpages');
        return $rc;
    }

    //
    // Update the sequences
    //
    if( isset($args['sequence']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'sequencesUpdate');
        $rc = ciniki_core_sequencesUpdate($ciniki, $args['business_id'], 'ciniki.landingpages.item', 'page_id', $args['page_id'], $args['sequence'], $item['sequence']);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.landingpages');
            return $rc;
        }
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.landingpages');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the business modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
    ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'landingpages');

    return array('stat'=>'ok');
}
?>
