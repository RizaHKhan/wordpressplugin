<?php
# Set Headers
header("Content-Description: File Transfer");
header("Content-type: application/json; charset=utf-8");
header('Content-Disposition: attachment; filename="tojson.json"');

# Call global variable for database access
require_once('../../../wp-config.php');
global $wpdb;

$advertisers = array();
$zones = array();
$zones_map = array();
$banners = array();
$campaigns = array();

# Get all zones
$qh = $wpdb->get_results("SELECT * FROM wp_adrotate_groups");
while($row = fetch_assoc($qh)) {
    $zones[] = array (
        'id' => intval($row['id']),
        'name' => $row['name'],
        'alias' => 'group_' . $row['id']
    );

    $zones_map[$row['id']] = true;
}

# Get all advertisers
$qh = $wpdb->get_results("SELECT * FROM wp_adrotate");

# Get the zone ids the ad runs in 

# 

// $import = array(
//     'websites' => array (
//         array(
//             'id' => 1,
//             'name' => $site,
//             'zones' => $zone,
//             'advertisers' => $advertisers,
//             'targeting_keys' => array()
//         )
//     )
// );

print_r($import);
