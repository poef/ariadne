<?php
	require_once('mod_error.php');

	class pinp_csv {

		public function _init($settings = "") {
			return csv::init($settings);
		}

		public function _load($fileName = "file", $fileNameNls = "", $settings = "") {
			return csv::load($fileName, $fileNameNls, $settings);
		}

		public function _read($fileName = "file", $settings = array() ) {
			return csv::read($fileName, $settings);
		}

		public function _readFromArray($csvArray, $settings = array() ) {
			return csv::readFromArray($csvArray, $settings);
		}

		public function _writeToString($csvFeed, $settings = array() ) {
			return csv::writeToString($csvFeed, $settings);
		}

	}

	class csv {

		public static function init($settings = "") {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];

			return new csvFeed($me, $settings);
		}

		public static function load($fileName = "file", $fileNameNls = "", $settings = array()) {
			return $this->read($fileName, array_merge( array('nls' => $fileNameNls), $settings));
		}

		public static function read($fileName = "file", $settings = array()) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];

			$csv = new csvFeed($me, $settings);
			$csv->read($fileName, $settings);
			return $csv;
		}

/*
		function readFromString($csv, $settings = array()) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];

			$csv = new csvFeed($me, $settings);
			$csv->readFromString($csv, $settings);
			return $csv;
		}
*/

		public function readFromArray($csvArray, $settings = array()) {
			$context = pobject::getContext();
			$me = $context["arCurrentObject"];

			$csv = new csvFeed($me, $settings);
			$result = $csv->readFromArray($csvArray, $settings);
			if ($result && error::isError($result)) {
				return $result;
			} else {
				return $csv;
			}
		}

		public function _readFromArray($csvArray, $settings = array() ) {
			return $this->readFromArray($csvArray, $settings);
		}

		public function writeToString($csvFeed, $settings = array()) {
			return $csvFeed->writeToString($settings);
		}

		public function _writeToString($csvFeed, $settings = array()) {
			return $csvFeed->writeToString($settings);
		}

	}

	class csvFeed {
		protected $settings;
		protected $object;
		protected $readMode;
		protected $fp;

		public function __construct($object, $settings) {
			$default = Array(
				"seperator"		=> ",",
				"quotation"		=> "\"",
				"charset"		=> "utf-8",
				"keyRow"		=> null,
				"keySelection"	=> null,
				"bufferLength"	=> 4096 * 4,
				"lineEnd"		=> "\n"
			);
			if (!$settings) {
				$settings = Array();
			}
			foreach ($default as $key => $value) {
				if (!isset($settings[$key]) || $settings[$key] === "" ) {
					$settings[$key] = $value;
				}
			}
			if (!isset($settings["escape"])) {
				$settings["escape"] = $settings["quotation"];
			}
			$this->settings = $settings;
			$this->object = $object;
			$this->readMode = false;
		}


		public function load($fileName = "file", $fileNameNls = "") {
			return $this->read($fileName, array('nls' => $fileNameNls));
		}

		public function _load($fileName = "file", $fileNameNls = "") {
			return $this->load($fileName, $fileNameNls);
		}
		
		public function read($fileName = "file", $settings = "") {
			$object = $this->object;

			$files	= $object->store->get_filestore("files");
			if (!$fileName) {
				$fileName = "file";
			}
			if (!$settings['nls']) {
				$settings['nls'] = $object->reqnls;
			}
			if ($files->exists($object->id, $settings['nls'].'_'.$fileName)) {
				$fileName = $settings['nls'].'_'.$fileName;
			}
			$tempDir	= $object->store->get_config("files")."temp/";
			$tempFile	= tempnam($tempDir, "csvexport");
			$files->copy_from_store($tempFile, $object->id, $fileName);

			$this->readMode = "fp";
			$this->fp = fopen($tempFile, "r");
			$this->reset();
		}

		public function _read($fileName = "file", $settings = array()) {
			return $this->read($fileName, $settings);
		}

		public function readFromArray($csvArray, $settings = array() ) {
			if (!is_array($csvArray)) {
				$error = error::raiseError('mod_csv: readFromArray, input is not an array', 1);
				return $error;
			}
			$this->readMode = 'array';
			$this->csvArray = $csvArray;
			$this->reset();
		}

		public function _readFromArray($csvArray, $settings = array() ) {
			return $this->readFromArray($csvArray, $settings);
		}

		public function reset() {
			switch ($this->readMode) {
				case "array":
					reset($this->csvArray);
				break;
				default:
				case "fp":
					fseek($this->fp, 0);
					if (isset($this->settings['keyRow']) && $this->settings['keyRow'] !== "") { // csv lib saves defaults as ""
						$this->keys = array();
						for ($i = 0; $i <= $this->settings['keyRow']; $i++) {
							$keys = $this->next();
						}
						$this->keys = $keys;
						if (!$this->settings['keySelection']) {
							$this->settings['keySelection'] = $keys;
						}
					}
					$this->readLine = "";
				break;
			}
			$this->next(); // set pointer to first item for current()
		}


		public function next() {
			switch ($this->readMode) {
				case 'array':
					$result = current($this->csvArray);
					next($this->csvArray);
				break;
				default:
				case "fp":
					if (feof($this->fp)) {
						$result = Array();
					} else {
						$result = fgetcsv($this->fp, $this->settings['bufferLength'], $this->settings['seperator'], $this->settings['quotation']);
						if (is_array($result) && strtolower($this->settings['charset']) != "utf-8") {
							if (!function_exists("iconv")) {
								global $store;
								include_once($store->get_config("code")."modules/mod_unicode.php");
								foreach ($result as $item => $resultItem) {
									$result[$item] = unicode::convertToUTF8($this->settings["charset"], $result[$item]);
								}
							} else {
								foreach ($result as $item => $resultItem) {
									$result[$item] = iconv($this->settings["charset"], "utf-8", $result[$item]);
								}
							}
						}
					}
				break;
			}
			if ($result && $this->keys && $this->settings['keySelection']) {
				$hashResult = Array();
				foreach ($this->keys as $i => $key) {
					if (in_array($key, $this->settings['keySelection'])) {
						$hashResult[$key] = $result[$i];
					}
				}
				$result = $hashResult;
			}
			$this->readLine = $result;
			return $result;
		}


		public function current() {
			return $this->readLine;
		}

		public function call($template, $args=Array()) {
			$current = $this->current();
			if ($current) {
				$args['item'] = $current;
				$result = $this->object->call($template, $args);
			}
			return $result;
		}

		public function count() {
			$this->reset();
			$i = 0;
			while ($this->current()) { $i++; $this->next(); };
			return $i;
		}

		public function ls($template, $args='', $limit=0, $offset=0) {
		global $ARBeenHere;
			$ARBeenHere = Array();
			$this->reset();
			if ($offset) {
				while ($offset) {
					$this->next();
					$offset--;
				}
			}
			if( $limit == 0 ) { $limit = -1; }
			while($this->current() && $limit ) {
				$ARBeenHere = Array();
				$args["item"] = $this->current();
				$this->call($template, $args);
				$limit--;
				$this->next();
			} 
		}

		public function _getArray($limit=0, $offset=0) {
			return $this->getArray($limit,$offset);
		}

		public function getArray($limit=0, $offset=0) {
			$result=Array();
			$this->reset();
			if ($offset) {
				while ($offset) {
					$this->next();
					$offset--;
				}
			}
			if( $limit == 0 ) { $limit = -1; }
			while( $this->current() && $limit ) {
				$result[]=$this->current();
				$limit--;
				$this->next();
			}
			return $result;
		}

		public function writeToString($settings = array()) {
			$settings = array_merge($this->settings, $settings);
			$result = '';
			if ($settings['keyRow'] && $settings['keySelection']) {
				foreach ($settings['keySelection'] as $key) {
					$result .= $this->quoteValue($key, $settings).$settings['seperator'];
				}
				$result = substr($result, 0, -(strlen($settings['seperator']))) . $settings['lineEnd'];
			}
			$limit = $settings['limit'];
			if (!$limit) { 
				$limit = -1;
			}
			while (($values = $this->current()) && $limit) {
				$limit--;
				$result .= $this->quoteValues($values, $settings);
				$this->next();
			}
			return $result;
		}

		public function _writeToString($settings = array() ) {
			return $this->writeToString($settings);
		}

		public function quoteValue($value, $settings = array()) {
			$settings = array_merge($this->settings, $settings);
			return $settings['quotation'] . AddCSlashes($value, $settings['escape']) . $settings['quotation'];
		}

		public function quoteValues($values, $settings = array()) {
			$settings = array_merge($this->settings, $settings);
			if (!is_array($values)) {
				return error::raiseError('mod_csv: quoteValues: values argument is not an array', 2);
			}
			$result = '';
			if ($settings['keySelection']) {
				foreach($settings['keySelection'] as $key) {
					$result .= $this->quoteValue($values[$key], $settings) . $settings['seperator'];
				}
			} else {
				foreach ($values as $value) {
					$result .= $this->quoteValue($value, $settings) . $settings['seperator'];
				}
			}
			$result = substr($result, 0, -strlen($settings['seperator'])) . $settings['lineEnd'];

			return $result;
		}

		public function _reset() {
			return $this->reset();
		}

		public function _next() {
			return $this->next();
		}

		public function _count() {
			return $this->count();
		}

		public function _current() {
			return $this->current();
		}

		public function _ls($template, $args='') {
			return $this->ls($template, $args);
		}

	}

?>