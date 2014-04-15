<?php
class _baseUrl{
	const BASE_DIR = '../';
	const DEFAULT_PROTOCOL = 'http';

	public static function dir($baseDir = NULL){
		return $baseDir === false ? '' : rtrim(strtr(realpath($baseDir ? $baseDir : __DIR__ . '/' . self::BASE_DIR), array('\\'=>'/')), '/') . '/';
	}

	public static function url($baseDir = NULL, $protocol = NULL){
		$baseUrl = '';

		$baseDir = self::dir($baseDir);

		if($baseDir){
			if($protocol === NULL){
				$protocol = self::DEFAULT_PROTOCOL;
			}

			if($protocol){
				$protocol .= ':';
			}

			$url = strtr('//' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'], array(strtr($_SERVER['SCRIPT_FILENAME'], array($baseDir=>''))=>''));

			$baseUrl = $protocol . $url;
		}

		return $baseUrl;
	}
}