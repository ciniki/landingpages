<?php
//
// Description
// ===========
// This method will update an page in the database.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the page is attached to.
// name:                (optional) The new name of the page.
// url:                 (optional) The new URL for the page website.
// description:         (optional) The new description for the page.
// start_date:          (optional) The new date the page starts.  
// end_date:            (optional) The new date the page ends, if it's longer than one day.
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_landingpages_pageUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
        'permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Permalink'), 
        'title'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Title'), 
        'short_title'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Short Title'), 
        'subtitle'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Subtitle'), 
        'tagline'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Tagline'), 
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Flags'),
        'primary_image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'),
        'redirect_url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Redirect URL'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'private', 'checkAccess');
    $rc = ciniki_landingpages_checkAccess($ciniki, $args['tnid'], 'ciniki.landingpages.pageUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    if( isset($args['permalink']) ) {
        //
        // Make sure the permalink is unique
        //
        $strsql = "SELECT id, name, permalink "
            . "FROM ciniki_landingpages "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.landingpages', 'page');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['num_rows'] > 0 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.landingpages.11', 'msg'=>'You already have an page with this name, please choose another name'));
        }
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.landingpages');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Update the page in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.landingpages.page', $args['page_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.landingpages');
        return $rc;
    }

    //
    // Update the settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'private', 'settingsUpdate');
    $rc = ciniki_landingpages_settingsUpdate($ciniki, $args['tnid'], $args['page_id']);
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
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'landingpages');

    return array('stat'=>'ok');
}
?>
