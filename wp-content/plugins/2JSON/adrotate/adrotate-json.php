<?php
header("Content-Description: File Transfer");
header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=\"tojson.json\"");

global $wpdb;

$dbh = mysqli_connect($host, $user, $password) or die('Cant connect to db');
mysqli_select_db($dbh, $db) or die("No db named $db");
mysqli_query($dbh, "SET NAMES utf8");
mysqli_query($dbh, "SET CHARACTER SET utf8");

$advertisers = array();
$zones       = array();
$zones_map   = array();
$banners     = array();
$campaigns   = array();

# Get all zones
$qh = mysqli_query($dbh, "SELECT * FROM {$prefix}adrotate_groups g");
while($row = mysqli_fetch_assoc($qh))
{
    $zones[] = array (
        'id' => intval($row['id']),
        'name' => $row['name'],
        'alias' => "group_" . $row['id']
    );

    $zones_map[$row['id']] = true;

}

# Get all advertisers
$qh = mysqli_query($dbh, "SELECT * FROM {$prefix}adrotate");

$fake_campaign_id = 1;

while($row = mysqli_fetch_assoc($qh))
{
    $tmp = explode('-', $row['title']);

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
        'id'   => $row['id'],
        'name' => $advertiser,
	    'users' => array (),
        'campaigns' => array ()
    );

    $banner = array (
        'id'   => $row['id'],
        'name' => $ad,
        'zone_ids' => array(),
        'destination' => null
    );

    # Get the zone ids the ad runs in
    $ch = mysqli_query($dbh, "SELECT `group`, `schedule` FROM {$prefix}adrotate_linkmeta WHERE ad = {$row['id']}");

    while($camp = mysqli_fetch_array($ch))
    {
        if ($row['id'] == '4') {
            #print_r($row);
            #print_r($camp);
        }

        if ($camp['group']) {
            $banner['zone_ids'][] = $camp['group'];
        }

        $code = clean(stripslashes(html_entity_decode($row['bannercode'], ENT_QUOTES, 'UTF-8')));
        $code = str_replace('%link%', '{BS:CLICK_URL}', $code);

        # Check the banner type
        if(strstr($code, '<table') || strstr($code, '<script') || strstr($code, '<iframe'))
        {
            $code = str_replace('/wp-content', $baseurl . '/wp-content', $code);
            $banner['html'] = $code;
        }
        elseif(strlen($row['image']))
        {
            $banner['image_url'] = $row['image'];
            $href = preg_match('#href="([^"]*)"#i', $code, $matches);
            if ($matches) {
                $banner['destination'] = $matches[1];
            }

            if (strpos($banner['destination'], 'www') == 0) {
                $banner['destination'] = 'http://' . $banner['destination'];
            }
        }
        else {
            $img = preg_match('#src=\"(.*(jpg|jpeg|png|gif))"#i', $code, $matches);

            if(!$matches) { echo "No Matches: {$row['id']} / $code\n"; continue; }
            #else print_r($matches);
            #else echo "Match: {$matches[1]}\n";

            if(strstr($matches[1], 'http')) $banner['image_url'] = $matches[1];
            else $banner['image_url'] = $baseurl . $matches[1];
        }

        /* Reset the flight time */
        $start = $stop = null;

        # Get the flight info for the ad, if it exists
        if ($camp['schedule']) { # schedule of 0 suggests forever
            $fh = mysqli_query($dbh, "SELECT starttime, stoptime FROM {$prefix}adrotate_schedule WHERE id = {$camp['schedule']}");
            while($flight = mysqli_fetch_array($fh))
            {
                if ($flight['starttime']) $start = date('m/d/Y', $flight['starttime']);
                if ($flight['stoptime']) $stop  = date('m/d/Y', $flight['stoptime']);
            }
        } else {
            continue;
        }

        if (!count($banner['zone_ids'])) {
            # no ads? move on
            continue;
        }

        echo "$start / $stop / " . strtotime($stop) . ' / ' . time() . "\n";
        if (strtotime($stop) < time()) {
            echo "Skipping $ad\n";
            continue;
        }

        $advertiser['campaigns'][] = array (
            'id' => $fake_campaign_id,
            'name' => $ad . ' Campaign ' . $fake_campaign_id++,
            'start_date' => $start,
            'end_date' => $stop,
            'banners' => array (
                $banner
            )
        );
    }

    if (count($advertiser['campaigns']) && count($advertiser['campaigns'][0]['banners']) ) {
        $advertisers[] = $advertiser;
    }
}

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

$import = array (
    'websites' => array (
        array (
            'id' => 1,
            'name' => $site,
            'zones' => $zones,
            'advertisers' => $advertisers,
            'targeting_keys' => array ()
        )
    )
);

// #print_r($zones);
// file_put_contents("$db.json", json_encode($import));

// echo '<pre>';
print_r($import);
// echo '</pre>';
echo count($import['websites'][0]['advertisers']) . " advertisers exported.\n";
