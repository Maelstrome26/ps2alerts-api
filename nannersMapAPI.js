<pseudo script>
	url.zone = 2; //8 = Esamir, 6= Amerish, 2=Indar, 4= Hossin
	mapURL = "http://census.daybreakgames.com/get/ps2:v2/zone/?zone_id="&url.zone&"&c:join=map_region^list:1^inject_at:regions^hide:zone_id%28map_hex^list:1^inject_at:hex^hide:zone_id%27map_region_id%29&c:tree=start:regions^field:facility_type^list:1&c:lang=en&c:limit=10";
	linkURL = "http://census.daybreakgames.com/get/ps2:v2/facility_link/?zone_id="&url.zone&"&c:limit=200";
	structCont = structnew();
	structCont["8"] = "Esamir";
	structCont["6"] = "Amerish";
	structCont["4"] = "Hossin";
	structCont["2"] = "Indar";
	zoneData = get(mapURL);
	linkData = get(linkURL);
</pseudo script>
<javascript>
	var zone = |value of zoneData|;
	var linkRaw = |value of linkData|;
	var markers = {}; //You can put static markers not available from the API here to start off with.
	var missingMarkerLocations = { //I found a base missing a long time ago.  Not sure if it is still missing from the API, but you can just stack any missing ones in here.
		"4132" : {
			lat:-169.71875,
			lng:102.90625
		}
	}
	var circles = {}; //This is for defining those those no deploy circles
	function getHex(hx,hy) { //This translates the weird x/y coord the API gives you to define a part of a territory into a 6 coordinate hex as a 2 diminsional array
		var p = 10000;
		var r = 3.6075;
		var rr = Math.round((Math.cos(2*Math.PI*30/360)*r)*p)/p;
		var mapCenter = [127.9932,-124.4062];
		var x = mapCenter[0];
		var y = mapCenter[1];
		var i = 0;
		var lng = 0;
		var lat = 0;
		if (hx > 0){for(i=hx;i>0;i--){x+=rr*2;}}
		if (hx < 0){for(i=hx;i<0;i++){x-=rr*2;}}
		if (hy < 0){for(i=hy;i<0;i++){
			x = Math.round((x + (2*rr) * Math.cos(2 * Math.PI * (2/3)))*p)/p;
			y = Math.round((y + (2*rr) * Math.sin(2 * Math.PI * (2/3)))*p)/p;
		}}
		if (hy > 0){for (i=hy;i>0;i--){
			x = Math.round((x + (2*rr) * Math.cos(2 * Math.PI * (1/6)))*p)/p;
			y = Math.round((y + (2*rr) * Math.sin(2 * Math.PI * (1/6)))*p)/p;
		}}
		var arrHex = [[1,1]];
		for (i = 0; i < 6; i++) {
			lng = Math.round((x + r * Math.cos(2 * Math.PI * (i / 6+0.25)))*p)/p;
			lat = Math.round((y + r * Math.sin(2 * Math.PI * (i / 6+0.25)))*p)/p;
			arrHex[i]=[lng,lat];
		}
		return arrHex; //two dimensional array [[#,#],[#,#]]
	}
	function fComp(a,b) { //A little helper to let me know when coords are the same within an exceptable tollerance.
		return (a == b || Math.round((a+0.0001)*10000)/10000 == b || Math.round((a-0.0001)*10000)/10000 == b);
	}
	function processHexes(hexes,id) {  //This is the magic that takes all the hexes for a territory, eliminates the duplicate coords, and creates a single path definition for territory polygon.
		console.log(id,hexes);
		var arrHex = [];
		var intPoint = 0;
		var intCHex = 0;
		var intCPoint = 0;
		var intMatch = 0;
		var arrMatches = [[0,0]];
		var arrStart = null;
		var arrStop = null;
		var intJoins = 0;
		var intSingles = 0;
		var matchTree = {};
		var objOut = {type:"Feature","id":id,geometry:{"type":"Polygon","coordinates":[[[1,1]]]}};
		for (var intHex = 0; intHex<hexes.length; intHex++) {
			arrHex[intHex] = getHex(hexes[intHex].x,hexes[intHex].y); //three dimensional array [[[#,#],[#,#]]]
		}
		//console.log(JSON.stringify(arrHex));
		//debugHexes = arrHex;
		if (arrHex.length == 1) {
			objOut.geometry.coordinates[0] = arrHex;
			return objOut;
		}
		for (intHex = 0; intHex < arrHex.length; intHex++) {
			for (intPoint = 0; intPoint < arrHex[intHex].length; intPoint++) {
				//console.log('Checking Hex: '+intHex+' and Point: '+intPoint);
				intMatch = 0;
				arrMatches = [[0,0]];
				if (arrHex[intHex][intPoint][0] != 0) {
					if (intHex<arrHex.length-1) {
						for (intCHex = intHex+1; intCHex < arrHex.length; intCHex++) {
							for (intCPoint = 0; intCPoint < arrHex[intCHex].length; intCPoint++) {
								if (fComp(arrHex[intHex][intPoint][0],arrHex[intCHex][intCPoint][0]) && fComp(arrHex[intHex][intPoint][1],arrHex[intCHex][intCPoint][1])) {
									arrMatches[intMatch] = [intCHex,intCPoint];
									intMatch++;
								}
							}
						}
					}
					switch (intMatch) {
						case 0:
							intSingles++;
						break;
						case 1:
							intJoins++;
							if (typeof matchTree[intHex] == 'undefined') {
								matchTree[intHex] = {};
							}
							matchTree[intHex][intPoint] = arrMatches[0];
							if (typeof matchTree[arrMatches[0][0]] == 'undefined') {
								matchTree[arrMatches[0][0]] = {};
							}
							matchTree[arrMatches[0][0]][arrMatches[0][1]] = [intHex,intPoint];
							if (arrStart == null) {
								arrStart = [intHex,intPoint];
								arrStop = arrMatches[0];
							}
						break;
						case 2:
							arrHex[intHex][intPoint] = [0,0];
							arrHex[arrMatches[0][0]][arrMatches[0][1]] = [0,0];
							arrHex[arrMatches[1][0]][arrMatches[1][1]] = [0,0];
						break;
					}
				}
			}
		}
		//console.log(JSON.stringify({"MatchTree":matchTree, "Singles":intSingles-intJoins, "Joins":intJoins, "Start":arrStart, "Stop":arrStop, "Hexs":arrHex}));
		//debugHexes2 = {"MatchTree":matchTree, "Singles":intSingles-intJoins, "Joins":intJoins, "Start":arrStart, "Stop":arrStop, "Hexs":arrHex};
		//console.log(JSON.stringify(arrHex),JSON.stringify(matchTree),arrStart,arrStop);
		//console.log('ONCE');
		var coord = contractHexJoins({"MatchTree":matchTree, "Singles":intSingles-intJoins, "Joins":intJoins, "Start":arrStart, "Stop":arrStop, "Hexs":arrHex, "Dir":1});
		if (coord.length < intSingles) {
			//console.log('TWICE');
			coord = contractHexJoins({"MatchTree":matchTree, "Singles":intSingles-intJoins, "Joins":intJoins, "Start":arrStart, "Stop":arrStop, "Hexs":arrHex, "Dir":-1});
		}
		objOut.geometry.coordinates[0] = coord;
		return objOut;
	}
	function advanceHex(intH,intDir) { //This little guy just helps step through a hex defiition
		intH += intDir;
		if (intH>5) {
			intH = 0;
		}
		if (intH<0) {
			intH = 5;
		}
		return intH;
	}

	function contractHexJoins(objHex) { //Does what it says.  It takes an object of hexes and spits out a collapsed coord set.
		var arrOriginalHex = JSON.parse(JSON.stringify(objHex.Hexs)); //Copy hack because as you know object reference assignments are just pointer references when what we need is a copy since changing the varible directly that is passed to a function is what assholes do.
		var intIterations = 0;
		var intHexPoint = 0;
		var markerHex = objHex.Start[0];
		var matchLoc = [];
		var markerPoint = objHex.Start[1];
		var arrHex = [arrOriginalHex[markerHex][markerPoint]];
		arrOriginalHex[markerHex][markerPoint] = [0,0];
		markerPoint = advanceHex(markerPoint,objHex.Dir);
		while (intIterations < 100 && !(markerHex == objHex.Stop[0] && markerPoint == objHex.Stop[1])) {
			//console.log(markerHex+","+markerPoint+": ["+arrOriginalHex[markerHex][markerPoint][0]+","+arrOriginalHex[markerHex][markerPoint][1]+"]");
			if (arrOriginalHex[markerHex][markerPoint][0] != 0) {
				if (typeof objHex.MatchTree[markerHex][markerPoint] != 'undefined') {
					matchLoc = objHex.MatchTree[markerHex][markerPoint];
					//erase point
					arrOriginalHex[markerHex][markerPoint]=[0,0];
					//change marker to matched point
					markerHex = matchLoc[0];
					markerPoint = matchLoc[1];
				}
				//record point
				arrHex.push(arrOriginalHex[markerHex][markerPoint]);
				//erase point
				arrOriginalHex[markerHex][markerPoint]=[0,0];
			}
			markerPoint = advanceHex(markerPoint,objHex.Dir);
			intIterations++;
		}
		return arrHex;
	}

	var geoJ = {"type":"FeatureCollection","features":[]};
	var links = {}; //Lattice coords
	var fac2terr = {}; //A reference for getting the territory ID from a facility ID
	var featureCount = 0;

	function cLat(lat) { //Conversion for my particular map system
		return lat/32-128;
	}
	function cLng(lng) { //Conversion for my particular map system
		return lng/32+128;
	}

	var arrFac = [
		{
			name: "Amp Station",
			layer: "bases",
			class: "amp-station",
			append:" Amp Station"
		},
		{
			name: "Bio Lab",
			layer: "bases",
			class: "bio-lab",
			append:" Bio Lab"
		},
		{
			name: "Large Outpost",
			layer: "facilities",
			class: "large-outpost",
			append:""
		},
		{
			name: "Small Outpost",
			layer: "outposts",
			class: "small-outpost",
			append:""
		},
		{
			name: "Tech Plant",
			layer: "bases",
			class: "tech-plant",
			append:" Tech Plant"
		},
		{
			name: "Warpgate",
			layer: "gates",
			class: "warpgate",
			append:""
		}
	];
	var terr = {};
	for (f = 0; f<arrFac.length; f++) {
		for (h = 0; h < zone.zone_list[0].regions[arrFac[f].name].length; h++) {
			console.log(h);
			terr = zone.zone_list[0].regions[arrFac[f].name][h];
			if (typeof terr.hex != 'undefined') {
			geoJ.features[featureCount] = processHexes(terr.hex,terr.map_region_id);
				featureCount++;
			}
			markers[terr.map_region_id] = {
				"clickable":false,"icon":{"className":"icon "+arrFac[f].class,"iconAnchor":[16,16],"iconSize":[32,32],"type":"div"},"label":{"message":terr.facility_name+arrFac[f].append,"options":{"noHide":true}},"layer":arrFac[f].layer,"lng":cLng(terr.location_z),"lat":cLat(terr.location_x),"zIndexOffset":500
			}
			if (typeof missingMarkerLocations[terr.map_region_id] != 'undefined') {
				markers[terr.map_region_id].lat = missingMarkerLocations[terr.map_region_id].lat;
				markers[terr.map_region_id].lng = missingMarkerLocations[terr.map_region_id].lng;
			}
			fac2terr[terr.facility_id] = terr.map_region_id;
		}

	}
	var pA = 0;
	var pB = 0;
	for (l=0;l<linkRaw.facility_link_list.length;l++) {
		pA = fac2terr[linkRaw.facility_link_list[l].facility_id_a];
		pB = fac2terr[linkRaw.facility_link_list[l].facility_id_b];
		links[pA+"x"+pB+"xA"] = {"color":"#FFF","opacity":1,"weight":6,"latlngs":[{"lat":markers[pA].lat,"lng":markers[pA].lng},{"lat":markers[pB].lat,"lng":markers[pB].lng}],"layer":"lattice"};
		links[pA+"x"+pB+"xB"] = {"color":"#79e0e1","opacity":1,"weight":4,"latlngs":[{"lat":markers[pA].lat,"lng":markers[pA].lng},{"lat":markers[pB].lat,"lng":markers[pB].lng}],"layer":"lattice"};
	}
	var exportJSON = {"geoJSON":geoJ,"markers":markers,"paths":links,"fac2Terr":fac2terr,"circles":circles};
	//console.log(JSON.stringify(exportJSON));
	console.log("Use the following command: copy(JSON.stringify(exportJSON));");

	/*var debugHexes = null;
	var debugHexes2 = null;
	var debugHex = [{"hex_type":"0","type_name":"Unrestricted access","x":"-11","y":"6"},{"hex_type":"0","type_name":"Unrestricted access","x":"-10","y":"5"},{"hex_type":"0","type_name":"Unrestricted access","x":"-10","y":"6"},{"hex_type":"0","type_name":"Unrestricted access","x":"-9","y":"5"}];
	geoJ.features[0] = processHexes(debugHex,1);

	console.log(JSON.stringify(geoJ.features[0]));*/


</javascript>
