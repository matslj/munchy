<?php
define("NL", "\r\n"); // Suited for win-environment
// ===========================================================================================
//
// Class CLogger
//
// Simple logger.
// 
// Author: Mats Ljungquist
//
class CLogger {

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//
        private $fh = null;
        private $newLine = "";
        private $logger = "";


	// ------------------------------------------------------------------------------------
	//
	// Constructor
        //
	private function __construct($aLogger, $aNewLine, $aFilename) {
            $this -> logger = $aLogger;
            $this -> newLine = $aNewLine;
            $filename = TP_LOGPATH . $aFilename;
            $this -> fh = null;

            $log = "*********** " . date("Y/m/d H:i:s"). substr((string)microtime(), 1, 6) . " ***********" . $aNewLine;
            
            $mode = "";
            if (file_exists($filename)) {
                $mode = "ab";
                $log = $this -> newLine . $this -> newLine . $log;
            } else {
                $mode = "wb";
            }
            $this -> fh = fopen($filename, $mode) or die("can't open file");
            fwrite($this -> fh, $log);
	}


	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() {
                fclose($this -> fh);
	}
        
        // ------------------------------------------------------------------------------------
	//
	// Factory method pattern
        // This class should at its minimum be used as follows:
        // $log = CLogger::getInstance(__FILE__);
        // from the file needing the logger.
        // 
	// @param aLogger For this class to be meningful the caller should use __FILE__ as a parameter
        public static function getInstance($aLogger, $aNewLine = NL, $aFilename = "sitelog.txt") {
            return new CLogger($aLogger, $aNewLine, $aFilename);
        }

        public function debug($aMessage) {
            $log = $this -> newLine . basename($this -> logger) . ": " . $aMessage;
            fwrite($this -> fh, $log);
        }
	

} // End of Of Class

?>