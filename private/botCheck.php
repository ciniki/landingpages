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
function ciniki_landingpages_botCheck($ciniki, $user_agent) {
    
    if( $user_agent == '' ) { return array('stat'=>'ok', 'bot'=>'no'); }

    if( strstr($user_agent, 'AdsBot-Google') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'AdsBot-Google-Mobile') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'AndroidDownloadManager/5.1.1') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'Mediapartners-Google') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'Baiduspider/') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'bingbot') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'Googlebot') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'MixrankBot') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'MJ12bot') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'spbot/') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'Yahoo Link Preview') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'Yahoo! Slurp') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'Twitterbot') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }
    if( strstr($user_agent, 'Wotbox') !== false ) { return array('stat'=>'ok', 'bot'=>'yes'); }

	return array('stat'=>'ok', 'bot'=>'no');
}
?>
