<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// 
// Returns
// -------
//
function ciniki_landingpages_cron_jobs($ciniki) {
    ciniki_cron_logMsg($ciniki, 0, array('code'=>'0', 'msg'=>'Checking for landingpages jobs', 'severity'=>'5'));

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'private', 'botCheck');

    //
    // Get the list of landingpage logs from the last 10 minutes to identify bots
    //
    $dt = new DateTime('now', new DateTimeZone('UTC'));
    $dt->sub(new DateInterval('PT10M'));
    $strsql = "SELECT id, tnid, user_agent, flags "
        . "FROM ciniki_landingpage_log "
        . "WHERE log_date > '" . $dt->format('Y-m-d H:i:s') . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.mail', 'last_sent');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.landingpages.1', 'msg'=>'Unable to get list of landingpage logs.', 'err'=>$rc['err']));
    }
    if( isset($rc['rows']) ) {
        foreach($rc['rows'] as $row) {
            $rc = ciniki_landingpages_botCheck($ciniki, $row['user_agent']);
            if( $rc['stat'] == 'ok' && $rc['bot'] == 'yes' ) {
                $strsql = "UPDATE ciniki_landingpage_log SET flags = flags | 0x01 WHERE id = '" . ciniki_core_dbQuote($ciniki, $row['id']) . "' ";
                $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.landingpages');
            }
        }
    }

    return array('stat'=>'ok');
}
