<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_landingpages_objects($ciniki) {
    
    $objects = array();
    $objects['page'] = array(
        'name'=>'Page',
        'sync'=>'yes',
        'table'=>'ciniki_landingpages',
        'fields'=>array(
            'name'=>array(),
            'permalink'=>array(),
            'title'=>array(),
            'short_title'=>array('default'=>''),
            'subtitle'=>array('default'=>''),
            'tagline'=>array('default'=>''),
            'status'=>array('default'=>'0'),
            'flags'=>array('default'=>'0'),
            'primary_image_id'=>array('default'=>'0'),
            'redirect_url'=>array('default'=>''),
            ),
        'history_table'=>'ciniki_landingpage_history',
        );
    $objects['item'] = array(
        'name'=>'Page Item',
        'sync'=>'yes',
        'table'=>'ciniki_landingpage_items',
        'fields'=>array(
            'page_id'=>array('ref'=>'ciniki.landingpages.page'),
            'menu_title'=>array(),
            'permalink'=>array('default'=>''),
            'sequence'=>array(),
            'item_type'=>array('default'=>'10'),
            'content_id'=>array('ref'=>'ciniki.landingpages.content'),
            'redirect_url'=>array('default'=>''),
            'item_module'=>array('default'=>''),
            ),
        'history_table'=>'ciniki_landingpage_history',
        );
    $objects['content'] = array(
        'name'=>'File',
        'sync'=>'yes',
        'table'=>'ciniki_landingpage_content',
        'fields'=>array(
            'title'=>array('default'=>''),
            'content_type'=>array('default'=>'10'),
            'primary_image_id'=>array('default'=>'0'),
            'primary_image_caption'=>array('default'=>''),
            'content'=>array(),
            ),
        'history_table'=>'ciniki_landingpage_history',
        );
    $objects['setting'] = array(
        'name'=>'Settings',
        'sync'=>'yes',
        'table'=>'ciniki_landingpage_settings',
        'fields'=>array(
            'page_id'=>array('ref'=>'ciniki.landingpages.page'),
            'detail_key'=>array(),
            'detail_value'=>array(),
            ),
        'history_table'=>'ciniki_landingpage_history',
        );
    
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
