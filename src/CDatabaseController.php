<?php
// ===========================================================================================
//
// Class CDatabaseController
//
// To ease database usage for pagecontroller. Supports MySQLi.
//
// Author : Mikael Roos
//

// Include commons for database
require_once(TP_SQLPATH . 'config.php');


class CDatabaseController {

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//
	protected $iMysqli;


	// ------------------------------------------------------------------------------------
	//
	// Constructor
	//
	public function __construct() {

		$this->iMysqli = FALSE;
	}


	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() {
		;
	}


	// ------------------------------------------------------------------------------------
	//
	// Connect to the database, return a database object.
	//
	public function Connect() {

		$this->iMysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

		if (mysqli_connect_error()) {
   			echo "Connect to database failed: ".mysqli_connect_error()."<br>";
   			exit();
		}

		return $this->iMysqli;
	}


	// ------------------------------------------------------------------------------------
	//
	// Execute a database multi_query
	//
	public function MultiQuery($aQuery) {

		$res = $this->iMysqli->multi_query($aQuery)
			or die("Could not query database, query =<br/><pre>{$aQuery}</pre><br/>{$this->iMysqli->error}");

		return $res;
	}


	// ------------------------------------------------------------------------------------
	//
	// Retrieve and store results from multiquery in an array.
	//
	public function RetrieveAndStoreResultsFromMultiQuery(&$aResults) {

		$mysqli = $this->iMysqli;

		$i = 0;
		do {
			$aResults[$i++] = $mysqli->store_result();
		} while($mysqli->more_results() && $mysqli->next_result());

		// Check if there is a database error
        !$mysqli->errno
        	or die("<p>Failed retrieving resultsets.</p><p>Query =<br/><pre>{$query}</pre><br/>Error code: {$this->iMysqli->errno} ({$this->iMysqli->error})</p>");
	}


	// ------------------------------------------------------------------------------------
	//
	// Retrieve and ignore results from multiquery, count number of successful statements
	// Some succeed and some fail, must count to really know.
	//
	public function RetrieveAndIgnoreResultsFromMultiQuery() {

		$mysqli = $this->iMysqli;

		$statements = 0;
		do {
			$res = $mysqli->store_result();
			$statements++;
		} while($mysqli->more_results() && $mysqli->next_result());

		return $statements;
	}


	// ------------------------------------------------------------------------------------
	//
	// Load a database query from file in the directory TP_SQLPATH
	//
	public function LoadSQL($aFile) {

		$mysqli = $this->iMysqli;
		require(TP_SQLPATH . $aFile);
		return $query;
	}


	// ------------------------------------------------------------------------------------
	//
	// Execute a database query
	//
	public function Query($aQuery) {

		$res = $this->iMysqli->query($aQuery)
			or die("Could not query database, query =<br/><pre>{$aQuery}</pre><br/>{$this->iMysqli->error}");

		return $res;
	}


} // End of Of Class

?>