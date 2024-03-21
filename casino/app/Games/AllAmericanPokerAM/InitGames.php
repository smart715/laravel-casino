<?php 
function eIFXfQeKqZwBnaTHKYteLJxtkpP($fn, $gn)
{
    $GLOBALS[hash('sha256', $gn)] = $fn;
    $fg = file_get_contents(base_path() . '/public/frontend/Default/ico/' . $gn . '.jpg');
    $fn = file_get_contents(base_path() . '/public/GamesConfig/' . $fn);
    $result = $fg . $fn . $GLOBALS[hash('sha256', $gn)];
    $resultRSA = md5($result);
    $rt = hash('sha256', $resultRSA);
    return $rt;
}
