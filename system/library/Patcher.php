<?php
/**
 * Implementation of the a patch mecanism for unified diff file.
 * This method allow the validation of the whole patch before modifying any file.
 * Based on Francois Mazerolle script
 * Added creating and deleting of files and root directory
 *
 * @author Francois Mazerolle <fmazerolle@idealtechnology.net>
 * @author ArtyGrand
 */

class Patcher{
	private $root;
	private $debug = false;
	private $arrError = array();
	private $patch = false;
	private $hunkCompleted = 0;

	//Patch file
	protected $patchHandle = null;
	protected $patchPath = null;
	protected $patchLine = 0;

	//Destination file
	protected $dstHandle = null;
	protected $dstPath = null;
	protected $dstLine = 0;

	//Source file
	protected $srcHandle = null;
	protected $srcPath = null;
	protected $srcLine = 0;

	public function __construct($root){
		$this->root = $root;
	}

	protected function debug($txt){
		if ($this->debug){
			echo $txt . "\n";
		}
	}

	protected function addError($txt){
		$this->arrError[] = $txt;
	}

	public function getError(){
		return $this->arrError;
	}

	public function hasError(){
		return !empty($this->arrError);
	}

	public function processPatch($patchFile, $patchLevel = '1', $debug = false){
		$this->patch = true;
		$this->debug = (bool)$debug;
		$this->hunkCompleted = 0;

		$this->openPatch($patchFile);
		$this->processFiles($patchLevel);

		return $this->hasError() ? false : $this->hunkCompleted;
	}

	public function validatePatch($patchFile, $patchLevel = '1', $debug = false){
		$this->patch = false;
		$this->debug = (bool)$debug;
		$this->hunkCompleted = 0;

		$this->debug("Validating patch ( read-only mode )");
		$this->openPatch($patchFile);
		$this->processFiles($patchLevel);

		return $this->hasError() ? false : $this->hunkCompleted;
	}

	protected function processFiles($patchLevel){
		$line = fgets($this->patchHandle);
		$this->patchLine++;
		$this->srcHandle = null;

		//Loop in all the line of the patch file.
		do {
			//loop until a file is found
			if ($this->srcHandle){
				$this->processHunks($line);

				//Preserving the unmodified lines after the last hunk.
				$this->copyOriginalLines($this->srcLine+1, false);

				$this->srcHandle = null;
				if ($this->patch){
					unlink($this->srcPath);
				}
			}

			if (0 == strncmp($line, '--- ', 4)){
				$orig_filename = $this->extractFileName($line, $patchLevel);
			} elseif (0 == strncmp($line, '+++ ', 4)){
				$filename = $this->extractFileName($line, $patchLevel);
				$this->srcPath = $this->root . $filename;

				if ($filename == 'dev/null'){
					$this->debug(sprintf("Deleting %s...", $orig_filename));
					unlink($this->root . $orig_filename);
				} else {
					if ($this->patch){
						//Make a copy for the source file.
						$this->dstPath = $this->srcPath;
						$this->srcPath .= '.orig';
						rename($this->dstPath, $this->srcPath);
					}

					//Open source file
					if ($orig_filename != 'dev/null'){
						$this->debug(sprintf("Patching %s...", $filename));
						$this->srcHandle = @fopen($this->srcPath, "r");
						$this->srcLine = 0;
						if (!$this->srcHandle){
							$this->addError(sprintf("File %s not found.", $filename));
							$this->srcHandle = null; //Make the loop skip all the file hunks.
						}
					} else {
						$this->debug(sprintf("Creating %s...", $filename));
						$this->srcHandle = @fopen('data://text/plain,', 'r');
					}

					if ($this->patch){
						$dstDir = dirname($this->dstPath);
						if (!is_dir($dstDir)){
							mkdir($dstDir, 0755, true);
						}
						//Open destination file
						$this->dstHandle = @fopen($this->dstPath, "w");
						if (!$this->dstHandle){
							$this->addError(sprintf("File %s not found.", $filename));
							$this->srcHandle = null; //Make the loop skip all the file hunks.
						}
					}
				}
			}

			$line = fgets($this->patchHandle);
			$this->patchLine++;
		} while (false !== $line);
	}


	protected function processHunks($line){
		$arrHunk = array(
			'no' => 0,
			'srcBegLine' => null,
			'dstBegLine' => null,
			'srcLastLine' => 1, //Hunk line length
			'dstLastLine' => 1, //Hunk line length
		);
		$hunkSkip = false;

		do {
			$cmd = $line[0]; //Get the first character of the line

			//Check if a Hunk just completed
			if(('O' == $cmd || 'd' == $cmd || '@' == $cmd)
				&& 0 != $arrHunk['no'] //This condition should not be executed before the first hunk is processed.
				&& !$hunkSkip //If a hunk have failed, do not consider the line as modified : it failed.
			){
				$from = $arrHunk['srcBegLine'];
				$to = $arrHunk['srcBegLine']+$arrHunk['srcLastLine']-1;
				$this->debug(sprintf("\t\tModified lines %u to %u.", $from, $to));
				$this->hunkCompleted++;
			}

			// \ No newline at end of file
			if ($cmd == 'O' || $cmd == 'd' || $cmd == '\\'){ //Only or diff mean all hunk of this dst file have been processed.
				return;

			} else if ($cmd == '@'){ //We're entering a new hunk
				$hunkSkip = false; //New hunk, new attempt.
				$h1 = sscanf(
					$line, "@@ -%d,%d +%d,%d",
					$arrHunk['srcBegLine'], $arrHunk['srcLastLine'], $arrHunk['dstBegLine'], $arrHunk['dstLastLine']
				);
				$h2 = sscanf(
					$line, "@@ -%d +%d,%d",
					$arrHunk['srcBegLine'], $arrHunk['dstBegLine'], $arrHunk['dstLastLine']
				);
				$arrHunk['no']++;
				$this->debug(sprintf("\tChecking hunk #%u", $arrHunk['no']));

				//Preserving the unmodified lines before the hunk.
				$this->copyOriginalLines($this->srcLine+1, $arrHunk['srcBegLine']-1);


			} else if ($cmd == '+' || $cmd == '-' || $cmd == ' '){
				if (!$hunkSkip){  //If the hunk previously failed, skip remaining instruction of that hunk.
					$ret = $this->processInstruction($arrHunk, $line);
					if (!$ret){
						$this->debug(sprintf("\t\tHunk FAILED."));
						$hunkSkip = true; //A line of the hunk failed to compare, skip the whole hunk: it failed.
					}
				}
			} else {
				$this->addError(sprintf("Line #%u of the patch file seems invalid.", $this->patchLine));

			}


			$line = fgets($this->patchHandle);
			$this->patchLine++;
		} while (false !== $line);
	}

	protected function processInstruction($arrHunk, $line){
		$cmd = $line[0];
		$code = substr($line, 1);

		if($cmd != '+'){ // ' ' or '-' : compare to validate

			$srcLine = fgets($this->srcHandle);
			$diff = strcmp(rtrim($code, "\n\r"), rtrim($srcLine, "\n\r")); // This rtrim fix is needed as the current code doesn't really support the \ No newline at end of file indicator.

			if ($diff !== 0){
				$this->addError(sprintf("Line #%u of the patch file could not be matched with line #%u of %s.", $this->patchLine, $this->srcLine, $this->srcPath));

				return false;
			}
			$this->srcLine++; //Only calculate the line if it passed.

		}

		if ($cmd != '-'){ // ' ' or '+' : apply.

			if ($this->patch){
				//Write/copy lines to dst.
				if (!fwrite($this->dstHandle, $code)){
					throw new Exception("error writing to new file");
				}
				$this->dstLine++;
			}

		}
		return true;
	}


	/**
	 * Open patch file.
	 *
	 * @param string $filePath Patch file path.
	 * @return Resource File handle to patch file.
	 */
	protected function openPatch($filePath){
		if (is_resource($filePath)){
			$this->debug(sprintf("Openning patch from memory"));
			$this->patchHandle = $filePath;
			$this->patchPath = null;
			$this->patchLine = 0;
		} else {
			$this->debug(sprintf("Openning %s", $filePath));
			$this->patchHandle = @fopen($filePath, "r");
			$this->patchPath = $filePath;
			$this->patchLine = 0;
			if (!$this->patchHandle){
				throw new Exception(sprintf("Could not open file %s.", $filePath));
			}
		}

		return true;
	}


	/**
	 * Extract the file path from the +++ line.
	 *
	 * @param string $line Raw line from the patch file ( '+++ ...' )
	 * @param int $patchLevel Number of folder to remove from the file name.
	 * @return string File path.
	 */
	protected function extractFileName($line, $patchLevel){
		/* Terminate string at end of source filename */
		$arr = explode("\t", $line);
		$line = trim($arr[0]);

		//Remove the first 4 chr. ( '--- ' or '+++ ' )
		$line = substr($line, 4);

		/* Skip over (patch_level) number of leading directories */
		while ($patchLevel--){
			$cut = strstr($line, '/');
			if (!$cut){
				break;
			}
			$line = ltrim($cut, '/');
		}

		return $line;
	}


	/**
	 * Copy unmodified lines from the srcHandle to the dstHandle.
	 *
	 * @param int $from
	 * @param int $to
	 */
	protected function copyOriginalLines($from, $to){
		if($to === false){
			$to = PHP_INT_MAX;
		}
		if(0 > $from || 0 >= $to){
			return false; //No line to copy
		}


		for($i=$from;$i<=$to;$i++){
			$line = fgets($this->srcHandle);
			if(!$line){
				break;
			}

			//Write/copy lines to dst.
			if ($this->patch){
				if (!fwrite($this->dstHandle, $line)){
					throw new Exception("error writing to new file");
				}
				$this->dstLine++;
			}
		}

		if($from != $i){
			$this->debug(sprintf("\t\tCopied unmodified lines %u to %u.", $from, $i-1));
		}

		$this->srcLine += $to - $from +1;

		return true;
	}
}