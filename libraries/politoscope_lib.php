<?php // Set of function to process data from politicoscope API

function array_convolution($array,$time_windows){
//  does the average of values of array on a sliding windows
	$output=array();
	for ($i=0;$i<$time_windows;$i++){
		$output[$i]=0;		
	}
	for ($i=$time_windows;$i<count($array);$i++){
		$output[$i]=array_sum(array_slice($array,($i-$time_windows+1),$time_windows))/$time_windows;		
	}
	return $output;
        }



function get_network_supporters_hist_data($q,$candidate,$level,$include,$interval,$since,$normalisation){
// renvoie les données associée à la requête $q sur la communauté de $candidate                    
            //echo $q.'<br/>';
            if (strlen($q)>2){
                $json=api_politic_france_network_supporters_twitter_histogram($q,$candidate,$level,$include,$interval,$since); 
                //print_r($json);
                if ($normalisation==1){
                    $jsonNorm=api_politic_france_network_supporters_twitter_histogram('',$candidate,$level,$include,$interval,$since);                                              
                    $data=network_supporters_hist_JSON2PhPArray($json,$jsonNorm);    
                }else{
                    $data=network_supporters_hist_JSON2PhPArray($json);        
                }
                
                
            }  
            return $data;
        }

function data2ChartData2($AllData,$data,$selfmention,$candidatesNames,$candidateRank,$MentionnedCandidate){
// incémente le tableau $ALlData avec les données $data reformatées pour le chord chart
	//$data : array with [0] : array of scores [1] array of periods label ordered in asc periods
	$counter=0;
	$cand=array();
	$ment=array();
	$score=array();


	foreach ($data[0] as $key1 => $value1) {
		$AllData[$data[1][$counter]]['candidates'][]=trim($candidatesNames[$candidateRank]);
		$AllData[$data[1][$counter]]['mention'][]=trim($MentionnedCandidate);
		if (strcmp($candidatesNames[$candidateRank], $MentionnedCandidate)==0){
			if ($selfmention==0){
				$AllData[$data[1][$counter]]['count'][]=0;    
			}else{
				$AllData[$data[1][$counter]]['count'][]=$data[0][$counter];
			}                    
		}else{                    
			$AllData[$data[1][$counter]]['count'][]=$data[0][$counter];    
		}

		$counter+=1;
	}   
	$remainingDates=array_diff(array_keys($AllData), $data[1]);
	if (count($remainingDates)>0){
		foreach ($remainingDates as $key => $date) {
			$AllData[$date]['candidates'][]=trim($candidatesNames[$candidateRank]);
			$AllData[$date]['mention'][]=trim($MentionnedCandidate);
			$AllData[$date]['count'][]=0;
		}
	}
	

	return $AllData;
}        

function data2ChartData($AllData,$data,$selfmention,$candidatesNames,$candidateRank,$MentionnedCandidate){
// incémente le tableau $ALlData avec les données $data reformatées pour le chord chart
	//$data : array with [0] : array of scores [1] array of periods label ordered in asc periods
	$counter=0;
	$cand=array();
	$ment=array();
	$score=array();
	foreach ($data[0] as $key1 => $value1) {
		$AllData[$data[1][$counter]]['candidates'][]=$candidatesNames[$candidateRank];
		$AllData[$data[1][$counter]]['mention'][]=$MentionnedCandidate;
		if (strcmp($candidatesNames[$candidateRank], $MentionnedCandidate)==0){
			if ($selfmention==0){
				$AllData[$data[1][$counter]]['count'][]=0;    
			}else{
				$AllData[$data[1][$counter]]['count'][]=$data[0][$counter];
			}                    
		}else{                    
			$AllData[$data[1][$counter]]['count'][]=$data[0][$counter];    
		}

		$counter+=1;
	}       
	return $AllData;
}

function getColumn($file,$label){
	//give back the column number of the csv corresponding to label	
	$f= fopen($file, "r","UTF-8");
	$line = fgetcsv($f,4000,';');
	fclose($f);
    $label_column_number=array_search($label, $line);
    return $label_column_number;                
}

function network_supporters_hist_JSON2PhPArray($json,$jsonNorm){
	//  gives a PHP array of Y value with Xlabels. X ticks are regular.

if (!is_null($jsonNorm)){// on calcul les données pour la normalisation
	$dataNorm=json_decode($jsonNorm);
	if (count($dataNorm)>0){
		foreach ($dataNorm as $key => $value) {
			$array = json_decode(json_encode($value), True);
			$eventsNorm=$array['hits'];
		}
		$norm=array();
		foreach ($eventsNorm as $key => $value) {
  		$date=explode('T', $value['key_as_string']);//strtotime();  
  		$date=explode('-',$date[0]);
  		$norm[($date[0].','.($date[1]).','.$date[2])]=trunc($value['doc_count']);  
  	}	
  }	
}


$data=json_decode($json);
$Ydata=array();
$XLabel=array();

foreach ($data as $key => $value) {
	$array = json_decode(json_encode($value), True);

	if ((is_array($array))&&(array_key_exists('hits', $array)==1)){
		$events=$array['hits'];	  		
		foreach ($events as $key => $value) {
	 	 	$date=explode('T', $value['key_as_string']);//strtotime();  
  			$date=explode('-',$date[0]);  
  			if (!is_null($jsonNorm)){// on normalise
  				if ($norm[($date[0].','.($date[1]).','.$date[2])]==0){
  					$Ydata[]=0;	
  				}else{
  					$Ydata[]=100*$value['doc_count']/$norm[($date[0].','.($date[1]).','.$date[2])];  	
  				}  				  				
  			}else{
  				$Ydata[]=$value['doc_count'];  	
  			}
  			$XLabel[]= date("d.m.y",mktime(0,0,0,($date[1]),$date[2],$date[0]));
		}
	}
}

return array($Ydata,$XLabel);
}

function get_field($json,$field_name){
	// get the value of the field named $field_name in the $json
	$data=json_decode($json);
	return $data[$field_name];
}


function network_supporters_hist_JSON2Array($json){
	//  echart format. gives an array of Y value with Xlabels. X ticks are regular.
	$data=json_decode($json);
foreach ($data as $key => $value) {
    $array = json_decode(json_encode($value), True);
    $events=$array['hits'];
}

$Ydata='[';
$XLabel='[';

foreach ($events as $key => $value) {
  $date=explode('T', $value['key_as_string']);//strtotime();  
  $date=explode('-',$date[0]);
  
  $Ydata.=$value['doc_count'].',';  
  $XLabel.= date("d.m.y",mktime(0,0,0,($date[1]-1),$date[2],$date[0])).',';
}

$Ydata=substr($Ydata, 0,-1).']';
return array($Ydata,$XLabel);
}


function JSON2HighChartArray($json,$jsonNorm){
if (!is_null($jsonNorm)){// on calcul les données pour la normalisation
	$dataNorm=json_decode($jsonNorm);	
	
	foreach ($dataNorm as $key => $value) {
	    $array = json_decode(json_encode($value), True);
	    $eventsNorm=$array['hits'];	    
	}
	$norm=array();
	foreach ($eventsNorm as $key => $value) {
  		$date=explode('T', $value['key_as_string']);//strtotime();  
  		$date=explode('-',$date[0]);
  		$norm[($date[0].','.($date[1]-1).','.$date[2])]=trunc($value['doc_count']);  
	}
}


$data=json_decode($json);
foreach ($data as $key => $value) {
    $array = json_decode(json_encode($value), True);
    $events=$array['hits'];
}


$Ydata='[';

foreach ($events as $key => $value) {
  $date=explode('T', $value['key_as_string']);//strtotime();  
  $date=explode('-',$date[0]);
  if (!is_null($jsonNorm)){// on normalise
  	$Ydata.='[Date.UTC('.$date[0].','.($date[1]-1).','.$date[2].'),'.(100*$value['doc_count']/$norm[($date[0].','.($date[1]-1).','.$date[2])]).'],';
  }else{
  	$Ydata.='[Date.UTC('.$date[0].','.($date[1]-1).','.$date[2].'),'.$value['doc_count'].'],';  	
  }
  
}

$Ydata=substr($Ydata, 0,-1).']';
return $Ydata;
}

function query_limiter($query,$max_length){
	// resize a query string to avoid too long queries
	// we cut after an OR
	if (strlen($query)<$max_length){
		return $query;
	}else{
		$q=substr($query,0,$max_length);
		$pos=strrpos($q,'OR');
		return substr($q,0,$pos);	
	}
	
}

function trunc($number){
	return floor(100*$number)/100;
}


?>