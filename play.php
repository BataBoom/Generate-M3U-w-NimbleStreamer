<?php
date_default_timezone_set('Europe/Lisbon');
$today = gmdate("n/j/Y g:i A");
$v = "q1";
$clock = date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'];
if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
    $ip = $_SERVER['HTTP_X_REAL_IP'];
} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    $commapos = strrpos($ip, ',');
    $ip = trim( substr($ip, $commapos ? $commapos + 1 : 0) );
}




$fetchUser = [];
$srvr = $_GET['s'] ?? false;


if($srvr){
    if($srvr == 1){
         $server = 'https://uscdn-01.s.com';
    }elseif($srvr == 2){
         $server = 'https://uscdn-02.s.com';
    }elseif($srvr == 3){
         $server = 'https://uscdn-04.s.com';
    }elseif($srvr == 4){
         $server = 'https://eucdn-01.s.com';
    }
    $set = true;
} else {
    $set = false;
    $errordata = array($_GET, $ip);
    $inp = file_get_contents('/var/www/logs/m3uAttempt.json');
    $tempArray = json_decode($inp);
    array_push($tempArray, $errordata);
    $jsonData = json_encode($tempArray);
    file_put_contents('/var/www/logs/m3uAttempt.json', $jsonData);
    die(json_encode(array('error' => 'Server not found, apply &s=1,2,3,or4 to the end of the link.')));
}
    

if ($fetchUser) {
$fetchchz = file_get_contents('chz.json'); //CH Names
$readchz = json_decode($fetchchz, true);
$chname = array_column($readchz, 'name');

/*
$token = $_GET['t'];
$id = checkToken($token);
*/

$csv = array_map('str_getcsv', file('data.csv'));
$a = array_fill(0, 24, 'Group A');
$b = array_fill(24, 13, 'Group B');
$c = array_fill(37, 15, 'Group C');
$d = array_fill(51, 5, 'Group D');
$e = array_fill(56, 8, 'Group E');
$f = array_fill(65, 14, 'Group F');
$g = array_fill(79, 10, 'Group G');
$h = array_fill(90, 12, 'Group H');
$j = array_fill(103, 9, 'Group J');
$k = array_fill(112, 9, 'Group K');
$l = array_fill(120, 10, 'Group L');

$groups = array_merge($a, $b, $c, $d, $e, $f, $g, $h, $j, $k, $l);
$groups[56] = 'Group A';
$groups[45] = 'Group B';

$fetchTVID = array_map('str_getcsv', file('cid.csv')); //EPG Setting
$chid = array_column($fetchTVID, 0);
array_unshift($chid, "EMPTY");
array_unshift($chname, "EMPTY");



$fetchchz = file_get_contents('chz.json');
$readchz = json_decode($fetchchz, true);
$chimg = array_column($readchz, 'image');
array_unshift($chimg, "EMPTY");

$today = gmdate("n/j/Y g:i:s A");
$key = "YourKey";
$str2hash = $ip . $fetchUser . $key . $today . $validminutes;
$md5raw = md5($str2hash, true);
$base64hash = base64_encode($md5raw);
$validminutes = 43200;
$urlsignature =
  'server_time=' . $today . '&hash_value=' . $base64hash .
  '&validminutes=' . $validminutes . '&id=' . $fetchUser . '&ip=' . $ip. '&checkip=true';
$base64urlsignature = base64_encode($urlsignature);

$expires = date('n/j/Y g:i A', strtotime( $today . " +30 days"));
$filename = 'ExpiresAt_' . $expires .' EST';

header("Content-Type: audio/mpegurl");
header("Content-disposition: filename=" . $filename . ".m3u");

print '#EXTM3U'.PHP_EOL;
for ($w = 1; $w < 131; ++$w){
if ($w <= 9){
print '#EXTINF:-1 tvg-id="'.$chid[$w].'" group-title="'.$groups[$w].'" tvg-logo="https://yourimage.com/uploads/'.$chimg[$w].'" tvg-name="'.$chname[$w].'" tvg-num="'.$w.'", '.$chname[$w]."\n";
print $server . '/viewsa/ch0'.$w.'q1/playlist.m3u8?wmsAuthSign='.$base64urlsignature."\n";
} else {
print '#EXTINF:-1 tvg-id="'.$chid[$w].'" group-title="'.$groups[$w].'" tvg-logo="https://yourimage.com/uploads/'.$chimg[$w].'" tvg-name="'.$chname[$w].'" tvg-num="'.$w.'", '.$chname[$w]."\n";
print $server . '/viewsa/ch'.$w.'q1/playlist.m3u8?wmsAuthSign='.$base64urlsignature."\n";
}
}
} else {
    $errordata = array($_GET, $ip);
    $inp = file_get_contents('/var/www/logs/m3uAttempt.json');
    $tempArray = json_decode($inp);
    array_push($tempArray, $errordata);
    $jsonData = json_encode($tempArray);
    file_put_contents('/var/www/logs/m3uAttempt.json', $jsonData);
    die(json_encode(array('error' => 'Not Authorized')));
   
}
