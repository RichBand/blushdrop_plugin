<?php
/**
 * Plugin Name: Blushdrop Plugin
 * Description: handle all the custom needs of blushdrop.
 * Version: 1.1
 * Author: Ricardo Bandala
 * Author URI: http://ricardobandala.com
 * License: private
 */
require_once 'blushdrop.php';
$args = array(
	'dropbox_path' => '/blushdrop',
	'prodCat_Music' => 'music',
	'prodID_Disc' => '31',
	'prodID_EditingPacakage' => '32',
	'prodID_ExtraMinute' => '79',
	'prodID_RawMaterial' => '67',
	'prodID_URL' => '34',
);
$bdp = new Blushdrop($args);


/*
*****************SANDBOX.BLUSHDROP.COM
{
  "key": "kulo8xx7kugwfzo",
  "secret": "5gqns9krb5b2v7i"
}
$appInfo = dbx\AppInfo::loadFromJsonFile(ABSPATH . "/wp-content/plugins/blushdrop_plugin/blushdrop.json");
$accessToken = "xZ1AXx94nAoAAAAAAAH2vYuaGl5d9RNlwAEJ3XacJ6JRqDfxAIZhe0ift20P7f9M";

add_option('blushdrop_settings', array(
	'dropbox_path' => '/blushdrop/clients',
	'prodCat_Music' => 'music',
	'prodID_Disc' => '31',
	'prodID_EditingPacakage' => '32',
	'prodID_ExtraMinute' => '79',
	'prodID_RawMaterial' => '67',
	'prodID_URL' => '34',
));

$oob = '[outofthebox dir="'.$path.'" mode="files"'
				.' viewrole="administrator|editor|author|contributor|subscriber|customer|guest"'
				.' downloadrole="administrator|editor|author|contributor|subscriber" upload="1" overwrite="1" rename="1"'
				.' renamefilesrole="administrator|editor|author|contributor|customer"'
				.' renamefoldersrole="administrator|editor|author|contributor|customer" move="1" delete="1"'
				.' deletefilesrole="administrator|editor|author|contributor|customer"'
				.' deletefoldersrole="administrator|editor|author|contributor|customer"'
				.' addfolder="1" addfolderrole="administrator|editor|author|contributor|customer|guest"]';

*****************BLUSHDROP.COM

{
  "key": "jknvogyzj4jwpru",
  "secret": "do6nvyzatxynkef"
}

$appInfo = dbx\AppInfo::loadFromJsonFile(ABSPATH . "/wp-content/plugins/blushdrop_plugin/blushdrop.json");
$accessToken = "xZ1AXx94nAoAAAAAAAH2vYuaGl5d9RNlwAEJ3XacJ6JRqDfxAIZhe0ift20P7f9M";


 *
 *
 * */