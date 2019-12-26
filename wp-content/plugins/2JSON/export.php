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

# Get all advertisers from adrotate root table
$adrotate = $wpdb->get_results("SELECT * FROM wp_adrotate", OBJECT_K);

foreach($adrotate as $row)
{
    $advertisers[] = $row;
}

# Group table
$groups = $wpdb->get_results("SELECT * FROM wp_adrotate_groups", OBJECT_K);

foreach($groups as $row) 
{
    $zones[] = $row;
}

# Linkmeta table CURRENTLY UNUSED
$linkmeta = $wpdb->get_results("SELECT * FROM wp_adrotate_linkmeta");

# Schedule table CURRENTLY UNUSED
$schedule = $wpdb->get_results("SELECT * FROM wp_adrotate_schedule");

# Currently combining the $groups table per the original script.

$results = array(
    'websites' => array (
        array(
            'id' => 1,
            'database name' => $wpdb->dbname,
            'zones' => $zones,
            'advertisers' => $advertisers,
            'targeting_keys' => array()
        )
    )
);

# Print to file
print_r(json_encode($results));
