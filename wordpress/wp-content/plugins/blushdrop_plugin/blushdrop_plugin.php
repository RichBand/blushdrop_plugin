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
	    'path' => '/blushdrop/clients/',
        'appInfo' => ['key' => 'jknvogyzj4jwpru', 'secret' => 'do6nvyzatxynkef'],
        'token' =>'aJhr5imPEkAAAAAAAAAA6ZHoBxaPNjU8nJF_fc611dRLa_EycP18skNkPmPkR4dc'
    ],
    'prodCat_Music' => 'Music',
    'prodID_Disc' => '160',
    'prodID_EditingPacakage' => '34',
    'prodID_ExtraMinute' => '292',
    'prodID_RawMaterial' => '159',
    'prodID_URL' => '32',
    'cartRules' =>[
        'onePerCart'=>[34,32],
        'noModifyQuantity'=>[292],
    ],
);
$bdp = new Blushdrop($args);