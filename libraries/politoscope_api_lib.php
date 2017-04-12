<?php // Set of function to process data from politicoscope API

include  'politoscope_api_keys.php';
function api_politic_france_network_supporters_twitter_histogram($query,$candidate,$level,$condition,$interval,$since){
	global $api_keys;
		// fait la requête $quey à l'api politics et retourne le résultat en JSON
	$query= urlencode ($query);
	$curl = curl_init();
	if (strlen($query)>0){
		
			curl_setopt_array($curl, array(
	CURLOPT_URL => "https://api.iscpif.fr/v2/pvt/politic/france/network/supporters/twitter/histogram?q=(".$query.')&candidate='.$candidate.'&levels='.$level.'&condition='.$condition.'&interval='.$interval.'&since='.$since.'&api_key='.$api_keys.'&project=politoscope&project_key='.$api_keys,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => array(
	    "cache-control: no-cache",
	    "postman-token: d5e6056f-3614-9d9a-494e-4185887e7661"
	  ),
	));

	}else{
			curl_setopt_array($curl, array(
	CURLOPT_URL => "https://api.iscpif.fr/v2/pvt/politic/france/network/supporters/twitter/histogram?&candidate=".$candidate.'&levels='.$level.'&condition='.$condition.'&interval='.$interval.'&since='.$since.'&api_key='.$api_keys.'&project=politoscope&project_key='.$api_keys,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => array(
	    "cache-control: no-cache",
	    "postman-token: d5e6056f-3614-9d9a-494e-4185887e7661"
	  ),
	));

	}	

	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  return $response;
	}
	}

function api_hist_query($query,$interval,$since){
	global $api_keys;
		// fait la requête $quey à l'api politics et retourne le résultat en JSON
	$query= urlencode ($query);
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://api.iscpif.fr/v2/pub/politic/france/twitter/histogram?q=(".$query.')&interval='.$interval.'&since='.$since.'&api_key='.$api_keys,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
	    "cache-control: no-cache",
	    "postman-token: 2fda2115-508c-d1a0-1997-6b9b68da5fe8"
	  ),
	));



	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  return $response;
	}
	}

function api_politic_france_twitter_search($query){
	global $api_keys;
		// fait la requête $quey à l'api politics et retourne le résultat en JSON
	$query= urlencode ($query);
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://api.iscpif.fr/v2/pvt/politic/france/twitter/search?q=(".$query.')&since='.$since.'&api_key='.$api_keys,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
	    "cache-control: no-cache",
	    "postman-token: 2fda2115-508c-d1a0-1997-6b9b68da5fe8"
	  ),
	));



	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  return $response;
	}
	}	

function api_search_query($query,$interval,$since){
	global $api_keys;
		// fait la requête $quey à l'api politics et retourne le résultat en JSON
	$query= urlencode ($query);
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://api.iscpif.fr/v2/pub/politic/france/twitter/histogram?q=(".$query.')&interval='.$interval.'&since='.$since.'&api_key='.$api_keys,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
	    "cache-control: no-cache",
	    "postman-token: 2fda2115-508c-d1a0-1997-6b9b68da5fe8"
	  ),
	));



	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {		
	  return $response;
	}
	}




?>