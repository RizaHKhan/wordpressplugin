<?php
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
$zones = array();
$advertisers = array();

# READING ALL TABLES:
$adrotate = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."adrotate`", OBJECT_K);
$groups = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."adrotate_groups`", OBJECT_K);
$linkmeta = $wpdb->get_results("SELECT `group`, `schedule` FROM `".$wpdb->prefix."adrotate_linkmeta`", OBJECT_K);
$schedule = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."adrotate_schedule`", OBJECT_K);

# Logic for $result array

# Zones array logic
foreach($groups as $row) 
{
    $zones[] = array(
        'id' => intval($row->id),
        'name' => $row->name,
        'alias' => "group_" . $row->id
    );
}

# group table logic
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

    foreach($linkmeta as $row) {
        
        if($row->group) {
            $banner['zone_ids'][] = $row->group;
        }

        $code = clean(stripslashes(html_entity_decode($row->bannercode, ENT_QUOTES, 'UTF-8')));
        $code = str_replace('%link%', '{BS:CLICK_URL}', $code);

        
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
print_r($results);

