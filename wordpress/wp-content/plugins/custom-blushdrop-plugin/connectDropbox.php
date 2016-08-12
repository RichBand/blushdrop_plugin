<?php
/*
*function to set the connection with the blushdrop Dropbox
*/
require_once 'dropbox-php/src/Dropbox/autoload.php';
use \Dropbox as dbx;
Function connectDropbox(){
  $appInfo = dbx\AppInfo::loadFromJsonFile("blushdrop.json");
  $accessToken = "xZ1AXx94nAoAAAAAAAH2rC5MftpPcIA71f9XhmERX4py7hhkpOxDDKMVooAu6V3L";
  $dbxClient = new dbx\Client($accessToken, "blushdrop/1.0");
  //TODO: check if the following line is used or not
  //$accountInfo = $dbxClient->getAccountInfo();
}
