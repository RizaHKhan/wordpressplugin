<?php
// Search for ALERT for potential issues in code.

// TABLES OF INTEREST: 
// wp_adrotate
// wp_adrotate-groups
// wp_adrotate-linkmeta
// wp_adrotate_schedule

# Set Headers
header("Content-Description: File Transfer");
header("Content-type: application/json; charset=utf-8");
header('Content-Disposition: attachment; filename="tojson.json"');

# Call global variable for database access
require_once('../../../wp-config.php');
global $wpdb;

# Declaring Variables:
$advertisers = array();
$zones       = array();
$zones_map   = array();
$banners     = array();
$campaigns   = array();

# Zones array logic
$groups = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."adrotate_groups`", OBJECT_K);

foreach($groups as $row) 
{
    $zones[] = array(
        'id' => intval($row->id),
        'name' => $row->name,
        'alias' => "group_" . $row->id
    );
}

# group table logic
$adrotate = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."adrotate`", OBJECT_K);

foreach($adrotate as $row)
{
    $tmp = explode('-', $row->title);
    
    if(count($tmp) > 1) {
        $advertiser = trim(htmlspecialchars($tmp[0]));
        $ad = $advertiser . ' - ' . htmlspecialchars(trim($tmp[1]));
    } else {
        $advertiser = trim(htmlspecialchars($tmp[0]));
        $ad = trim(htmlspecialchars($tmp[0]));
    }

    $advertiser = preg_replace('#\-?\s*\d+\s*x\s*\d+\s*\-?#', '', $advertiser);
    $advertiser = stripslashes(html_entity_decode($advertiser, ENT_QUOTES, 'UTF-8'));
    $ad         = $advertiser;
    $advertiser = str_ireplace('rotator', '', $advertiser);
    
    $advertiser = array (
        'id'   => $row->id,
        'name' => $advertiser,
	    'users' => array (),
        'campaigns' => array ()
    );

    $banner = array (
        'id'   => $row->id,
        'name' => $ad,
        'zone_ids' => array(),
        'destination' => null
    );

    $linkmeta = $wpdb->get_results("SELECT `group`, `schedule` FROM `".$wpdb->prefix."adrotate_linkmeta`", OBJECT_K);
    
    foreach($linkmeta as $row) {
        
        if ($row->id == '4') {
            #print_r($row);
            #print_r($camp);
        }

        // ALERT
        if($banner->zone_ids) {
            if($row->group) {
                $banner->zone_ids = $row->group;
            }
        }        
        
        $code = clean(stripslashes(html_entity_decode($row->bannercode, ENT_QUOTES, 'UTF-8')));
        $code = str_replace('%link%', '{BS:CLICK_URL}', $code);        
        
        if ($code) {
            # Check the banner type
            if(strstr($code, '<table') || strstr($code, '<script') || strstr($code, '<iframe'))
            {
                $code = str_replace('/wp-content', $baseurl . '/wp-content', $code);
                $banner->html = $code;
            }
            elseif(strlen($row->image))
            {
                $banner->image_url = $row->image;
                $href = preg_match('#href="([^"]*)"#i', $code, $matches);
                if ($matches) {
                    $banner->destination = $matches[1];
                }

                if (strpos($banner->destination, 'www') == 0) {
                    $banner->destination = 'http://' . $banner->destination;
                }
            }
            else {
                $img = preg_match('#src=\"(.*(jpg|jpeg|png|gif))"#i', $code, $matches);
                
                if(!$matches) { echo "No Matches: {$row->id} / $code\n"; continue; }
                #else print_r($matches);
                #else echo "Match: {$matches[1]}\n";

                if(strstr($matches[1], 'http')) $banner->image_url = $matches[1];
                else $banner->image_url = $baseurl . $matches[1];
            }
        }

        /* Reset the flight time */
        $start = $stop = null;

        # Get the flight info for the ad, if it exists
        if ($camp->schedule) { # schedule of 0 suggests forever
            $schedule = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."adrotate_schedule`", OBJECT_K);
            foreach($schedule as $row)
            {
                if ($flight->starttime) $start = date('m/d/Y', $flight->starttime);
                if ($flight->stoptime) $stop  = date('m/d/Y', $flight->stoptime);
            }
        } else {
            continue;
        }

        if (!count($banner->zone_ids)) {
            # no ads? move on
            continue;
        }

        echo "$start / $stop / " . strtotime($stop) . ' / ' . time() . "\n";
        if (strtotime($stop) < time()) {
            echo "Skipping $ad\n";
            continue;
        }

        $advertiser->campaigns[] = array (
            'id' => $fake_campaign_id,
            'name' => $ad . ' Campaign ' . $fake_campaign_id++,
            'start_date' => $start,
            'end_date' => $stop,
            'banners' => array (
                $banner
            )
        );      
    }

    // ALERT
    if ($advertiser->campaigns) {
        if (count($advertiser->campaigns) && count($advertiser->campaigns->banners) ) {
            $advertisers[] = $advertiser;
        }
    }
}

# Output array combinging the above logic
$results = array(
    'websites' => array (
        array(
            'id' => 1,
            'website name' => $_POST['website'] ? $_POST['website'] : 'Unnamed',
            'database name' => $wpdb->dbname,
            'zones' => $zones,
            'advertisers' => $advertisers,
            'targeting_keys' => array()
        )
    )
);

# Print to file
print_r(json_encode($results));

function clean($str)
{
$regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3
    ){1,100}                        # ...one or more times
  )
  | .                                 # anything else
      /x
END;
    return preg_replace($regex, '$1', $str);
}
