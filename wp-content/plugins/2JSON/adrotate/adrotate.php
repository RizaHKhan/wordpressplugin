<?php

require 'Broadstreet.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

# Config
$site = 'Genesee Sun Import Test';
$host = '127.0.0.1';
$db   = 'pvpost';
$user = 'root';
$password = 'root';
$network_id = 637;
$api_key = 'ace7c595a5741dc10481ba153b1b824aa3ece1bedbb40a6405e94e0103fc48b1';
$prefix = "wp_pvpost_";


$dbh = mysql_connect($host, $user, $password) or die('Cant connect to db');
mysql_select_db($db, $dbh) or die("No db named $db");

$advertisers = array();
$zones       = array();
$banners     = array();
$campaigns   = array();

# Get all advertisers
$qh = mysql_query("SELECT * FROM {$prefix}adrotate");

while($row = mysql_fetch_assoc($qh))
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

    $advertiser = array (
        'id'   => $row['id'],
        'name' => $advertiser
    );

    $banner = array (
        'id'   => $row['id'],
        'name' => $ad,
        'destination' => $row['link'],
        'zone_ids' => array()
    );
    
    # Check the banner type
    if($row['imagetype'] == '')
        $banner['html'] = $row['bannercode'];
    else
        $banner['image_url'] = $row['image'];
    
    $advertiser['banner'] = $banner;
    $advertisers[] = $advertiser;
    
    $banners[] = $banner;
}

print_r($banners);
print_r($advertisers);
exit;

$bs = new Broadstreet($api_key);
$count = 0;
foreach($advertisers as $adv)
{
    try {
        $new_adv = $bs->createAdvertiser($network_id, $adv['name']);
    } catch(Exception $ex) {
        echo "Error importing {$adv['name']}\n";
        continue;
    }

    $file = "/tmp/" . basename($adv['banner']['image_url']);
    copy($adv['banner']['image_url'], $file);
    
    if(isset($adv['banner']['image_url']))
    {
        $params = array();
        $params['name'] = $adv['banner']['name'];
        $params['active_url'] = $adv['banner']['image_url'];
        #$params['active'] = base64_encode(file_get_contents($file));
        $params['destination'] = $adv['banner']['destination'];

        try {
            $bs->createAdvertisement($network_id, $new_adv->id, $adv['banner']['name'], 'static', $params);
        } catch(Exception $ex) {
            echo "Error importing " . $adv['banner']['name'] . "\n";
        }
        echo "Imported " . ++$count . " of " . count($advertisers) . "\n";
    }
}

exit;
# Get all zones
$qh = mysql_query("SELECT * FROM jos_ad_agency_zone");

while($row = mysql_fetch_assoc($qh))
{
    $zones[$row['zoneid']] = array (
        'id' => $row['zoneid'],
        'name' => $row['z_title'],
        'count'  => $row['banners']
    );
}

# Roll through each advertiser, get campaigns
for($i = 0; $i < count($advertisers); $i++)
{
    $advertisers[$i]['campaigns'] = array();
    $advertiser = $advertisers[$i];
    $sql = "SELECT c.campaign_id, c.banner_id, c.zone, i.name, i.start_date, i.validity
        FROM jos_ad_agency_campaign_banner c 
        JOIN jos_ad_agency_campaign i ON i.id = c.campaign_id
        WHERE i.approved = 'Y' AND status AND i.aid = {$advertiser['id']}";

    $qh = mysql_query ($sql);

    # Roll through each campaign
    while($row = mysql_fetch_assoc($qh))
    {
        $campaign = array (
            'id' => $row['campaign_id'],
            'name' => $row['name'],
            'start_date' => date('d/m/Y', strtotime($row['start_date'])),
            'end_date' => date('d/m/Y', strtotime($row['validity'])),
            'banners' => array()
        );

        $campaign_id = $row['campaign_id'];

        # Get all banners for the campaign
        $qh2 = mysql_query ("SELECT b.*, group_concat(c.zone) as 'zones'
            FROM jos_ad_agency_campaign_banner c
            JOIN jos_ad_agency_banners b on b.id = c.banner_id
            WHERE c.campaign_id = $campaign_id AND b.advertiser_id = {$advertiser['id']} GROUP BY c.campaign_id");

        # Add each banner
        while($row2 = mysql_fetch_assoc($qh2))
        {
            $banner = array (
                'id'   => $row2['id'], 
                'name' => $row2['title'],
                'destination' => $row2['target_url'],
                'zone_ids' => array_unique(explode(',', $row2['zones']))
            );

            # Check the banner type
            if($row2['media_type'] == 'Advanced')
                $banner['html'] = $row2['ad_code'];
            else
                $banner['image_url'] = sprintf($image_template, $row2['advertiser_id'], $row2['image_url']);

            $banners[] = $banner;
        }

        $campaign['banners'] = $banners;
        $advertisers[$i]['campaigns'][] = $campaign;

        $banners = array();
    }
}

$import = array (
    'websites' => array (
        array (
            'name' => $site,
            'zones' => $zones,
            'advertisers' => $advertisers
        )
    )
);

print_r($import);
file_put_contents('out.json', json_encode($import));
