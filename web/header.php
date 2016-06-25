<?php
//
// Description
// -----------
// This function will generate the header for landing pages. This is based on web/private/generatePageHeader.php but
// has different logic to make the landing pages more efficient.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
// title:           The title to use for the page.
//
// Returns
// -------
//
function ciniki_landingpages_web_header($ciniki, $settings, $page) {

    //
    // Store the header content
    //
    $content = '';

    // Used if there is a redirect to another site
    $page_home_url = $ciniki['request']['base_url'] . '/';
    if( isset($settings['page-home-url']) && $settings['page-home-url'] != '' ) {
        $page_home_url = $settings['page-home-url'];
    }

    // Generate the head content
    $content .= "<!DOCTYPE html>\n"
        . "<html>\n"
        . "<head>\n"
        . "<title>" . $ciniki['business']['details']['name'];
    if( isset($page['short_title']) && $page['short_title'] != '' ) {
        $content .= " - " . $page['short_title'];
    } elseif( isset($page['title']) && $page['title'] != '' ) {
        $content .= " - " . $page['title'];
    }
    $content .= "</title>\n"
        . "<link rel='icon' href='/ciniki-mods/core/ui/themes/default/img/favicon.png' type='image/png' />\n"
        . "";

    if( isset($ciniki['business']['modules']['ciniki.web']['flags']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x0100) > 0 ) {
        //
        // FIXME: Check if theme files in directory are up to date
        //
        if( !isset($page['settings']['page-privatetheme-id'])
            || !isset($page['settings']['page-privatetheme-permalink']) 
            || $page['settings']['page-privatetheme-permalink'] == ''
            || !file_exists($ciniki['business']['web_cache_dir'] . '/theme-' . $page['settings']['page-privatetheme-permalink']) 
            || !isset($page['settings']['page-privatetheme-last-updated']) 
            || filemtime($ciniki['business']['web_cache_dir'] . '/theme-' . $page['settings']['page-privatetheme-permalink']) < $page['settings']['page-privatetheme-last-updated']
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'updatePrivateTheme');
            $rc = ciniki_web_updatePrivateTheme($ciniki, $ciniki['request']['business_id'], $settings, $page['settings']['page-privatetheme-id']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }

        if( isset($page['settings']['page-privatetheme-id']) && $page['settings']['page-privatetheme-id'] > 0 ) {
            //
            // Check for remote CSS files FIXME: Move into theme_settings
            //
            $strsql = "SELECT ciniki_web_theme_content.id, "
                . "ciniki_web_theme_content.content_type, "
                . "ciniki_web_theme_content.media, "
                . "ciniki_web_theme_content.content "
                . "FROM ciniki_web_theme_content "
                . "WHERE ciniki_web_theme_content.theme_id = '" . ciniki_core_dbQuote($ciniki, $page['settings']['page-privatetheme-id']) . "' "
                . "AND ciniki_web_theme_content.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
                . "AND ciniki_web_theme_content.content_type = 'csshref' "
                . "AND ciniki_web_theme_content.status = 10 "
                . "ORDER BY ciniki_web_theme_content.media, ciniki_web_theme_content.sequence "
                . "";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
            $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
                array('container'=>'links', 'fname'=>'id', 
                    'fields'=>array('id', 'media', 'content')),
                ));
            if( $rc['stat'] == 'ok' && isset($rc['links']) ) {
                foreach($rc['links'] as $link_id => $link) {
                    $content .= "<link type='text/css' rel='stylesheet' href='" . $link['content'] . "' media='" . $link['media'] . "' />\n";
                }
            }
        }
    }
    //
    // Add required layout css files
    //
    if( file_exists($ciniki['request']['layout_dir'] . '/' . $settings['site-layout'] . '/global.css') ) {
        $content .= "<style>" . file_get_contents($ciniki['request']['layout_dir'] . '/' . $settings['site-layout'] . '/global.css') . "</style>\n";
    } else if( $settings['site-layout'] != 'private' && file_exists($ciniki['request']['layout_dir'] . '/default/global.css') ) {
        $content .= "<style>" . file_get_contents($ciniki['request']['layout_dir'] . '/default/global.css') . "</style>\n";
    }
    if( file_exists($ciniki['request']['layout_dir'] . '/' . $settings['site-layout'] . '/layout.css') ) {
        $content .= "<style>@media (min-width: 33.236em) {" . file_get_contents($ciniki['request']['layout_dir'] . '/' . $settings['site-layout'] . '/layout.css') . "}</style>\n"
            . "<!--[if (lt IE 9) & (!IEMobile)]>\n"
            . "<script>\n"
                . "document.createElement('hgroup');\n"
                . "document.createElement('header');\n"
                . "document.createElement('nav');\n"
                . "document.createElement('section');\n"
                . "document.createElement('article');\n"
                . "document.createElement('aside');\n"
                . "document.createElement('footer');\n"
            . "</script>\n"
            . "<![endif]-->\n"
            . "";
    } else if( $settings['site-layout'] != 'private' && file_exists($ciniki['request']['layout_dir'] . '/default/layout.css') ) {
        $content .= "<style>@media (min-width: 33.236em) {" . file_get_contents($ciniki['request']['layout_dir'] . '/default/layout.css') . "}</style>\n";
    }

    //
    // Check for ie8.css file in the layout directory
    //
    if( file_exists($ciniki['request']['layout_dir'] . '/' . $settings['site-layout'] . '/ie8.css') ) {
        $content .= "<!--[if IE 8]>\n"
            . "<style>" . file_get_contents($ciniki['request']['layout_dir'] . '/' . $settings['site-layout'] . '/ie8.css') . "</style>\n"
            . "<![endif]-->\n"
            . "";
    } else if( $settings['site-layout'] != 'private' && file_exists($ciniki['request']['layout_dir'] . '/default/ie8.css') ) {
        $content .= "<!--[if IE 8]>\n"
            . "<style>" . file_get_contents($ciniki['request']['layout_dir'] . '/default/ie8.css') . "</style>\n"
            . "<![endif]-->\n"
            . "";
    } 

    //
    // Add the theme files
    //
    if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/style.css') ) {
        $content .= "<style>" . file_get_contents($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/style.css') . "</style>\n";
        if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/extras.css') ) {
            $content .= "<style>" . file_get_contents($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/extras.css') . "</style>\n";
        }
        if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/ie9.css') ) {
            $content .= "<!--[if (IE 9) & (!IEMobile)]>\n"
                . "<style>" . file_get_contents($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/ie9.css') . "</style>\n"
                . "<![endif]-->\n";
        }
        if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/ie8.css') ) {
            $content .= "<!--[if (IE 8) & (!IEMobile)]>\n"
                . "<style>" . file_get_contents($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/ie8.css') . "</style>\n"
                . "<![endif]-->\n";
        }
        if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/ie.css') ) {
            $content .= "<!--[if (lt IE 8)]>\n"
                . "<style>" . file_get_contents($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/ie.css') . "</style>\n"
                . "<![endif]-->\n";
        }
    } else if( $settings['site-theme'] != 'private' && file_exists($ciniki['request']['theme_dir'] . '/default/style.css') ) {
        $content .= "<style>" . file_get_contents($ciniki['request']['theme_dir'] . '/default/style.css') . "</style>\n";
        if( file_exists($ciniki['request']['theme_dir'] . '/default/ie9.css') ) {
            $content .= "<!--[if (IE 9) & (!IEMobile)]>\n"
                . "<style>" . file_get_contents($ciniki['request']['theme_dir'] . '/default/ie.css') . "</style>\n"
                . "<![endif]-->\n";
        }
        if( file_exists($ciniki['request']['theme_dir'] . '/default/ie8.css') ) {
            $content .= "<!--[if (IE 8) & (!IEMobile)]>\n"
                . "<style>" . file_get_contents($ciniki['request']['theme_dir'] . '/default/ie8.css') . "</style>\n"
                . "<![endif]-->\n";
        }
        if( file_exists($ciniki['request']['theme_dir'] . '/default/ie.css') ) {
            $content .= "<!--[if (lt IE 8)]>\n"
                . "<style>" . file_get_contents($ciniki['request']['theme_dir'] . '/default/ie.css') . "</style>\n"
                . "<![endif]-->\n";
        }
    }

    //
    // Check for private theme files
    //
    if( isset($ciniki['business']['modules']['ciniki.web']['flags']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x0100) > 0 
        && isset($settings['site-privatetheme-permalink']) && $settings['site-privatetheme-permalink'] != '' 
        ) {
        $theme_cache_dir = $ciniki['business']['web_cache_dir'] . '/theme-' . $settings['site-privatetheme-permalink'];
        //
        // Include the private theme files
        //
        if( file_exists($theme_cache_dir . '/style.css') ) {
            $content .= "<style>" . file_get_contents($theme_cache_dir . '/style.css') . "</style>\n";
        }
        if( file_exists($theme_cache_dir . '/code.js') ) {
            $content .= "<script async type='text/javascript'>" . file_get_contents($theme_cache_dir . '/code.js') . "</script>\n";
        }
    }

    //
    // Check head links
    //
    if( isset($ciniki['response']['head']['links']) ) {
        foreach($ciniki['response']['head']['links'] as $link) {
            $content .= "<link rel='" . $link['rel'] . "'" . (isset($link['title'])?" title='" . $link['title'] . "'":'') . " href='" . $link['href'] . "'/>\n";
        }
    }

    //
    // Check for head scripts
    //
    if( isset($ciniki['response']['head']['scripts']) ) {
        foreach($ciniki['response']['head']['scripts'] as $script) {
            $content .= "<script src='" . $script['src'] . "' type='" . $script['type'] . "'></script>\n";
        }
    }
    
    //
    // Header to support mobile device resize
    //
    $content .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
    $content .= '<meta charset="UTF-8">' . "\n";

    //
    // Check for header Open Graph (Facebook) object information, for better linking into facebook
    //
    if( isset($ciniki['response']['head']['og']) ) {
        $og_site_name = $ciniki['business']['details']['name'];
        foreach($ciniki['response']['head']['og'] as $og_type => $og_value) {
            if( $og_value != '' ) {
                if( $og_type == 'site_name' ) {
                    $og_site_name = $og_value;
                }
                $content .= '<meta property="og:' . $og_type . '" content="' . preg_replace('/"/', "'", $og_value) . '"/>' . "\n";
            }
        }
        if( $og_site_name != '' ) {
            $content .= "<meta property=\"og:site_name\" content=\"" . preg_replace('/"/', "\'", $og_site_name) . "\"/>\n";
        }
        if( $ciniki['response']['head']['og']['title'] == '' && isset($page['title']) && $page['title'] != '' ) {
            $content .= '<meta property="og:title" content="' . $ciniki['business']['details']['name'] . ' - ' . $page['title'] . '"/>' . "\n";
        }
    }

    if( isset($ciniki['request']['inline_javascript']) && $ciniki['request']['inline_javascript'] != '' ) {
        $content .= $ciniki['request']['inline_javascript'];
    }

    //
    // Include google analytics
    //
    if( isset($settings['site-google-analytics-account']) && $settings['site-google-analytics-account'] != '' ) {
        $content .= "<script type='text/javascript'>\n"
            . "var _gaq = _gaq || [];\n"
            . "_gaq.push(['_setAccount', '" . $settings['site-google-analytics-account'] . "']);\n"
            . "_gaq.push(['_trackPageview']);\n"
            . "(function() {\n"
                . "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n"
                . "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n"
                . "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n"
            . "})();\n"
            . "</script>\n"
            . "";
    }

    //
    // Check if there is custom CSS to include
    //
    if( isset($settings['site-custom-css']) && $settings['site-custom-css'] ) {
        $content .= "<style>" . $settings['site-custom-css'] . "</style>";
    }

    $content .= "</head>\n";

    // Generate header of the page
    $content .= "<body";
    if( isset($ciniki['request']['onresize']) && $ciniki['request']['onresize'] != '' ) {
        $content .= " onresize='" . $ciniki['request']['onresize'] . "'";
    }
    if( isset($ciniki['request']['onload']) && $ciniki['request']['onload'] != '' ) {
        $content .= " onload='" . $ciniki['request']['onload'] . "'";
    }
    $content .= ">\n";

    // Check for social media icons
    $social_icons = '';
    if( !isset($page['settings']['header-social-display']) || $page['settings']['header-social-display'] == 'yes' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'socialIcons');
        $rc = ciniki_web_socialIcons($ciniki, $settings, 'header');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['social']) && $rc['social'] != '' ) {
            $social_icons = $rc['social'];
        }
    }

    //
    // Check if we are to display a sign in button
    //
    $signin_content = '';
    $signin_content .= "<div class='signin'><div class='signin-wrapper hide-babybear'><span class='social-icons'>$social_icons</span></div></div>";

    //
    // Setup the page-container
    //
    $content .= "<div id='page-container'";
    $page_container_class = 'ciniki-landingpages';
//  if( isset($ciniki['request']['page-container-class']) && $ciniki['request']['page-container-class'] != '' ) {
//      $page_container_class = $ciniki['request']['page-container-class'];
//  }
    if( $signin_content != '' ) {
        if( $page_container_class != '' ) { $page_container_class .= " "; }
        $page_container_class .= 'signin';
    }
    if( isset($settings['site-logo-display']) && $settings['site-logo-display'] == 'yes' 
        && isset($ciniki['business']['details']['logo_id']) && $ciniki['business']['details']['logo_id'] > 0 ) {
        if( $page_container_class != '' ) { $page_container_class .= " "; }
        $page_container_class .= 'logo';
    }
    if( $page_container_class != '' ) {
        $content .= " class='$page_container_class'";
    }
    $content .= ">\n";

    $content .= "<header>\n";
    $content .= "<div class='header-wrapper'>\n";

    // Add signin button if any.
    $content .= $signin_content;

    //
    // Setup the header image
    //
    $site_header_image = '';
    if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        if( !isset($settings['site-header-image-size']) || $settings['site-header-image-size'] == 'medium' ) {
            $page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 125, 90);
        } elseif( $settings['site-header-image-size'] == 'small' ) {
            $page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 100, 90);
        } elseif( $settings['site-header-image-size'] == 'large' ) {
            $page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 150, 90);
        } elseif( $settings['site-header-image-size'] == 'xlarge' ) {
            $page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 200, 90);
        } elseif( $settings['site-header-image-size'] == 'xxlarge' ) {
            $page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 300, 90);
        } elseif( $settings['site-header-image-size'] == 'original' ) {
            $page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 0, 90);
        }
    }

    //
    // Decide if there is a header image to be displayed, or display an h1 title
    //
    $content .= "<div class='logo-nav-wrapper'>\n";
    if( !isset($settings['site-header-title']) || $settings['site-header-title'] == 'yes' ) {
        $content .= "<hgroup>\n";
        if( isset($page_home_image) && $page_home_image['stat'] == 'ok' ) {
            $content .= "<div class='title-logo'>"
                . "<img alt='Home' src='" . $page_home_image['url'] . "' />"
                . "</div>";
        }
        if( isset($ciniki['business']['details']['tagline']) && $ciniki['business']['details']['tagline'] != '' ) {
            $content .= "<div class='title-block'>";
        } else {
            $content .= "<div class='title-block no-tagline'>";
        }
        $content .= "<h1 id='site-title'>";
        $content .= "<span class='title'>" . $ciniki['business']['details']['name'] . "</span></h1>\n";
        if( isset($ciniki['business']['details']['tagline']) && $ciniki['business']['details']['tagline'] != '' ) {
            $content .= "<h2 id='site-description'>" . $ciniki['business']['details']['tagline'] . "</h2>\n";
        }
        $content .= "</div>";
        $content .= "</hgroup>\n";
    } else {
        $content .= "<hgroup class='header-image'>\n";
        $content .= "<span>";
            if( isset($page_home_image) && $page_home_image['stat'] == 'ok' ) {
                $content .= "<img alt='Home' src='" . $page_home_image['url'] . "' />";
            }
        $content .= "</span>\n";
        $content .= "</hgroup>";
    }

    $content .= "</div>\n";
    $content .= "</div>\n";
    $content .= "</header>\n"
        . "";
    $content .= "<hr class='section-divider header-section-divider' />\n";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
