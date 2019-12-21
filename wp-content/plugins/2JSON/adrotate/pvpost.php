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
$network_id = 642;
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
mysql_query("SET NAMES utf8");
mysql_query("SET CHARACTER SET utf8");

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
    $advertiser = stripslashes(html_entity_decode($advertiser, ENT_QUOTES, 'UTF-8'));
    $ad         = $advertiser;
    $advertiser = str_ireplace('rotator', '', $advertiser);

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
    

    $code = stripslashes(html_entity_decode($row['bannercode'], ENT_QUOTES, 'UTF-8'));
    $code = str_replace('%link%', '{BS:CLICK_URL}', $code);

    # Check the banner type
    if(strstr($code, '<table') || strstr($code, '<script') || strstr($code, '<iframe'))
    {
        $code = str_replace('/wp-content', 'http://pvpost.com/wp-content', $code);
        $banner['html'] = $code;
    }
    else {
        $img = preg_match('#src=\"(.*(jpg|jpeg|png|gif))"#i', $code, $matches);

        if(!$matches) { echo "No Matches: {$row['id']} / $code\n"; continue; }
        #else print_r($matches);
        #else echo "Match: {$matches[1]}\n";

        if(strstr($matches[1], 'http')) $banner['image_url'] = $matches[1];
        else $banner['image_url'] = 'http://pvpost.com' . $matches[1];
    }

    $advertiser['banner'] = $banner;
    $advertisers[] = $advertiser;
    
    $banners[] = $banner;
}

#print_r($banners);
#print_r($advertisers);
#exit;

$bs = new Broadstreet($api_key);
$count = 0;
foreach($advertisers as $adv)
{
    try {
        $new_adv = $bs->createAdvertiser($network_id, $adv['name']);
    } catch(Exception $ex) {
        echo "Error importing advertiser: {$adv['name']}\n";
        echo $ex->__toString();
        echo "\n";
        continue;
    }


    if(isset($adv['banner']['image_url']) || isset($adv['banner']['html']))
    {
        $params = array();
        $params['name'] = $adv['banner']['name'];
        $params['destination'] = $adv['banner']['destination'];

        if(isset($adv['banner']['image_url']))
        {
            $file = "/tmp/" . basename($adv['banner']['image_url']);
            #$params['active'] = base64_encode(file_get_contents($file));
            $params['active_url'] = $adv['banner']['image_url'];

            try {
                $bs->createAdvertisement($network_id, $new_adv->id, $adv['banner']['name'], 'static', $params);
            } catch(Exception $ex) {
                echo "Error importing ad: " . $adv['banner']['name'] . "\n";
                echo $ex->__toString();
                echo "\n";
            }
        }

        if(isset($adv['banner']['html']))
        {
            $params['html'] = clean($adv['banner']['html']);

            try {
                $bs->createAdvertisement($network_id, $new_adv->id, $adv['banner']['name'], 'html', $params);
            } catch(Exception $ex) {
                echo "Error importing ad: " . $adv['banner']['name'] . "\n";
                echo $ex->__toString();
                echo "\n";
            }
        }

        echo "Imported " . ++$count . " of " . count($advertisers) . "\n";
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
