<?php
/*
*function to set the connection with the blushdrop Dropbox
*/
$dbxClient;
require_once 'dropbox-sdk-php/lib/Dropbox/autoload.php';
use \Dropbox as dbx;
function connectDropbox(){
    $appInfo = dbx\AppInfo::loadFromJsonFile(ABSPATH."/wp-content/plugins/custom-blushdrop-plugin/blushdrop.json");
    $accessToken = "xZ1AXx94nAoAAAAAAAH2vYuaGl5d9RNlwAEJ3XacJ6JRqDfxAIZhe0ift20P7f9M";
    $dbxClient = new dbx\Client($accessToken, "blushdrop");
    return $dbxClient;
}
$GLOBALS["dbxClient"] = connectDropbox();

$accountInfo = $dbxClient->getAccountInfo();
