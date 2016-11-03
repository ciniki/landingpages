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
function ciniki_landingpages_pageContentAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'), 
        'menu_title'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Menu Title'), 
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Permalink'), 
        'sequence'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Sequence'), 
        'item_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'), 
        'redirect_url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Redirect URL'), 
        'item_module'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Module'), 
        'title'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Title'), 
        'content_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Content Type'), 
        'primary_image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'),
        'primary_image_caption'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image Caption'),
        'content'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Content'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to business_id as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'private', 'checkAccess');
    $rc = ciniki_landingpages_checkAccess($ciniki, $args['business_id'], 'ciniki.landingpages.pageContentAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( !isset($args['permalink']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['menu_title']);
    }

    //
    // Check the permalink doesn't already exist for this page
    //
    $strsql = "SELECT id "
        . "FROM ciniki_landingpage_items "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' " 
        . "AND page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' " 
        . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.landingpages', 'page');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num_rows'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.landingpages.6', 'msg'=>'You already have an page with this permalink, please choose another name'));
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.landingpages');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Add the content to the database
    //
    $rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.landingpages.content', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.landingpages');
        return $rc;
    }
    $args['content_id'] = $rc['id'];

    //
    // Add the item to the database
    //
    $rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.landingpages.item', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.landingpages');
        return $rc;
    }
    $args['item_id'] = $rc['id'];

    //
    // Update the sequences
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'sequencesUpdate');
    $rc = ciniki_core_sequencesUpdate($ciniki, $args['business_id'], 'ciniki.landingpages.item', 'page_id', $args['page_id'], $args['sequence'], -1);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.landingpages');
        return $rc;
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

    return array('stat'=>'ok', 'id'=>$args['content_id']);
}
?>
