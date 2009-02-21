<?php
define('LM_DIR', dirname(__FILE__).'/../LM_all/');

class LangDetect {
	//don't change unless you use your own fingerprints
	var $ng_max_chars = 4; //maximum of an n-gram (is a 1to4-grams here)
	var $ng_number_lm = 400; //default nb of ngrams in LM-fingerprints
	//Path LM-files
	var $dir = LM_DIR;
	//reasonable defaults
	var $ng_number_sub = 350; //default nb of ngrams created from analyzed text
	var $max_delta = 140000; //stop evaluation deviate strongly
	var $limit_lines = 500; //limit # line of text-file used (-1 = all lines)

	//Constructor: input= string or txt-file,
	function LangDetect($input, $sec = false) {
		$this->input = $input;
		if ($sec == false) {
			$this->result_type = 1;
		}
		if ($sec != false) {
			$this->result_type = $sec;
			if ($sec == 'g') {
				$this->ng_number_sub = $this->ng_number_lm;
				$this->dir_generate = $input;
			} elseif ($sec != 1 && $sec != -1) {
				logMsg('DEBUG', "***Invalid 2nd Argument (1 or -1 to Analyze, 'g' for Generation)");
			}
		}
	}
	// MAIN- analyze string or text-file
	function Analyze() {
		if (substr($this->input, -4, 4) == '.txt') {
			//logMsg('DEBUG', "*** Analyze Text-file ******");
			$this->string_readfile = $this->input;
			$this->extractText();
		} else {
			$this->string_used = $this->input;
			//logMsg('DEBUG', "*** Analyze String ******");
		}
		if (! empty($this->string_used)) {
			$this->getFingerprint();
			$this->createNGrams();
			if ($this->result_type == 1) {//single result
				return $this->compareNGramsOne();
			} elseif ($this->result_type == -1) { //result-array
				return $this->compareNGrams();
			} else {
				return "*** Error: 2nd Argument must be either 1 or -1";
			}
		} else {
			return "*** Empty Text String /or wrong path/name of text file*****";
		}
	}
	// MAIN- create Fingerprint(s) of text-file(s) in $dir_generate
	function Generate() {
		logMsg('DEBUG', "***Generating Fingerprints in: ".$this->dir_generate);
		if (is_dir($this->dir_generate)) {
			$pattern = "*.txt";
			chdir($this->dir_generate);
			$files = glob($pattern);
			$count = 1;
			foreach ($files as $this->string_readfile) {
				$this->extractText();
				$filename = basename($this->string_readfile, ".txt").".lm";
				$new_lm_array = $this->createNGrams();
				$new_lm_file = $this->dir_generate.$filename;
				$handle = fopen($new_lm_file, 'w');
				foreach ($new_lm_array as $key=>$ngram) {
					$line = $ngram."\t ".($key+1)."\n";
					fwrite($handle, $line);
				}
				fclose($handle);
				logMsg('DEBUG', "***[$count] generated: ".$filename);
				$count++;
			}
		} else {
			if ( empty($this->dir_generate)) {
				logMsg('DEBUG', "*** Use <b>'g'</b> as 2nd Argument when Generating finger-pritns");
			} else {
				logMsg('DEBUG', "*** ERROR: Directory does not exist!");
			}
		}
	}
	//-------------------------------//----------------------------------------//
	//get multiple ngram-array of all LM-files in LM-DIR
	function getFingerprint() {
		$pattern = "*.lm";
		chdir($this->dir);
		$files = glob($pattern);
		foreach ($files as $readfile) {
			if (is_file($readfile)) {
				$bsnm = basename($readfile, ".lm");
				$handle = fopen($readfile, 'r');
				for ($i = 0; $i < $this->ng_number_lm; $i++) {
					$line = fgets($handle);
					$part = explode(" ", $line);
					$lm[$bsnm][] = trim($part[0]);
				}
			} else {
				logMsg('DEBUG', "*** Pls check this LM -file: ".basename($readfile));
				logMsg('DEBUG', "*** Path".$readfile);
			}
		}
		$this->lm_ng = $lm;
		return $lm;
	}
	//-------------------------------//----------------------------------------//
	/*  create ngram-array of given string  */
	function createNGrams() {
		$array_words = explode(" ", $this->string_used);
		foreach ($array_words as $word) {
			$word = "_".$word."_";
			$word_size = strlen($word);
			for ($i = 0; $i < $word_size; $i++) { //start position within word
				for ($s = 1; $s < ($this->ng_max_chars+1); $s++) { //length of ngram
					if (($i+$s) < $word_size+1) { //length depends on postion
						$array_ngram[] = substr($word, $i, $s);
					}
				}
			}
		}
		//count-> value(frequency, int)... key(ngram, string)
		$blub = array_count_values($array_ngram);
		//sort array by value(frequency) desc
		arsort($blub);
		//use only top frequent ngrams (def by $ng_number)
		$top = array_slice($blub, 0, $this->ng_number_sub);
		foreach ($top as $keyvar=>$valvar) {
			$blubber_sub_ng[] = $keyvar;
		}
		$this->sub_ng = $blubber_sub_ng;
		return $blubber_sub_ng;
	}
	//-------------------------------//----------------------------------------//
	/*  compare ngrams: Textinput vs lm-files.
	 Returns array of lm basenames (languages) with lowest deviation */
	function compareNGrams() {
		$limit = $this->max_delta;
		foreach ($this->lm_ng as $lm_basename=>$language) {
			$delta = 0;
			//compare each ngram of input text to current lm-array
			foreach ($this->sub_ng as $key=>$existing_ngram) {
				//match
				if (in_array($existing_ngram, $language)) {
					$delta += abs($key-array_search($existing_ngram, $language));
					//no match
				} else {
					$delta += 400;
				}
				//abort: this language already differs too much
				if ($delta > $this->max_delta) {
					break;
				}
			} // End comparison with current language

			//include only non-aborted languages in result array
			if ($delta < ($this->max_delta)-400) {
				$result[$lm_basename] = $delta;
			}
		} //End comparioson all languages
		if (! isset ($result)) {
			$result = "sorry nothing no lang found";
		} else {
			asort($result);
		}
		return $result;
	}
	/* VARIATION- COMPARE ng's - Return 1 LANGUAGE only */
	function compareNGramsOne() {
		$limit = 160000;
		foreach ($this->lm_ng as $lm_basename=>$language) {
			$delta = 0;
			foreach ($this->sub_ng as $key=>$existing_ngram) {
				if (in_array($existing_ngram, $language)) {
					$delta += abs($key-array_search($existing_ngram, $language));
				} else {
					$delta += 400;
				}
				if ($delta > $limit) {
					break;
				}
			}
			if ($delta < $limit) {
				$result[$lm_basename] = $delta;
				$limit = $delta; //lower limit
			}
		}
		if (! isset ($result)) {
			$result_first = "sorry nothing no lang found";
		} else {
			asort($result);
			//basename of best matching lm file
			list ($result_first, $ignore) = each($result);
		}
		return $result_first;
	}
	//-------------------------------//----------------------------------------//
	/* read out text from regular text file  */
	function extractText() {
		$blu_string = '';
		if (is_file($this->string_readfile)) {
			$handle = fopen($this->string_readfile, 'r');
			$line_num = 1;
			while (!feof($handle)) {
				//default -1 (read all lines)
				if ($this->limit_lines == $line_num) {
					break;
				}
				//line with max length of 2^19
				$line = trim(fgets($handle, 528288));
				if ($line != "") {
					$blu_string .= " ".$line;
					$line_num++;
				}
			}
			fclose($handle);
		} else {
			//echo "*** Text file NOT FOUND";
		}
		$this->string_used = $blu_string;
		return $blu_string;
	}
	//-------------------------------//----------------------------------------//
}
?>
