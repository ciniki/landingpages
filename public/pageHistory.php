<?php
//
// Description
// -----------
// This method will return the list of actions that were applied to an element of an page. 
// This method is typically used by the UI to display a list of changes that have occured 
// on an element through time. This information can be used to revert elements to a previous value.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the details for.
// page_id:			    The ID of the page to get the history for.
// field:				The field to get the history for. This can be any of the elements 
//						returned by the ciniki.landingpages.get method.
//
// Returns
// -------
// <history>
// <action user_id="2" date="May 12, 2012 10:54 PM" value="Page Name" age="2 months" user_display_name="Andrew" />
// ...
// </history>
//
function ciniki_landingpages_pageHistory($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'field'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'private', 'checkAccess');
	$rc = ciniki_landingpages_checkAccess($ciniki, $args['business_id'], 'ciniki.landingpages.pageHistory');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
	return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.landingpages', 'ciniki_landingpage_history', $args['business_id'], 'ciniki_landingpages', $args['page_id'], $args['field']);
}
?>
