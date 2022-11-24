<?php 

function getJournalList() {
	
	/*****************************
		This function lists all journals. 
		It takes no argument and returns an array of journals.
		The array returned contains the journal URL and the name and id for each journal. 
	*****************************/
	
	$pageCount = 0; $perPage = 1; 
	$u = T_URL1.'journalpath'.T_URL2.'contexts'.'?isEnabled=true&count='.$perPage.'&offset='.$pageCount.'&'.T_APIK;
	$page = json_decode(webGet($u)); 
	$total = $page->itemsMax ?? 0; 
	if (defined('MAXITEMS')) $total = MAXITEMS; // debug
	$jList = [];
	$perPage = COUNT; 
	$journalCount = 0; 
	while ($pageCount < $total) { 
		$u = T_URL1.'journalpath'.T_URL2.'contexts'.'?isEnabled=true&count='.$perPage.'&offset='.$pageCount.'&'.T_APIK;
		$pageString = webGet($u);
		$page = json_decode($pageString);
		$pageCount += $perPage;
		$innerLoop = 0;
		foreach($page->items as $i) {
			$journalCount++; 
			$journalCount++; 
			$innerLoop ++;
			$journal = [];
			$journal['href'] = $i->url ?? "No URL";
			$journal['urlPath'] = $i->urlPath ?? ""; 
			$urlPath = $journal['urlPath']; 
			$journal['userDetails'] = [];
			$journal['description'] = $i->description->it_IT ?? "";
			$journal['name'] = $i->name->it_IT ?? null;
			$journal['enabled'] = ($i->enabled) ? "Yes" : "No";
			$journal['id'] = $i->id ?? 0;
			$jList[$urlPath] = (object) array('urlpath' => $urlPath, 'id' => $journal['id'], 'name' => $journal['name']);
		}
	}
	echoLog("We have ".count($jList)." Journals.");
	return($jList);
}

function getStat($journals) {						

	/*****************************
		This function collects statistics via API and document export. 
		It takes the list of journals as argument and writes the out files. 
		One file per month/year for view accounting and one file per month/year for geoMap.
		Usually it collects for the preceding month of the actual date, but you can force a total collection with config parameter STARTPOINT
	*****************************/

	$chOjs = ojsLogin(POSTINFO, COOKIE_FILE_PATH); 	// Login to OJS
	$perPage = COUNT; 
	$all = 'All';

	// date management
	$startYm = date("Ym",strtotime("-1 months"));
	$endYm = date("Ym",strtotime("-1 months"));
	if (defined('STARTPOINT'))
		$startYm = STARTPOINT;
	$stYm = $startYm;		
	
	while ($stYm <= $endYm) 
	{
		$y = substr($stYm, 0, 4);
		$m = substr($stYm, 4, 2);
		$stat = [];
		$geo = [];
		$firstDayOfMonth = $y."-".$m."-01"; 
		$lastDayOfMonth = date("Y-m-t", strtotime($y."-".$m."-01"));
		echoLog("Processing $y-$m");	
		$stat[$y.$m] = [];
		$geo[$y.$m] = [];
		$stat[$y.$m][$all] = [];
		$stat[$y.$m][$all]['abstractViews'] = 0;
		$stat[$y.$m][$all]['galleyViews'] = 0;
		$stat[$y.$m][$all]['pdfViews'] = 0;
		$stat[$y.$m][$all]['htmlViews'] = 0;
		$stat[$y.$m][$all]['otherViews'] = 0;			

		foreach ($journals as $journal => $value) {
			curl_close($chOjs); $chOjs = ojsLogin(POSTINFO, COOKIE_FILE_PATH); // Login to OJS
			$journalId = $value->id; 
			$stat[$y.$m][$journal] = [];
			$geo[$y.$m][$journal] = [];
			$stat[$y.$m][$journal]['abstractViews'] = 0;
			$stat[$y.$m][$journal]['galleyViews'] = 0;
			$stat[$y.$m][$journal]['pdfViews'] = 0;
			$stat[$y.$m][$journal]['htmlViews'] = 0;
			$stat[$y.$m][$journal]['otherViews'] = 0;
			echoLog("Collecting $m $y for $journal $journalId");
			$u = T_URL1.$journal.T_URL2.'stats/publications?count=1&offset=0&dateEnd='.$lastDayOfMonth.'&dateStart='.$firstDayOfMonth.'&'.T_APIK;
			$pageString = webGet($u);
			$page = json_decode($pageString); 
			$journalCount = 0; 
			$sList = [];
			$total = $page->itemsMax ?? 0;
			$pageCount = 0; 

			// NOW GETTING VIEW STATISTICS 
			while ($pageCount < $total) { 
				$u = T_URL1.$journal.T_URL2.'stats/publications?count='.$perPage.'&offset='.$pageCount.'&dateEnd='.$lastDayOfMonth.'&dateStart='.$firstDayOfMonth.'&'.T_APIK;
				$pageString = webGet($u);
				$page = json_decode($pageString);
				$pageCount += $perPage;
				foreach ($page->items as $item) {
					$stat[$y.$m][$all]['abstractViews'] += $item->abstractViews;
					$stat[$y.$m][$all]['galleyViews'] += $item->galleyViews;
					$stat[$y.$m][$all]['pdfViews'] += $item->pdfViews;
					$stat[$y.$m][$all]['htmlViews'] += $item->htmlViews;
					$stat[$y.$m][$all]['otherViews'] += $item->otherViews;						
					$stat[$y.$m][$journal]['abstractViews'] += $item->abstractViews;
					$stat[$y.$m][$journal]['galleyViews'] += $item->galleyViews;
					$stat[$y.$m][$journal]['pdfViews'] += $item->pdfViews;
					$stat[$y.$m][$journal]['htmlViews'] += $item->htmlViews;
					$stat[$y.$m][$journal]['otherViews'] += $item->otherViews;
				}
			}	
			
			// NOW GETTING REPORT GENERATOR STATISTICS 133,755secondi/45Mesi => 3secondi => 1,5secondi per rivista
			$geoStat = [];
			
			$u = 'https://riviste.unimi.it/index.php/'.$journal.'/stats/reports/generateReport?metricType=ojs::counter&columns[]=assoc_type&columns[]=context_id&columns[]=city&columns[]=country_id&columns[]=month&filters={"assoc_type":["256"],"context_id":'.$journalId.',"month":"'.$y.$m.'"}';
			$pageString = "\n\n--------------------------------\n".ojsReport($chOjs, $u); 
			sleep(1); 

			$u = 'https://riviste.unimi.it/index.php/'.$journal.'/stats/reports/generateReport?metricType=ojs::counter&columns[]=assoc_type&columns[]=context_id&columns[]=city&columns[]=country_id&columns[]=month&filters={"assoc_type":["259"],"context_id":'.$journalId.',"month":"'.$y.$m.'"}';
			$pageString .= "\n\n--------------------------------\n".ojsReport($chOjs, $u); 
			sleep(1); 
			
			$u = 'https://riviste.unimi.it/index.php/'.$journal.'/stats/reports/generateReport?metricType=ojs::counter&columns[]=assoc_type&columns[]=context_id&columns[]=city&columns[]=country_id&columns[]=month&filters={"assoc_type":["515"],"context_id":'.$journalId.',"month":"'.$y.$m.'"}';
			$pageString .= "\n\n--------------------------------\n".ojsReport($chOjs, $u); 
			sleep(1); 

			$u = 'https://riviste.unimi.it/index.php/'.$journal.'/stats/reports/generateReport?metricType=ojs::counter&columns[]=assoc_type&columns[]=context_id&columns[]=city&columns[]=country_id&columns[]=month&filters={"assoc_type":["1048585"],"context_id":'.$journalId.',"month":"'.$y.$m.'"}';
			$pageString .= "\n\n--------------------------------\n".ojsReport($chOjs, $u); 
			sleep(1); 
			
			$geoStat = addGeoStat($pageString);
			
			$geo[$y.$m][$journal] = $geoStat;
		} 	//Foreach
		
		file_put_contents(OUTPATHSTAT.'stat'.$y.$m.'.json', json_encode($stat));
		file_put_contents(OUTPATHGEO.'geoStat'.$y.$m.'.json', json_encode($geo));	
		$time = strtotime($stYm.'01');
		$stYm = date("Ym", strtotime("+1 month", $time));
	} // while
}

function produceRetrievedData($jList) {
	
	/*****************************
		This function transform the files produced into a Javascript program. 
		It takes the list of journals as argument and writes the out the Javascript file. 
		The Javascript files contains 2 main parts: 
			the number of downloads, calculated per journal per period (VIEWS), and calls getViewValues() function.
			the number of site views, divided geographically calculated per journal per period (MAPS), and calls getMapsValues() function.
	*****************************/

	$journalsString = "";
	$ojsViewsString = "";
	$journalsString = "const journals = [";
	$ojsViewsString = "\nlet ojsViews = []\n\n"; 							
	$ojsMapsString = "\nlet ojsMaps = []\n\n"; 							

	$journalNumber = count($jList);
	$jViews = [];

	$j = "ALL";
	$ojsViewsString .= getViewValues($j);
	$ojsMapsString .= getMapsValues($j);

	$counter = 0;
	foreach ($jList as $j => $obj) {
		$counter++;
		if ($counter != 1) {
			$journalsString .= ",";
		}
		$journalsString .= "'". $j ."'";
		$ojsViewsString .= getViewValues($j);
		$ojsMapsString .= getMapsValues($j);
	}

	$journalsString .= "]";
	file_put_contents(OUTPATHJS.'retrieveData.js', $journalsString . "\n" . $ojsViewsString . "\n" . $ojsMapsString);
}


function getViewValues($j) {
	
	/*****************************
		This function gets views data calling getOjsViews($j), and then prepares a string to be returned. 
		It takes a journal as argument and returns a string. 
	*****************************/
	
	$jViews[$j] = getOjsViews($j);
	$ojsViewsString = "ojsViews['" . $j . "'] = []\n";
	$counter = 0;
	foreach ($jViews[$j] as $period => $value) {
		$counter++;
		$ojsViewsString .= "ojsViews['" . $j . "']['" . $period . "'] = ";
		$ojsViewsString .= "{";
		$ojsViewsString .= "abstractViews:" . $value['abstractViews'] . ",";
		$ojsViewsString .= "galleyViews:" . $value['galleyViews'] . ",";
		$ojsViewsString .= "pdfViews:" . $value['pdfViews'] . ",";
		$ojsViewsString .= "htmlViews:" . $value['htmlViews'] . ",";
		$ojsViewsString .= "otherViews:" . $value['otherViews']  . "}\n";
}
	$ojsViewsString .= "\n";
	return ($ojsViewsString);
}

function getMapsValues($j) {

	/*****************************
		This function gets map (geo) data calling getOjsMaps($j), and then prepares a string to be returned. 
		It takes a journal as argument and returns a string. 
	*****************************/
	
	$jMaps = getOjsMaps($j);
	$ojsMapsString = "ojsMaps['" . $j . "'] = []\n";
	$counter = 0;
	foreach ($jMaps as $period => $value) {
		$counter++;		
		$ojsMapsString .= "ojsMaps['" . $j . "']['" . $period . "'] = ";
		$ojsMapsString .= "{";
		$innerCounter = 0; 
		foreach ($value as $state => $views) {		
			$innerCounter++; 
			if ($innerCounter <> 1) 
				$ojsMapsString .= ",";
			$ojsMapsString .= $state . ":" . $views;
		}
		$ojsMapsString .= "}\n";
	}
	$ojsMapsString .= "\n";
	return ($ojsMapsString);
}

function getOjsMaps($j) {
	$search = OUTPATHGEO .'*.json';
	$files = glob($search);
	$out = [];
	foreach ($files as $file) {
		$period = substr(basename($file), 7, 6); 
		$pageString = file_get_contents($file);
		$page = json_decode($pageString);
		foreach ($page->$period as $journalName => $stateList) {
			if (($j == $journalName) || ($j == 'ALL')) {
				foreach ($stateList as $state => $value) {
					if ($state != "") {
						$valueInt = intval($value);
						if (isset($out[$state])) {
							$out[$state]['gdp'] += $valueInt;
						}
						else {
							$out[$state] = [];
							$out[$state]['gdp'] = $valueInt;
						}
					}
				}
			}
		}
	}
	return $out;
}

function getOjsViews($journal) {
	$search = OUTPATHSTAT .'*.json';
	$files = glob($search);
	$outPerMonth = [];
	$outPerQuarter = [];
	if ($journal == 'ALL') $journal = 'All';
	foreach ($files as $file) {
		$periodMonth = substr(basename($file), 4, 6); 
		$outPerMonth[$periodMonth] = [];
		$pageString = file_get_contents($file);
		$page = json_decode($pageString);
		foreach ($page->$periodMonth as $journalName=>$j) {
			if ($journalName == $journal) {
				$rowMonth = [];
				$rowMonth["abstractViews"] = $j->abstractViews;
				$rowMonth["galleyViews"] = $j->galleyViews;
				$rowMonth["pdfViews"] = $j->pdfViews;
				$rowMonth["htmlViews"] = $j->htmlViews;
				$rowMonth["otherViews"] = $j->otherViews;
				$outPerMonth[$periodMonth] = $rowMonth;
			}
		}
	}
	
	foreach ($outPerMonth as $period => $periodData) {
		$month = substr(basename($period), 4, 2); 
		$year = substr(basename($period), 0, 4); 
		switch (intdiv($month, 4)) {
			case 0:	$q = "Q1"; break;
			case 1:	$q = "Q2"; break;
			case 2:	$q = "Q3"; break;
			case 3:	$q = "Q4"; break;
		} 
		$periodQuarter = $year.$q;
		if (($month == 1) || ($month == 4) || ($month == 7) || ($month == 10)) {
			$rowQuarter = [];
			$rowQuarter["abstractViews"] = $periodData["abstractViews"];
			$rowQuarter["galleyViews"] = $periodData["galleyViews"];
			$rowQuarter["pdfViews"] = $periodData["pdfViews"];
			$rowQuarter["htmlViews"] = $periodData["htmlViews"];
			$rowQuarter["otherViews"] = $periodData["otherViews"];
		} else {
			$rowQuarter["abstractViews"] += $periodData["abstractViews"];
			$rowQuarter["galleyViews"] += $periodData["galleyViews"];
			$rowQuarter["pdfViews"] += $periodData["pdfViews"];
			$rowQuarter["htmlViews"] += $periodData["htmlViews"];
			$rowQuarter["otherViews"] += $periodData["otherViews"];	
		}
		
		if (($month == 3) || ($month == 6) || ($month == 9) || ($month == 12)) {
			$outPerQuarter[$periodQuarter] = $rowQuarter;
		}
	} 
	//return $outPerMonth;
	return $outPerQuarter;
}	

///////////////////////// UTILITY FUNCTIONS /////////////////
function webGet($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
	if (defined('PROXY'))  
		curl_setopt($ch, CURLOPT_PROXY, PROXY);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'GET');
	curl_setopt($ch, CURLOPT_HEADER, false);
	$text = curl_exec($ch);
	$info = curl_getinfo($ch);
	$err = curl_errno($ch);
	$errmsg = curl_error($ch);
	//echo "info\n"; print_r($info); echo "text\n"; print_r($text); echo "err\n"; print_r($err); echo "errmsg\n";print_r($errmsg);
	//curl_close($ch);
	if ($info['http_code'] < 300) return($text); else return(false);
}

function echoLog ($string) {
	file_put_contents(T_LOG, date('Ymd-H:i:s')." ".$string."\n", FILE_APPEND);
	echo date('Ymd-H:i:s')." ".$string."\n";
}

function ojsLogin($postinfo, $cookie_file_path) {
	$ch = curl_init();
	if (defined('PROXY'))  
		curl_setopt($ch, CURLOPT_PROXY, PROXY);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_NOBODY, false);
	curl_setopt($ch, CURLOPT_URL, T_URL1.ACTIONURL);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
	curl_setopt($ch, CURLOPT_COOKIE, "cookiename=0");
	curl_setopt($ch, CURLOPT_USERAGENT,
		"Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postinfo);
	curl_exec($ch);
	return ($ch);	
}

function ojsReport($ch, $url) {
	curl_setopt($ch, CURLOPT_URL, $url);
	return (curl_exec($ch));
}

function addGeoStat($str) {
	$geoMonth = [];
	foreach ($lines = explode("\n",$str) as $line) {
		if (substr($line,0,1) == ',') {
			$items = explode(",",$line); // => ,Tipo,Testata,Città,Nazione,Mese,Conteggio either 256 and 259
			$state = $items[4] ?? "None";
			$city = $items[3] ?? "None";
			$counterField = count($items);
			$value = $items[$counterField - 1] ?? null;
			$city = $items[$counterField - 4] ?? "";
			$state = $items[$counterField - 3] ?? "";
			if (is_numeric($value)) {
				if (isset($geoMonth[$state])) {
					$geoMonth[$state] = $geoMonth[$state] + $value;
				} else {
					$geoMonth[$state] = $value;				
				}
			}
		}
	}
	return($geoMonth);
}
?>