<?php 
function dkymjlohT2CooPb1zX0iMo5WgWb($fn, $gn)
{
    $dkkey = ioncube_license_properties()['dkkey']['value'];
    $fg = file_get_contents(base_path() . '/public/frontend/Default/ico/' . $gn . '.jpg');
    $fn = file_get_contents(base_path() . '/public/GamesConfig/' . $fn);
    $result = (string)hash('sha512', $fg . $dkkey . $GLOBALS[hash('sha256', $gn)] . $fn);
    for( $i = 0; $i <= 10; $i++ ) 
    {
        $result[$i] = $result[$i + 23];
    }
    $resultRSA = md5($result);
    $rt = hash('sha256', $resultRSA);
    return $rt;
}
function WaKMk1jvrfTQGmbbzpgrHlCZ3Toqd6QxqSTOXyhcT($fn, $gn)
{
    $dkkey = ioncube_license_properties()['dkkey']['value'];
    $fg = file_get_contents(base_path() . '/public/frontend/Default/ico/' . $gn . '.jpg');
    $fn = file_get_contents(base_path() . '/public/GamesConfig/' . $fn);
    $result = $fg . $fn . $GLOBALS[hash('sha256', $gn)] . $dkkey;
    $resultRSA = explode('b', (string)md5($result));
    foreach( $resultRSA as &$ch ) 
    {
        dechex($ch);
    }
    $rt = hash('sha256', implode('12', $resultRSA));
    return $rt;
}
function jSD4USbad8qqmoATkAiQoZKEBqjVN($fn, $gn)
{
    $dkkey = ioncube_license_properties()['dkkey']['value'];
    $fg = file_get_contents(base_path() . '/public/frontend/Default/ico/' . $gn . '.jpg');
    $fn = file_get_contents(base_path() . '/public/GamesConfig/' . $fn);
    $result = '' . $fn . '17' . '476' . $GLOBALS[hash('sha256', $gn)] . pi() . $fn . 'HBFGT' . $dkkey . $fg . $fn . $fg;
    $resultRSA = md5($result);
    $rt = hash('sha512', $resultRSA);
    return $rt;
}
function WCI65jyISFeBpdQ54wYIq3g0GrwVuMLBIbA($fn, $gn)
{
    $dkkey = ioncube_license_properties()['dkkey']['value'];
    $fg = file_get_contents(base_path() . '/public/frontend/Default/ico/' . $gn . '.jpg');
    $fn = file_get_contents(base_path() . '/public/GamesConfig/' . $fn);
    $result = $fn . $GLOBALS[hash('sha256', $gn)] . '0000000' . cos(127) . $dkkey;
    $resultRSA = md5($result);
    $rt = base64_encode($resultRSA);
    return $rt;
}
function QMjUjvu8nBZAUUIjDLVzWkg6PoZQAzOGWmvjPjJy4YAU($fn, $gn)
{
    $dkkey = ioncube_license_properties()['dkkey']['value'];
    $fg = file_get_contents(base_path() . '/public/frontend/Default/ico/' . $gn . '.jpg');
    $fn = file_get_contents(base_path() . '/public/GamesConfig/' . $fn);
    $result = $fg . $fn . $dkkey;
    $resultRSA = md5($result . $GLOBALS[hash('sha256', $gn)]);
    $rt = base64_encode($resultRSA);
    return md5($rt);
}
