<?php
//
// Description
// -----------
// This function will generate the content for the bottom of the landing pages.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_landingpages_web_footer($ciniki, $settings, $page) {
    global $start_time;

    //
    // Store the content
    //
    $content = '';
    $popup_box_content = '';
    $javascript = '';

    // Generate the footer content
    $content .= "<hr class='section-divider footer-section-divider' />\n";
    $content .= "<footer>";
    $content .= "<div class='footer-wrapper'>";

    //
    // Check for social media icons
    //
    $social = '';
    if( !isset($settings['theme']['footer-layout']) || strstr($settings['theme']['footer-layout'], 'social') !== FALSE ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'socialIcons');
        $rc = ciniki_web_socialIcons($ciniki, $settings, 'footer');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['social']) && $rc['social'] != '' ) {
            $social = $rc['social'];
        }
    }

    //
    // Check for copyright information
    //
    $copyright = '';
    if( !isset($settings['theme']['footer-layout']) || strstr($settings['theme']['footer-layout'], 'copyright') !== FALSE ) {
        if( isset($settings['theme']['footer-copyright-message']) && $settings['theme']['footer-copyright-message'] != '' ) {
            $copyright .= "<span class='copyright'>" . preg_replace('/{_year_}/', date('Y'), $settings['theme']['footer-copyright-message']) . "</span><br/>";
        } else {
            $copyright .= "<span class='copyright'>All content &copy; Copyright " . date('Y') . " by " . ((isset($settings['site-footer-copyright-name']) && $settings['site-footer-copyright-name'] != '')?$settings['site-footer-copyright-name']:$ciniki['tenant']['details']['name']) . ".</span><br/>";
        }
        if( isset($settings['site-footer-copyright-message']) && $settings['site-footer-copyright-message'] != '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $settings['site-footer-copyright-message'], 'copyright');   
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $copyright .= $rc['content'];
        }
    }

    //
    // Check for theme copyrights
    //
    if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/copyright.html') ) {
        $copyright .= "<span class='copyright'>" . file_get_contents($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/copyright.html') . "</span><br/>";
    }

    if( isset($ciniki['config']['ciniki.web']['poweredby.url']) && $ciniki['config']['ciniki.web']['poweredby.url'] != '' && $ciniki['config']['ciniki.core']['master_tnid'] != $ciniki['request']['tnid'] ) {
        $copyright .= "<span class='poweredby'>Powered by <a href='" . $ciniki['config']['ciniki.web']['poweredby.url'] . "'>" . $ciniki['config']['ciniki.web']['poweredby.name'] . "</a></span>";
    }

    //
    // Check if any links should be added to the footer
    //
    $links = '';
    $content_types = array();
    if( !isset($settings['theme']['footer-layout']) || strstr($settings['theme']['footer-layout'], 'links') !== FALSE ) {
        if( isset($settings['theme']['footer-subscription-agreement']) && $settings['theme']['footer-subscription-agreement'] == 'popup' 
            && isset($ciniki['tenant']['modules']['ciniki.info']['flags']) && ($ciniki['tenant']['modules']['ciniki.info']['flags']&0x02000000) > 0
            ) {
            $content_types[] = '26';
        }
        if( isset($settings['theme']['footer-privacy-policy']) && $settings['theme']['footer-privacy-policy'] == 'popup' 
            && isset($ciniki['tenant']['modules']['ciniki.info']['flags']) && ($ciniki['tenant']['modules']['ciniki.info']['flags']&0x8000) > 0 
            ) {
            $content_types[] = '16';
        }
    }

    //
    // Get the information for the links
    //
    if( count($content_types) > 0 ) {
        //
        // Setup the javascript for the popups
        //
        $javascript = ""
            . "var curPopup = '';"
            . "function popupShow(p) {"
            . "var e = document.getElementById(p);"
            . "e.style.display='block';"
            . "curPopup = p;"
            . "popupResize();"
            . "window.addEventListener('resize', popupResize);"
            . "};"
            . "function popupHide(p) {"
            . "var e = document.getElementById(p);"
            . "e.style.display='none';"
            . "curPopup = '';"
            . "window.removeEventListener('resize', popupResize);"
            . "};"
            . "function popupResize() {"
            . "var e = document.getElementById(curPopup);"
            . "var h = document.getElementById(curPopup+'-header');"
            . "var c = document.getElementById(curPopup+'-content');"
            . "var f = document.getElementById(curPopup+'-footer');"
            . "if(h!=null&&c!=null&&f!=null){"
                . "var s=h.parentNode.parentNode.currentStyle||window.getComputedStyle(h.parentNode.parentNode);"
                . "c.style.height=(window.innerHeight-h.clientHeight-f.clientHeight-(parseInt(s.marginTop)*2))+'px';"
            . "}"
            . "};"
            . "";
        //
        // Load the content to be setup for popups
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');
        $strsql = "SELECT content_type, title, permalink, content "
            . "FROM ciniki_info_content "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['tnid']) . "' "
            . "AND content_type IN (" . ciniki_core_dbQuoteIDs($ciniki, $content_types) . ") "
            . "ORDER BY content_type DESC "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'info');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['rows']) ) {
            foreach($rc['rows'] as $row) {
                if( $row['content'] == '' ) {
                    continue;
                }
                $links .= ($links!=''?' | ':'') . "<a href='javascript: popupShow(\"" . $row['permalink'] . "\");'>" . $row['title'] . "</a>";
                $popup_box_content .= "<div id='" . $row['permalink'] . "' class='popup-container' style='display:none;'>\n"
                    . "<div class='popup-wrapper'>\n"
                        . "<div class='popup-body'>"
                        . "<div id='" . $row['permalink'] . "-header' class='popup-header'>"
                            . "<button type='button' class='popup-button' onclick='popupHide(\"" . $row['permalink'] . "\");'>&times;</button>"
                            . "<h4 class='popup-title'>" . $row['title'] . "</h4>"
                        . "</div>"
                        . "<div id='" . $row['permalink'] . "-content' class='popup-content'>"
                        . $row['content']
                        . "</div>"
                        . "<div id='" . $row['permalink'] . "-footer' class='popup-footer'>"
                            . "<button type='button' class='popup-button' onclick='popupHide(\"" . $row['permalink'] . "\");'>Close</button>"
                        . "</div>"
                        . "</div>"
                    . "</div>"
                    . "</div>";
            }
        }
    }

    //
    // Decide how the footer should be laid out
    //
    if( isset($settings['theme']['footer-layout']) && $settings['theme']['footer-layout'] == 'copyright-links-social' ) {
        $content .= "<div class='copyright'>" . $copyright . "</div>";

        if( $links != '' ) {
            $content .= "<div class='links'>" . $links . "</div>";
        }

        if( $social != '' ) {
            $content .= "<div class='social-icons'>" . $social . "</div>";
        }

    } elseif( isset($settings['theme']['footer-layout']) && $settings['theme']['footer-layout'] == 'copyright-links' ) {
        $content .= "<div class='copyright'>" . $copyright . "</div>";

        if( $links != '' ) {
            $content .= "<div class='links'>" . $links . "</div>";
        }
    } else {
        if( $social != '' ) {
            $content .= "<div class='social-icons'>" . $social . "</div>";
        }

        if( $links != '' ) {
            $content .= "<div class='links'>" . $links . "</div>";
        }

        $content .= "<div class='copyright'>";
        $content .= $copyright;
        $content .= "</div>";
    }

    //
    // Extra information for the bottom of the page, error messages, debug info, etc
    //
    $content .= "<div id='x-info' class='x-info'>";
    // If there was an error page generated, see if we should put the error code in the footer for debug purposes.
    // This keeps it out of the way, but easy to tell people what to look for.
    if( isset($ciniki['request']['error_codes_msg']) && $ciniki['request']['error_codes_msg'] != '' ) {
        $content .= "<br/><span class='error_msg'>" . $ciniki['request']['error_codes_msg'] . "</span>";
    }
    $content .= "<span id='x-stats' class='x-stats' style='display:none;'>Execution: " . sprintf("%.4f", ((microtime(true)-$start_time)/60)) . "seconds</span>";
    $content .= "</div>";
    $content .= "</div>";

    $content .= "</footer>"
        . "";

    // Close page-container
    $content .= "</div>\n";

    //
    // Include any modal boxes
    //
    if( $popup_box_content != '' ) {
        $content .= $popup_box_content;
    }
    if( $javascript != '' ) {
        $content .= "<script type='text/javascript'>$javascript</script>";
    }
    if( isset($page['javascript']) && $page['javascript'] != '' ) {
        $content .= "<script type='text/javascript'>" . $page['javascript'] . "</script>";
    }

    $content .= "</body>"
        . "</html>"
        . "";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
