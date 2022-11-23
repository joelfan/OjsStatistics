<?php 

////////////////////// Step 0 - Initialize /////////////////////////////////////////////////////////////////
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
require_once(__DIR__ . "/constants.php"); 
require_once(__DIR__ . "/functions.php"); 

////////////////////// Step 1 - START /////////////////// //////////////////////////////////////////////////
$executionStartTime = microtime(true);
echoLog("Starting statistics for OJS"); 

////////////////////// Step 2 - Retrieve journal list //////////////////////////////////////////////////////
$jList = getJournalList(); 

////////////////////// Step 3 - Save submissions statistics per Journal ////////////////////////////////////
echoLog("Collecting views"); 
$statList = getStat($jList); 

////////////////////// Step 4 - Produce RetrievedData //////////////////////////////////////////////////////
echoLog("Collecting geographical provenance"); 
$statList = produceRetrievedData($jList); 

////////////////////// Step 5 - Wrap up ////////////////////////////////////////////////////////////////////
$seconds = microtime(true) - $executionStartTime;
echoLog ("This script took ".number_format($seconds,3,',','.')." seconds to execute.");
echoLog ("That's all folks"); exit(0);

?>
