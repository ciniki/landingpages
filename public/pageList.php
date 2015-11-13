<?php
//
// Description
// -----------
// This method returns the list of landing pages.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get landingpages for.
//
// Returns
// -------
//
function ciniki_landingpages_pageList($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'org'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Organization'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
    //  
    // Check access to business_id as owner, or sys admin. 
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'private', 'checkAccess');
    $rc = ciniki_landingpages_checkAccess($ciniki, $args['business_id'], 'ciniki.landingpages.pageList');
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
	// Get the list of landing pages
	//
	$rsp = array('stat'=>'ok');
	$strsql = "SELECT ciniki_landingpages.id, "
		. "ciniki_landingpages.name, "
		. "ciniki_landingpages.title, "
		. "ciniki_landingpages.status, "
		. "ciniki_landingpages.status AS status_text "
		. "FROM ciniki_landingpages "
		. "WHERE ciniki_landingpages.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "ORDER BY ciniki_landingpages.status, ciniki_landingpages.name "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	if( isset($args['org']) && $args['org'] == 'status' ) {
		$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.landingpages', array(
			array('container'=>'status', 'fname'=>'status', 
				'fields'=>array('status_text'),
				'maps'=>array('status_text'=>$maps['page']['status'])),
			array('container'=>'pages', 'fname'=>'id', 
				'fields'=>array('id', 'name', 'title', 'status', 'status_text'),
				'maps'=>array('status_text'=>$maps['page']['status'])),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['status']) ) {
			$rsp['status'] = $rc['status'];
		} else {
			$rsp['status'] = array();
		}
	} else {
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.landingpages', array(
			array('container'=>'pages', 'fname'=>'id',
				'fields'=>array('id', 'name', 'title', 'status', 'status_text'),
				'maps'=>array('status_text'=>$maps['page']['status'])),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['pages']) ) {
			$rsp['pages'] = $rc['pages'];
		} else {
			$rsp['pages'] = array();
		}
	}

	return $rsp;
}
?>
