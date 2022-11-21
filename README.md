# OjsStatistics
Collection of program to produce statistics on OJS

This project is a sìcollection of programs that collect and then visualize statistics from PKP OJS (https://github.com/pkp/ojs).
It is composed of two main part: 
1) the first part collect the data and prepares the data files
2) the second part visualizes the data


**Part 1** is done with php programs. The aim of those PHP programs is to collect the relevant data. 
The PHP programs get the OJS data with the following API:
* https://[ojsSite]/index.php/contexts/api/v1?apiToken=[token] that produces the list of journals. [token] is the apikey
* then for each journal, https://[ojsSite]/index.php/[journalname]/api/v1/stats/publications?apiToken=[token] where [ojsSite] is the site where OJS is running, [journalname] is the journal, [token] is the apikey.
* then for each journal, we get the geographical data doing a login with CURL and then with https://[ojsSite]/index.php/[journal]/management/tools/generateReport?metricType=ojs::counter&columns[]=assoc_type&columns[]=context_id&columns[]=city&columns[]=country_id&columns[]=month&filters={"assoc_type":[*reportType*],"context_id":'.$journalId.',"month":"'.$y.$m.'"}';  for *reportType* we use 256, 259, 515 and 1048585. We make indeed 4 calls per journal, to harvest geo data,  and then we sum up the numbers.
The collected data is then stored and then packed in one javascript file, ready to be sent to the browser. 

**Part 2** is the visualization: it consists of an index file that include the chart library, the map library, the data produced at step 1 and the logic to display the data. 

An example view can be seen here: https://milanoup.unimi.it/ita/statistiche.html
