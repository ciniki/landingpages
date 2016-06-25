<?php
//
// Description
// -----------
// This function will update the settings based on submitted arguments from the UI.
//
// Arguments
// ---------
// ciniki:
// business_id:         The business ID to check the session user against.
// method:              The requested method.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_landingpages_settingsUpdate(&$ciniki, $business_id, $page_id) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');

    //
    // Get the list of existing settings
    //
    $strsql = "SELECT id, detail_key, detail_value "
        . "FROM ciniki_landingpage_settings "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND page_id = '" . ciniki_core_dbQuote($ciniki, $page_id) . "' "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.landingpages', array(
        array('container'=>'settings', 'fname'=>'detail_key',
            'fields'=>array('id', 'detail_key', 'detail_value')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['settings']) ) {
        $settings = array();
    } else {
        $settings = $rc['settings'];
    }

    //
    // The list of valid settings
    //
    $settings_fields = array(
        'page-theme',
        'page-layout',
        'page-privatetheme-id',
        'header-social-display',
        'header-image-display',
        'header-menu-display',
        'page-form',
        'page-form-above',
        'page-form-below',
    );

    //
    // Check each valid setting and see if a new value was passed in the arguments for it.
    // Insert or update the entry in the ciniki_donation_settings table
    //
    foreach($settings_fields as $field) {
        //
        // Check if the setting was passed as an argument
        //
        if( isset($ciniki['request']['args'][$field]) ) {
            //
            // Add if setting does not already exist
            //
            if( !isset($settings[$field]) ) {
                $args = array(
                    'page_id'=>$page_id,
                    'detail_key'=>$field,
                    'detail_value'=>$ciniki['request']['args'][$field]
                    );
                $rc = ciniki_core_objectAdd($ciniki, $business_id, 'ciniki.landingpages.setting', $args, 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            } 
            //
            // Update if setting exists and detail_value is different
            //
            elseif( $settings[$field]['detail_value'] != $ciniki['request']['args'][$field] ) {
                $args = array(
                    'detail_value'=>$ciniki['request']['args'][$field]
                    );
                $rc = ciniki_core_objectUpdate($ciniki, $business_id, 'ciniki.landingpages.setting', $settings[$field]['id'], $args, 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
        }
    }

    return array('stat'=>'ok');
}
?>
