<?php

/**
 * Created by PhpStorm.
 * User: ricardobandala
 * Date: 2016-09-04
 * Time: 17:07
 */
use \Dropbox as dbx;
require_once 'dropbox-sdk-php/lib/Dropbox/autoload.php';

if (!class_exists('Blushdrop_dropbox')) {
	class Blushdrop_dropbox
	{
		protected $path = null;
		private $client = null;
		function __construct($args)
		{
			$this->path = $args['path'];
			$this->client = $this->setClient($args);
		}

		public function createFolder($path)
		{
			$metadata = null;
			$exist = $this->getFolderMetadata($path);
			if ($exist == null) {
				$metadata = $this->client->createFolder($path);
			}
			return $metadata;
		}

		private function setClient($args)
		{
			$dbxClient = null;
			$appInfo = dbx\AppInfo::loadFromJson($args['appInfo']);
			$dbxClient = new dbx\Client($args['token'], "blushdrop");
			return $dbxClient;
		}

		public function getFolderMetadata($path)
		{
			return $this->client->getMetadataWithChildren($path);
		}

		public function getVideoMinutes($path)
		{
			$videoTime = $this->getVideoTime($path);
			return $videoTime["minutes"] + ( $videoTime["hours"] * 60 );
		}
		public function getVideoTime($path)
		{
			$countTime = 0;
			$count = 0;
			$count = [
				"totalTime" => 0,
				"seconds" => 0,
				"minutes" => 0,
				"hours" => 0,
			];
			try {
				$folderMetadata = $this->getFolderMetadata($path);
				$contents = $folderMetadata["contents"];
				$countTime = 0;
				if (is_array($contents) || is_object($contents)) {
					foreach ($contents as $metadatos) {
						//$count++;
						$countTime += isset($metadatos["video_info"]["duration"]) ? $metadatos["video_info"]["duration"] : '';
					};
					$uSec = $countTime % 1000;
					$input = floor($countTime / 1000);
					$seconds = $input % 60;
					$input = floor($input / 60);
					$minutes = $input % 60;
					$input = floor($input / 60);
					$hours = $input % 60;
					$count = [
						"totalTime" => $countTime,
						"seconds" => $seconds,
						"minutes" => $minutes,
						"hours" => $hours,
					];
				};
			} catch (Exception $e) {
				//echo 'Excepción capturada: ',  $e->getMessage(), "\n";
			}
			return $count;
		}
	}
}