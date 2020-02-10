<?php

//TODO: test
// Use cookie to store option to automatically refresh, with refresh time
//  - Can be used between sessions
//  - Automatically disabled if location is automatically searched? or keep refreshing manual search?
// Include warning about inaccuracies around powerlines and inside. 
//  - Where to put it?
//create a humidex folder in the wordpress directory. Place this file (humidex.php) in that folder
$wordPressPage = 10; //example
$pageRoot      = "./humidex/";
$page          = $pageRoot."humidex.php"; // The location of this file in relation to domain
$sec           = "10"; //how often AJAX refreshes the data in minutes
$API           = '1f24bf51cc35eacfb918e79a89f77618'; // This is the api from openweather map
$OWMWeatherURL = 'http://api.openweathermap.org/data/2.5/weather?units=metric&appid=' . $API. '&';
$OWMUvURL      = 'http://api.openweathermap.org/data/2.5/uvi?appid=' . $API. '&';

//prevent xss
$postData      = array_map('htmlspecialchars', $_REQUEST);


//bom doesn't provide easy access to data. 
//it takes more than 30 seconds to get this data every time fom http://www.bom.gov.au/qld/observations/brisbane.shtml
// so we get it manually. I have provided an easy way to update this in getStations();
//getStations('http://www.bom.gov.au/qld/observations/brisbane.shtml');
$stations = array(array('name' => 'Brisbane','json' => 'IDQ60901/IDQ60901.94576.json','lat' => -27.5,'lon' => 153),
	array('name' => 'Brisbane Airport','json' => 'IDQ60901/IDQ60901.94578.json','lat' => -27.4,'lon' => 153.1),
	array('name' => 'Amberley','json' => 'IDQ60901/IDQ60901.94568.json','lat' => -27.6,'lon' => 152.7),
	array('name' => 'Archerfield','json' => 'IDQ60901/IDQ60901.94575.json','lat' => -27.6,'lon' => 153),
	array('name' => 'Banana Bank','json' => 'IDQ60901/IDQ60901.94591.json','lat' => -27.5,'lon' => 153.3),
	array('name' => 'Beerburrum','json' => 'IDQ60901/IDQ60901.95566.json','lat' => -27,'lon' => 153),
	array('name' => 'Beaudesert AWS','json' => 'IDQ60901/IDQ60901.95575.json','lat' => -28,'lon' => 153),
	array('name' => 'Canungra (Defence)','json' => 'IDQ60901/IDQ60901.94418.json','lat' => -28,'lon' => 153.2),
	array('name' => 'Cape Moreton','json' => 'IDQ60901/IDQ60901.94594.json','lat' => -27,'lon' => 153.5),
	array('name' => 'Coolangatta','json' => 'IDQ60901/IDQ60901.94592.json','lat' => -28.2,'lon' => 153.5),
	array('name' => 'Double Island Point','json' => 'IDQ60901/IDQ60901.94584.json','lat' => -25.9,'lon' => 153.2),
	array('name' => 'Gatton','json' => 'IDQ60901/IDQ60901.94562.json','lat' => -27.5,'lon' => 152.3),
	array('name' => 'Gold Coast Seaway','json' => 'IDQ60901/IDQ60901.94580.json','lat' => -27.9,'lon' => 153.4),
	array('name' => 'Greenbank (Defence)','json' => 'IDQ60901/IDQ60901.94419.json','lat' => -27.7,'lon' => 153),
	array('name' => 'Gympie','json' => 'IDQ60901/IDQ60901.94566.json','lat' => -26.2,'lon' => 152.6),
	array('name' => 'Inner Beacon','json' => 'IDQ60901/IDQ60901.94590.json','lat' => -27.3,'lon' => 153.2),
	array('name' => 'Kingaroy','json' => 'IDQ60901/IDQ60901.94549.json','lat' => -26.6,'lon' => 151.8),
	array('name' => 'Logan City','json' => 'IDQ60901/IDQ60901.95581.json','lat' => -27.7,'lon' => 153.2),
	array('name' => 'Nambour','json' => 'IDQ60901/IDQ60901.95572.json','lat' => -26.6,'lon' => 152.9),
	array('name' => 'Oakey','json' => 'IDQ60901/IDQ60901.94552.json','lat' => -27.4,'lon' => 151.7),
	array('name' => 'Point Lookout','json' => 'IDQ60901/IDQ60901.94593.json','lat' => -27.4,'lon' => 153.5),
	array('name' => 'Rainbow Beach','json' => 'IDQ60901/IDQ60901.94564.json','lat' => -25.9,'lon' => 153.1),
	array('name' => 'Redcliffe','json' => 'IDQ60901/IDQ60901.95591.json','lat' => -27.2,'lon' => 153.1),
	array('name' => 'Redland (Alexandra Hills)','json' => 'IDQ60901/IDQ60901.94561.json','lat' => -27.5,'lon' => 153.2),
	array('name' => 'Spitfire Channel','json' => 'IDQ60901/IDQ60901.94581.json','lat' => -27,'lon' => 153.3),
	array('name' => 'Sunshine Coast Airport','json' => 'IDQ60901/IDQ60901.94569.json','lat' => -26.6,'lon' => 153.1),
	array('name' => 'Tewantin','json' => 'IDQ60901/IDQ60901.94570.json','lat' => -26.4,'lon' => 153),
	array('name' => 'Tin Can Bay (Defence)','json' => 'IDQ60901/IDQ60901.94420.json','lat' => -25.9,'lon' => 153),
	array('name' => 'Toowoomba','json' => 'IDQ60901/IDQ60901.95551.json','lat' => -27.5,'lon' => 151.9),
	array('name' => 'Warwick','json' => 'IDQ60901/IDQ60901.94555.json','lat' => -28.2,'lon' => 152.1),
	array('name' => 'Wellcamp Airport','json' => 'IDQ60901/IDQ60901.99435.json','lat' => -27.6,'lon' => 151.8));


function getStations($url){
	// regenerate the locations array
	$doc = new DOMDocument('1.0', 'UTF-8');
	libxml_use_internal_errors(true);
	$doc->loadHTML(getData($url));
	$strOut =  "<span>\$stations = array(";
	foreach ( $doc->getElementsByTagName('th') as $node ) {
		if (preg_match("/^rowleftcolumn$/", $node->getAttribute('class'))) {
			//echo $node->nodeValue, '<br />';
			
			foreach($node->getElementsByTagName('a') as $element) {
				$href = substr(substr($element->getAttribute('href'),9),1,-6) . '.json';
				$data = getData('http://www.bom.gov.au/fwo/' . $href);
				$jsonData = json_decode($data, true);
				$strOut = $strOut . "array('name' => '" . $node->nodeValue 
					. "','json' => '" . $href 
					. "','lat' => " . $jsonData['observations']['data'][0]['lat'] 
					. ",'lon' => " . $jsonData['observations']['data'][0]['lon'] 
					. "'),</span></br>";

				
			}
			
		}
	}
	$strOut = substr($strOut, 0, -13)  . "</br></span>\n);";
	echo $strOut;
}
function getData($url){
    $context = stream_context_create(
        array(
            "http" => array(
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
            )
        )
	);
	$data = null;
	while (!$data){
		@$data = file_get_contents($url,false, $context);
	}
    return $data;
}
//basic humidex formula
function calculateHumidex($t, $h) {
    $Kconst    = 273.15;
    $dewpoint  = pow(($h / 100), (1 / 8)) * (112 + (0.9 * $t)) + (0.1 * $t) - 112;
    $dewpointK = $dewpoint + $Kconst;
    return round($t + 0.5555 * (6.11 * pow(exp(1), (5417.7530 * ((1 / 273.16) - (1 / $dewpointK)))) - 10), 2);
}

function distance($lat1, $lon1, $lat2, $lon2, $unit) {
	if (($lat1 == $lat2) && ($lon1 == $lon2)) {
	  return 0;
	}
	else {
	  $theta = $lon1 - $lon2;
	  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	  $dist = acos($dist);
	  $dist = rad2deg($dist);
	  $miles = $dist * 60 * 1.1515;
	  $unit = strtoupper($unit);
  
	  if ($unit == "K") {
		return ($miles * 1.609344);
	  } else if ($unit == "N") {
		return ($miles * 0.8684);
	  } else {
		return $miles;
	  }
	}
  }
  
function min_array_value($array,$key_name){
	$min = $array[0];
	foreach($array as $key => $value){
		if (is_numeric($value[$key_name])){
			$make_array[] = $value[$key_name];
			$min = min($make_array);
		}
	}
	foreach($array as $key => $value){
		if ($array[$key]['distance'] == $min){
			return $array[$key]['station'];
		}
	}
}
function checkValidVar($var){
	if ($var == null || $var=='' || !isset($var)){
		return false;
	} else {
		return true;
	}
}

function getNearestStation($location,$recursed=false){
    global $stations,$OWMWeatherURL;
    if ($recursed && !checkValidVar($location['city'])){
		return array('error' => 'value is not set');
    }

	$min_val = null;
	$min_key = null;
	foreach ($stations as $key => $value) {
		if (checkValidVar($location['city']) && strtolower($location['city']) == strtolower($stations[$key]['name'])){
            $station_ret = $stations[$key];
            //return $stations[$key];
		} else {
			$newStations[] = array(
				'distance'=>distance($stations[$key]['lat'], $stations[$key]['lon'], $location['lat'], $location['lon'], 'k'), 
				'station' => $stations[$key]
			);
		}
    }
    if(!isset($station_ret)){
        //$data = json_decode(getData($OWMWeatherURL.'q='.$location['city']).',au',true);
       
        @$station_ret = getNearestStation(array(
            'lon' => (isset($data['lon'])?$data['lon']:null),
            'lat' => (isset($data['lat'])?$data['lat']:null),
            'city' => (isset($data['city'])?$data['city']:null)
		),true);
    } else {
        return $station_ret;
    }

	return(min_array_value($newStations,'distance'));

}

if (isset($postData['auto_loc']) || isset($postData['manual_loc'])) {
    try {
        $station = getNearestStation(array(
            'lon' => (isset($postData['lon'])?$postData['lon']:null),
            'lat' => (isset($postData['lat'])?$postData['lat']:null),
            'city' => (isset($postData['city'])?$postData['city']:null)
		));
    } catch (exception $e) {
       echo  json_encode(array(
            'error' => $e . message . 'on line: ' . e . line
        ));
    }

    try {
        $weatherData = json_decode(getData('http://bom.gov.au/fwo/' . $station['json']),true);
        $lastUpdate = $weatherData['observations']['header'][0]['refresh_message'];     
        $uv         = 5;
        //$uv         = json_decode(getData($OWMUvURL.'lat='.$postData['lat'].'&lon='.$postData['lon']),true)['value'];
        $temp       = $weatherData['observations']['data'][0]['air_temp'];
        $humidity   = $weatherData['observations']['data'][0]['rel_hum'];
        $location   = $weatherData['observations']['data'][0]['name'];
        $desc       = "";
        $humidex    = calculateHumidex($temp, $humidity);
        // make the json
        $json_ret   = array(
            'temp' => $temp,
            'humidity' => $humidity,
            'humidex' => $humidex,
            'location' => $location,
            'desc' => $desc,
            'uv' => $uv,
            'last_update' => $lastUpdate
        );       
    }
    catch (exception $e) {
        $json_ret = array(
            'error' => $e . message . 'on line: ' . e . line
        );
    }
   echo json_encode($json_ret);
} else {
    //check we are in wordpress and redirect if we aren't
    if (headers_sent() == false) {
        header('Location:../?page_id=' . $wordPressPage, true, 302);
        exit();
    } else{
?><html>
<head>
  <meta name='Description' content='Automatically get the humidex for  the current location.'>
  <style>
    div.frm_location {
        display:table;
        width:100%;
    }
    div.frm_location span {
        display:table-cell;
    }
    div.frm_location input[type="image"]{

        width: 3em;
        margin-bottom:-1em;
        margin-right:1em;
        padding-top: 1em;
    }
    div.frm_location span a{
        width:9;
        height:9;
    }
      .wrapper {
      display: grid;
      }

      #loading, #weatherResults {
      grid-area: 1 / 1;
      }
      #container{
      bottom: 0;
      left: 0;
      margin: auto;
      position: relative;
      top: 0;
      right: 0;
      width: 100%;
      }
      .bold{
      font-weight: bold;
      }
      .navigate{
        max-width:2em;
        height:auto;
        float:left;
      }
      
  </style>
  <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js'></script>
  <script type='text/javascript'>
      function isAusPostCode(postcode) {
          var pRE = /^(?:(?:[2-8]\d|9[0-7]|0?[28]|0?9(?=09))(?:\d{2}))$/;
          if (pRE.exec(postcode)) {
              return true;
          } else {
              return false;
          }
      }
      function gotLocation(position) {
          var lat, lon;
          lat = position.coords.latitude;
          lon = position.coords.longitude;
          console.log('Getting data automatically');
          getData('<?php echo $page; ?>?lat=' + lat + '&lon=' + lon + '&auto_loc');
      }
      function getData(url) {
        console.clear();
          jQuery('#weatherResults').css('visibility', 'hidden');
          jQuery('#loading').css('visibility', 'visible');
          console.log('Getting Data using:\n' + url);
          jQuery.ajax({
              url: url,
              method: 'POST',
              success: function(data) {
                  console.log('Data recieved:\n'+data);
                  try {
                      var json = jQuery.parseJSON(data);
                      if (json.error) {
                          jQuery('#frm_err').empty();
                          jQuery('#frm_err').append('<strong>'+json.error+'</strong>');
                          jQuery('#frm_err').css('visibility', 'visible');
                          jQuery('#loading').css('visibility', 'hidden');
                          console.log('Error recieved:\n'+json.error);
                      } else {
                          jQuery('#location').text(json.location);
                          jQuery('#temp').text(json.temp);
                          jQuery('#humidity').text(json.humidity);
                          jQuery('#humidex').text(json.humidex);
                          jQuery('#desc').text(json.desc);
                          jQuery('#lastUpdate').text(json.last_update);
                          jQuery('#uvIndex').text(json.uv);
                          var humidex = json.humidex;
                          if (jQuery('#info').length) {
                              jQuery('#info').empty();
                          }
                          if (humidex > 0 && humidex < 25) {
                              jQuery('#comment').text('No discomfort');
                              jQuery('#info').append('<p id=\"extra\"/>Provide water as on needed basis.');
                              jQuery('#humidexInfo').css('background-color', '#99ff6');
                          }
                          if (humidex >= 24 && humidex < 30) {
                              jQuery('#comment').text('Little discomfort');
                              jQuery('#info').append('<p id=\"extra\"/>Provide water as on needed basis.');
                              jQuery('#humidexInfo').css('background-color', '#ffff99');
                          }
                          if (humidex >= 30 && humidex < 34) {
                              jQuery('#comment').text('Some discomfort');
                              jQuery('#info').append('<p id=\"extra\"/><strong>Post Heat Stress Alert notice</strong><br/>- Encourage workers to drink extra water<br/>- Start recording hourly temperature and relative humidity');
                              jQuery('#humidexInfo').css('background-color', '#ffff00');
                          }
                          if (humidex >= 34 && humidex < 38) {
                              jQuery('#comment').text('Some discomfort');
                              jQuery('#info').append('<p id=\"extra\"/><strong>Post Heat Stress Warning notice</strong><br/>- Notify workers that they need to drink extra water<br/>- Ensure workers are trained to recognise symptoms');
                              jQuery('#humidexInfo').css('background-color', '#ffdf00');
                          }
                          if (humidex >= 38 && humidex < 40) {
                              jQuery('#comment').text('Great discomfort; Avoid Exertion');
                              jQuery('#info').append('<p id=\"extra\"/><strong>Only work with 15 minutes relief per hour should continue</strong><br/>- Provide 240 mL of cool (10-15�c) water every 20 minutes<br/>- Workers with symtoms should seek medical attention');
                              jQuery('#humidexInfo').css('background-color', '#ffaf00');
                          }
                          if (humidex >= 40 && humidex < 42) {
                              jQuery('#comment').text('Dangereous; Heat stroke possible');
                              jQuery('#info').append('<p id=\"extra\"/><strong>Only work with 30 minutes relief per hour should continue</strong><br/>- Provide 240 mL of cool (10-15�c) water every 20 minutes<br/>- Workers with symtoms should seek medical attention');
                              jQuery('#humidexInfo').css('background-color', '#ff8f00');
                          }
                          if (humidex >= 42 && humidex < 45) {
                              jQuery('#comment').text('Dangereous; heat stroke possible');
                              jQuery('#info').append('<p id=\"extra\"/><strong>Only work with 45 minutes relief per hour should continue</strong><br/>- Provide 240 mL of cool (10-15�c) water every 20 minutes<br/>- Workers with symtoms should seek medical attention');
                              jQuery('#humidexInfo').css('background-color', '#ff4f00');
                          }
                          if (humidex >= 45) {
                              jQuery('#comment').text('Only medically supervised work can continue');;
                              jQuery('#humidexInfo').css('background-color', '#ff4f00');
                          }
                          if (humidity < 0 || humidity > 100) {
                              jQuery('#comment').text('Humidity must be between 0 and 100');
                              jQuery('#humidexInfo').css('background-color', '#ffffff');
                          }
                          if (jQuery('#uvInfo').length) {
                              jQuery('#uvInfo').empty();
                          }
                          if (json.uv >= 1 && json.uv <= 2) {
                              jQuery('#uvInfo').append('<strong>Low</strong><br/>No Protection Required');
                              jQuery('#uvInfo').css('background-color', '#58A313');
                          }
                          if (json.uv >= 3 && json.uv <= 5) {
                              jQuery('#uvInfo').append('<strong>Moderate</strong><br/>Protection Requrired - When spending long periods in the sun, especially for fair skin');
                              jQuery('#uvInfo').css('background-color', '#D47B05');
                          }
                          if (json.uv >= 6 && json.uv <= 7) {
                              jQuery('#uvInfo').append('<strong>High</strong><br/>Protection Essential - Slip, Slop, Slap and Slide!');
                              jQuery('#uvInfo').css('background-color', '#CF4818');
                          }
                          if (json.uv >= 8 && json.uv <= 10) {
                              jQuery('#uvInfo').append('<strong>Very High</strong><br/>Seek Shade - Slip, Slop, Slap and Slide! Cover up & reapply sunscreen regularly');
                              jQuery('#uvInfo').css('background-color', '#AF1A20');
                          }
                          if (json.uv >= 11) {
                              jQuery('#uvInfo').append('<div><strong>Extreme</strong><br/>Reschedule outdoor activites for early morning & evening. Full protection is <strong>essential</strong></div>');
                              jQuery('#uvInfo').css('background-color', '#6B1538');
                          }
                          jQuery('#weatherResults').css('visibility', 'visible');
                          jQuery('#loading').css('visibility', 'hidden');
                      }
                  } catch (e) {
                      jQuery('#frm_err').css('visibility', 'visible');
                      jQuery('#frm_err').empty();
                      jQuery('#frm_err').append('Caught error:\n'+e);
                      jQuery('#loading').css('visibility', 'hidden');
                      console.log('Caught error:\n'+e);
                  }
              }
          });
      }
      /*jQuery(document).ready(function() {
          getLocation();
      });*/
      function getLocation() {
          navigator.permissions.query({
              name: 'geolocation'
          }).then(function(permissions) {
              if (permissions.state == 'prompt' || permissions.state == 'granted'){
                  if (navigator.geolocation) {
                      navigator.geolocation.getCurrentPosition(gotLocation);
                      jQuery('#frm_automatic').prop('disabled', false);
                      jQuery('#frm_automatic').css('visibility', 'visible');
                      jQuery('#frm_err').css('visibility', 'hidden');
                  } else {
                      jQuery('#frm_automatic').css('visibility', 'hidden');
                      jQuery('#frm_err').empty();
                      jQuery('#frm_err').append('<strong>Geolocation is not supported by this browser.</strong>');
                      jQuery('#frm_err').css('visibility', 'visible');
                      jQuery('#frm_automatic').prop('disabled', true);
                      jQuery('#frm_automatic').css('visibility', 'hidden');
                  }
              } else {
                  jQuery('#frm_err').empty();
                  jQuery('#frm_err').append('<strong>You need to allow location in your browser to allow automatic results. </br> You can still manually enter your location.</strong>');
                  jQuery('#frm_err').css('visibility', 'visible');
                  jQuery('#frm_automatic').prop('disabled', true);
                  jQuery('#frm_automatic').css('visibility', 'hidden');
              }
          });
      }
      function frm_onFocus(frm_input) {
          jQuery(frm_input).val('');
          jQuery(frm_input).keyup(function() {
              console.log(frm_input.value);
              if (jQuery(frm_input).val() == '') {
                  jQuery('.enableOnInput').prop('disabled', true);
              } else {
                  jQuery('.enableOnInput').prop('disabled', false);
              }
          });
      }
      function verifyInput() {
          //console.log('Base:'+jQuery('#frm_loc').val()+'\nEncoded:'+encodeURI(jQuery('#frm_loc').val()));
          var frm_loc = encodeURI(jQuery('#frm_loc').val());
          var error = true;
          if (isAusPostCode(frm_loc) || frm_loc.match(/[a-z]/i)) {
              if (frm_loc != '') {
                  error = false;
                  //console.log('Getting data manually');
                  getData('<?php echo $page; ?>?city=' + frm_loc + '&manual_loc');
              }
              if (error) {
                  jQuery('#frm_err').css('visibility', 'visible');
                  jQuery('#weatherResults').css('visibility', 'hidden');
                  jQuery('#frm_err').empty();
                  jQuery('#frm_err').append('<strong>That is not a valid post code or location.</strong>')
              } else{
                  jQuery('#frm_err').css('visibility', 'hidden');
                  jQuery('#frm_loc').blur();
              }
          }
          return false;
      }
  </script>
</head>
<body>
  <div id='container'>
  test 
      <div id='body' style='margin:1em;width:100%' >

          <div class='frm_location' style='visibility:visible' >
                <form onsubmit='return verifyInput();'>
                    <span><input type="image" onClick='getLocation();' src='<?php echo $pageRoot; ?>navigate.png'></span>
                    <span><input id='frm_loc' type='text' onfocus='frm_onFocus(this)' onfocousout='frm_focusOut(this)'  placeholder='(broken)Suburb or postcode'/></span>
                    <span><input class='enableOnInput' type='submit' disabled='disabled' value='Submit'></span>
              </form>
          </div>
          <div style='float:none;padding:1em;'>
              <span id='frm_err' style='color:red;visibility:hidden;float:left;padding:1em;'></span>
              
              <button id='frm_automatic' onClick='getLocation();' >Refresh</button>
             
          </div>
          <div name='results' id='weatherResults'  style='visibility: hidden;position: relative;'>
              <div id='loading' style='position: absolute;z-index: 1;' ><h4>Loading Weather data....</h4>
              <noscript>Sorry, your browser does not support JavaScript!</noscript>
          </div>
          <div >
              <ul style='list-style-type: none;margin-right:1em;'>
                  <li style='padding-top:1em'>Location: <span id='location'></span></li>
                  <li>Temperature: <span id='temp'></span>�c</p>
                  <li>Humidity: <span id='humidity'></span>%</li>
                  <li>Humidex: <span id='humidex' class='bold'></span></li>
                  <div id='humidexInfo' style='padding: 1em;border-style:solid;border-width:medium;'>
                      <span id='comment'></span><br/>
                      <span class='bold' id='notice'></span>
                      <span id='info'></span>
                  </div>
                  <li style='padding-top:1em'>UV Index: <span id='uvIndex'></span></li>
                  <div id='uvInfo' style='padding: 1em;border-style:solid;border-width:medium;'></div>  
              </ul>
          
              
              <p><span id='lastUpdate'></span></p>
              <?php
                  //<!-� This is requered by the openweathermap.org licence conditions --> 
                  //  <p>Weather data current as of <span id='lastUpdate'> (GMT+10) using the <a href='http://openweathermap.org'>openweathermap.org</a> api</span> </p> ?>
</div>
      </div>
      <div id='ilnesses'>
          <h4>Symptoms of Heat Exhaustion</h4>
          <br/>The most common signs and symptoms of heat exhaustion include:
          <ul>
          <li>Confusion</li>
          <li>Dark-colored urine <em>(See dehydration chart below)</em><strong>*</strong></li>
          <li>Dizziness</li>
          <li>Fainting<strong>*</strong></li>
          <li>Fatigue</li>
          <li>Headache</li>
          <li>Muscle or abdominal cramps</li>
          <li>Nausea, vomiting, or diarrhea<strong>*</strong></li>
          <li>Pale skin</li>
          <li>Profuse sweating</li>
          <li>Rapid heartbeat</li>
          <li style='list-style-type: none;'><strong>*<em>Please seek medical attention if these symptoms are present.</em></strong></li>
          </ul>
          <h4>Dehydration Chart</h4>
          <table style='border: solid 2px black; border-collapse: collapse;' cellspacing='0' cellpadding='4'>
          <tbody>
              <tr style='background-color: #ededed; padding: 10px;'>
              <td style='border-top: 1px solid black; border-right: 1px solid black; border-bottom: 2px solid black; padding: 4px;' width='29%'>What Color?</td>
              <td style='border-top: 1px solid black; border-bottom: 2px solid black;' width='70%'>Are You Hydrated?</td>
              </tr>
              <tr>
              <td style='border-right: 1px solid black;' valign='top'>
                  <div style='background-color:#FFFFED; width:80%;height:30px;margin:5px;border-style:solid;border-width:medium;'></div>
              </td>
              <td valign='top'>Pale yellow to clear:<br/> Well hydrated.</td>
              </tr>
              <tr>
              <td style='border-right: 1px solid black;' valign='top'>
                  <div style='background-color:#FFFFCC; opacity: .9; width:80%;height:30px;margin:5px;border-style:solid;border-width:medium;'></div>
              </td>
              <td valign='top'>Light yellow and transparent:<br/> Normal hydration.</td>
              </tr>
              <tr>
              <td style='border-right: 1px solid black;' valign='top'>
                  <div style='background-color:#FCE883; opacity: .8; width:80%;height:30px;margin:5px;border-style:solid;border-width:medium;'></div>
              </td>
              <td valign='top'>A pale honey, transparent color: <br/>Normal hydration. Rehydrate soon</td>
              </tr>
              <tr>
              <td style='border-right: 1px solid black;' valign='top'>
                  <div style='background-color:#EFCC00; opacity: .7; width:80%;height:30px;margin:5px;border-style:solid;border-width:medium;'></div>
              </td>
              <td valign='top'>A yellow, more cloudy color: <br/>Dehydrated. Rehydrate</td>
              </tr>
              <tr>
              <td style='border-right: 1px solid black;' valign='top'>
                  <div style='background-color:#EED202; opacity: .7; width:80%;height:30px;margin:5px;border-style:solid;border-width:medium;'></div>
              </td>
              <td valign='top'>A darker yellow, amber color: <br/>Very dehydrated. <strong>Rehydrate now</strong></td>
              </tr>
              <td style='border-right: 1px solid black;' valign='top'>
              <div style='background-color:#DAA520; width:80%;height:30px;margin:5px;border-style:solid;border-width:medium;'></div>
              </td>
              <td valign='top'>Orangish yellow and darker: <br/>Severely dehydrated. <strong>Rehydrate <em>immediately</em> and seek medical attention</strong></td>
          </tr>
          </tbody>
      </table>
      </div>
      <div id='footer'>
      <span id='author'><h6>Developed by B4C's Christopher Gray</h6></span>
      </div>
  </div>
</div>
</body>
</html>
<?php
}
}

?>
