# OjsStatistics
Collection of programs to produce statistics on OJS

## Introduction
This project is a collection of programs that collect and then visualize statistics from PKP OJS (https://github.com/pkp/ojs).
It is composed of two main parts: 
1) the first part collects the data and prepares the data files. This part runs asynch, that means it is deferred periodically. We make it run, for example, once in a month.
2) the second part visualizes the data

The typical running of data collection will be:
* ONESHOT - Once to get all past data, if necessary
* PERIODICAL - Once in a month to collect the last month

What is the difference with OJStat (https://www.ojstat.eu.org/)? OJStat is a really professional statistical tool which must be inside OJS installation. OjsStatics is just a tool to collect statistics from outside OJS installation.  

**Part 1 (data collection)** is done with php programs. The aim of those PHP programs is to collect the relevant data. 
The PHP programs get the OJS data with the following API:
* https://[ojsSite]/index.php/contexts/api/v1?apiToken=[token] that produces the list of journals. where [ojsSite] is the site where OJS is running, [token] is the apikey.
* then for each journal, https://[ojsSite]/index.php/[journalname]/api/v1/stats/publications?apiToken=[token] where [ojsSite] is the site where OJS is running, [journalname] is the journal, [token] is the apikey.
* then for each journal, we get the geographical data doing a login with CURL and then with https://[ojsSite]/index.php/[journal]/management/tools/generateReport?metricType=ojs::counter&columns[]=assoc_type&columns[]=context_id&columns[]=city&columns[]=country_id&columns[]=month&filters={"assoc_type":[*reportType*],"context_id":'.$journalId.',"month":"'.$y.$m.'"}';  for *reportType* we use 256, 259, 515 and 1048585. We make indeed 4 calls per journal, to harvest geo data,  and then we sum up the numbers.
* The collected data is then stored and then packed in one javascript file, ready to be sent to the browser. 

**Part 2 (web view)** is the visualization: it consists of an index file that includes the chart library, the map library, the data produced at step 1 and the logic to display the data. 

An example view can be seen here: https://milanoup.unimi.it/ita/statistiche.html


## Installation

**Part1 (data collection)**  
There are no specific requirements, apart from php > 7.2. 

**Part 2 (web view)** 
* create a folder in htdocs, name it as you want
* (after having launched part1) copy inside this folder part2 content, that is index.html and src subfolder

## Configuration and launching part1 (data collection)
To configure,  
* edit constants.php. Many of the parameters are mandatory and refer to your specific installation
* then you need the output folder structure (out/geo and out/stat), create structure if not already done
* then you can launch the data collection simply by typing  
<pre>   *php collectOjsStat.php*  </pre>
or if you want to put in crontab, you can configure crontab with  
<pre>   *30 6 1 \* \*     su - apache -c "php /usr/share/httpd/ojsStatistics/collectOjsStat.php"*   </pre>
which is of course an example with user apache at 6:30 each first day of month.


Be careful, depending on the number of journals, the process can be quite long. It takes more than 1 hour with 50 journals. 
If you want to collect past data, you need to be even more patient, it really takes much time. But at least it must be done only once. 

