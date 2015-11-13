<?php
//
// Description
// -----------
// The module flags
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_landingpages_maps($ciniki) {
	$maps = array();
	$maps['page'] = array('status'=>array(
		'0'=>'In Development',
		'10'=>'Active',
		'40'=>'Redirect',
		'50'=>'Removed',
		));

	return array('stat'=>'ok', 'maps'=>$maps);
}
?>
