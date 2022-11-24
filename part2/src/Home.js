journals.sort((a, b) => a.localeCompare(b, undefined, {sensitivity: 'base'}))
let journalsNum = journals.length

let view = ''
view += '<div style="margin:auto; text-align:center">'
view += '<p>Number of Published Journals: <b>' + journalsNum + '</b></p>'
view += '<p style="font-size:10px;">Choose the journal</p><select id="junoDs">'
view += '<option value="All">All</option>'
Object.keys(journals).map(function(key, index) {
	view += '<option value="' + journals[key] + '">' + journals[key] + '</option>'});
view += '</select><p id="totalViews"></p></div><BR>';
view += '<div><div style="padding-left:10px;">'
view += '</div><div id="chart" style="width:900px; margin:auto; text-align:center"></div>'		
view += '<div style="text-align: center;"><div style="display: inline-block; text-align: left;">'
view += '<p style="font-size:10px;">Notes:<br>'
view += '- The numbers refer to total views (galleysViews) in that time period<br>'
view += '- Some numbers in 2017 were not completely collected due to technical migration<br>'
view += '</p></div></div>'
view += '</div><div style="width:900px; margin:auto; text-align:center">'
view += '<p style="font-size:16px;">Site views - Geographical spread</p>'
view += '<p style="font-size:10px;">The numbers refer to total site views from November 2021</p><div>';
view += '<div id="mapFather"><div id="svgMap" style="text-align:center;height:200;width:400;z-index:-100;"></div></div>';
hook = document.getElementById("page_stat_container")
hook.innerHTML = view

// TOTAL DOWNLOADS
let testo = "Number of views per quarter. Data is updated " + lastMonth + "/" + lastYear;
var d = new Date();
var lastMonth = d.getMonth()
var lastYear = d.getFullYear()
barChart('ALL');

// GEOGRAPHIC MAP
let valuesPack = ojsMaps['ALL']; 
document.getElementById("mapFather").innerHTML = ""; // Before drawing, lets create the div //
const child = document.createElement("div");
child.setAttribute("id", "svgMap");
const father = document.getElementById("mapFather"); 
father.appendChild(child);
drawMap(valuesPack); 
let journ = document.getElementById("junoDs");
let chart;

journ.addEventListener("change", async function() {
	
	let value = journ.value;
	let j = journ.options[journ.selectedIndex].text;
	if (j == "All") j = "ALL"
	barChart(j);	
	
	valuesPack = ojsMaps[j];console.log(valuesPack);
	document.getElementById("mapFather").innerHTML = ""; 	// Before drawing, lets create the div //
	const child = document.createElement("div");
	child.setAttribute("id", "svgMap");
	const father = document.getElementById("mapFather"); 
	father.appendChild(child);

	drawMap(valuesPack); 
}); // journ.addEventListener
	
function drawMap(valuesPack) {
	new svgMap({
	  targetElementID: 'svgMap',
	  data: {
		data: {
		  gdp: {
			name: '# of Views',
			format: '{0} ',
			thousandSeparator: ',',
			thresholdMax: 50000,
			thresholdMin: 1000
		  },
		},
		applyData: 'gdp',
		initialZoom: 1,
		values: valuesPack
		/* {
		  AF: {gdp: 587},
		  AL: {gdp: 4583},
		  DZ: {gdp: 4293} 
		  // ...
		} 
		*/
	  }
	});
}

function barChart(j) {	
	let oV = ojsViews[j]
	let dataVisual = ["galleyViews"]
	let xTick = ["x"]
	let totViews = 0
	for (var key in oV){
		dataVisual.push(oV[key].galleyViews)
		totViews = totViews + oV[key].galleyViews
		xTick.push(key)
	}
	let tV = document.getElementById("totalViews")
	let totViewsString = totViews.toLocaleString(undefined);
	tV.innerHTML = "Total views: <b>" + totViewsString + "</b>";
	drawBarChart(dataVisual, xTick, testo); // draw on change		
}

function drawBarChart(dataVisual, xTick, txt) {
	var chart = bb.generate({
	title: {
	text: txt,
	padding: {
	 top: 10,
	 right: 10,
	 bottom: 10,
	 left: 10
	},
	position: "center"
	},
	legend: {
		show: false
	},
	bindto: "#chart",
	data: {
		x: "x",
		type: "bar",
		columns: [
			xTick,
			dataVisual
			/*["data1", 30, 200, 100, 170, 150, 250], ["data2", 130, 100, 140, 35, 110, 50] */
		]
	},
	axis: {
		x: {
		  type: "category",
		  tick: {
			rotate: 90,
			multiline: false,
			tooltip: true
		  },
		  height: 130
		}
	}
	});		
} 


/*
let oV = ojsViews['ALL']
let dataVisual = ["galleyViews"]
let xTick = ["x"]
let totViews = 0
for (var key in oV){
	dataVisual.push(oV[key].galleyViews)
	totViews = totViews + oV[key].galleyViews
	xTick.push(key)
}
let tV = document.getElementById("totalViews")
let totViewsString = totViews.toLocaleString(undefined);
tV.innerHTML = "Total views: <b>" + totViewsString + "</b>";

drawBarChart(dataVisual, xTick, testo);
*/