<?php
/**
 * Plugin Name: Blushdrop Plugin
 * Description: handle all the custom needs of blushdropCart.
 * Version: 1.1
 * Author: Ricardo Bandala
 * Author URI: http://ricardobandala.com
 * License: private
 */
require_once 'blushdrop.php';

$args = array(
	'dropbox' => [
	    'path' => '/blushdrop/',
        'appInfo' => ['key' => 'kulo8xx7kugwfzo', 'secret' => '5gqns9krb5b2v7i'],
        'token' =>'xZ1AXx94nAoAAAAAAAH2vYuaGl5d9RNlwAEJ3XacJ6JRqDfxAIZhe0ift20P7f9M'
    ],
    'prodCat_Music' => 'music',
    'prodID_Disc' => '160',
    'prodID_EditingPacakage' => '34',
    'prodID_ExtraMinute' => '292',
    'prodID_MusicCapsule' => '350',
    'prodID_RawMaterial' => '159',
    'prodID_URL' => '32',
    'cartRules' =>[
        'onePerCart'=>[34,32],
        'noModifyQuantity'=>[292],
    ],
);
$bdp = new Blushdrop($args);