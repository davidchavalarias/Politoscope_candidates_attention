<?php

include  'libraries/politoscope_lib.php';
include  'libraries/politoscope_api_lib.php';
// this script creates the all the chord charts of candidates 

$label_col='label';
$query_col='query string';
$delimiter=';';

// query parameters
$interval='day';
$since="2016-07-01";
//$since="2017-02-22";

$time_windows=7;// times windows for the averaging of data

$query_size_limit=500;  
$level='2';
$normalisation=1;
$include='include';
$load_file=false;
$selfmention=1;// if the chord should show the mention of the communities to themselves.

// pour afficher le tableau récapitulatif
$avg_idealisme=array();
$avg_proselytisme=array();
$avg_hostilite=array();
$avg_focus=array();

//$candidates=array('JLMelenchon','benoithamon','EmmanuelMacron','alainjuppe','FrancoisFillon','MLP_officiel'); 
//$candidatesNames=array('Melenchon','Hamon','Macron','Juppé','Fillon','Le Pen'); 
//$candidatesColors=array("#FF0000", "#FA5858", "#F5A9E1", "#2ECCFA", "#2E64FE", "#5F4C0B");

$candidates=array('JLMelenchon','benoithamon','EmmanuelMacron','FrancoisFillon','MLP_officiel'); 
$candidatesNames=array('Melenchon','Hamon','Macron','Fillon','Le Pen'); 
$candidatesColors=array("#FF0000", "#FA5858", "#F5A9E1",  "#2E64FE", "#5F4C0B");

if (file_exists('AllData1.json')&&($load_file)){
    $AllData1=json_decode(file_get_contents('AllData1.json'), true);
}else{
    $AllData1=array();// var to store all the query results before writing the csv
$AllData2=array();// var to store all the query results before writing the csv
$nb=0;

$label_column_number=getColumn('candidatesAttention/data/candidatsMentions.csv',$label_col);
$query_column_number=getColumn('candidatesAttention/data/candidatsMentions.csv',$query_col);

foreach ($candidates as $key => $candidate) {
    $count=0;
    $queries_list= fopen('candidatesAttention/data/candidatsMentions.csv', "r","UTF-8");
    while ((($line= fgetcsv($queries_list, 4096,$delimiter))) !== false) {
        if ($count>0){
            $MentionnedCandidate=$line[$label_column_number];
            $query=$line[$query_column_number];             
            $q=query_limiter($query,$query_size_limit); 
            //$data : array with [0] : array of scores [1] array of periods label ordered in asc periods
            $data=get_network_supporters_hist_data($q,$candidate,'2',$include,$interval,$since,$normalisation);                        
            $AllData1=data2ChartData($AllData1,$data,$selfmention,$candidatesNames,$key,$MentionnedCandidate);        
        }else{
            $count+=1;
        }
    }
    fclose($queries_list);
}
file_put_contents("AllData1.json",json_encode($AllData1));

}







/*foreach ($candidates as $key => $candidate) {
    $count=0;
    $queries_list= fopen('data/candidatsMentions.csv', "r","UTF-8");
    while ((($line= fgetcsv($queries_list, 4096,$delimiter))) !== false) {
        if ($count>0){
            $MentionnedCandidate=$line[$label_column_number];
            $query=$line[$query_column_number];             
            $q=query_limiter($query,$query_size_limit); 
            //$data : array with [0] : array of scores [1] array of periods label ordered in asc periods
            $data=get_network_supporters_hist_data($q,$candidate,'3',$include,$interval,$since,$normalisation);                        
            $AllData2=data2ChartData($AllData2,$data,$selfmention,$candidatesNames,$key,$MentionnedCandidate);        
        }else{
            $count+=1;
        }
    }
    fclose($queries_list);
} */

echo 'extraction finished';
// generation of all the csv for the Chord Chart and the html viz


$D3Chord="
 d3.csv('d3/data/CSV', function (error, data) {
        var mpr = chordMpr(data);

        mpr
          .addValuesToMap('candidate')
          .setFilter(function (row, a, b) {
            return (row.candidate === a.name && row.mentions === b.name)
          })
          .setAccessor(function (recs, a, b) {
            if (!recs[0]) return 0;
            return +recs[0].count;
          });          
        drawChords(mpr.getMatrix(), mpr.getMap());
      });";

$D3ChordCode='';// strong to be replaced in the template
$nbGraph=0;
foreach ($AllData1 as $key => $value) {
    $names=$value['candidates'];
    $mentions=$value['mention'];
    $count=$value['count'];    
    $output= fopen('candidatesAttention/d3/data/'.$key.'_level1.csv', "w","UTF-8");
    fwrite($output,'candidate,mentions,count'.PHP_EOL);    
    foreach ($names as $key1 => $value1) {
        fwrite($output,'"'.$value1.'","'.$mentions[$key1].'",'.trunc($count[$key1]).PHP_EOL);
        $week=$key;

    }
    fclose($output);
    $D3ChordCode=str_replace('CSV',($key.'_level1.csv'), $D3Chord);//.$D3ChordCode; on prend que le plus résent (à recoder)    
    $nbGraph+=1;
    
}
$page=file_get_contents('candidatesAttention/templates/index.html');
$page=str_replace('#ChordCode#',$D3ChordCode, $page);

// Insertion de l'histogramme
/// legendes Xaxis
$histxAxisData='[';
foreach ($AllData1 as $key => $value) {
    $histxAxisData.="'".$key."',";
}
$histxAxisData=substr($histxAxisData, 0,-1).']';
$page=str_replace('#histxAxisData#',$histxAxisData, $page);

// insertion des séries temporelles

// Attention pour les candidats
$DataSeries='';
$attention=array();
$legend='[';
$nbCandidates=count($candidatesNames);// nombre de candidats
foreach ($candidatesNames as $key => $name) {
    $legend.="'".$name."'".',';
    $candidateData="{itemStyle: {normal : {color: '".$candidatesColors[$key]."'}},name:'".$name."',type:'bar',data:[";        
    foreach ($AllData1 as $date => $table) {
        $f=array_keys($table['mention'],$name);// tableau avec les index des éléments mentionnant $name
        $score=0;
        foreach ($f as $key => $s) {// score cumulé d'attention de $name
            if (strcmp($table['mention'][$s],$table['candidates'][$s])!==0 ){// on ne considère pas les auto-mentions dans la calcul de l'attention pour un candidat
                $score+=$table['count'][$s]; 

            }            
        }
        $candidateData.=trunc(($score/($nbCandidates-1))).',';        
        $attention[$name][]=trunc(($score/($nbCandidates-1)));           
    }
    $candidateData=substr($candidateData, 0,-1).']},';
    echo 'Capital de focus de '.$name.' :'.(array_sum(array_slice($attention[$name],-30)) / 30).' --- ';
    $avg_focus[$name]=trunc((array_sum(array_slice($attention[$name],-7)) / 7));//moyenne sur 7 jours
    $DataSeries.=$candidateData;
}

//Processing of mean attention #4ea81b
$AvgAttention="{itemStyle: {normal : {color: '#4ea81b'}},name:'% moyen de tweets d\'une communauté parlant d\'un candidats concurrents',type:'line',yAxisIndex: 1,data:[";// pour stocker l'attention moyenne au cours du temps
foreach ($AllData1 as $date => $table) {
    $sum_attention=0;
    foreach ($AllData1[$date]['count'] as $key => $value) {
        if (strcmp($table['mention'][$key],$table['candidates'][$key])!==0){
            $sum_attention+=$AllData1[$date]['count'][$key];
        }
    }
    $AvgAttention.=trunc(($sum_attention/($nbCandidates))).','; 
}
$AvgAttention=substr($AvgAttention, 0,-1);
$DataSeries.=$AvgAttention.']}';

$legend=substr($legend, 0,-1).']';

$page=str_replace('#legend#',$legend, $page);
$page=str_replace('#Dataseries#',$DataSeries, $page);
$page=str_replace('#semaine#',$week, $page);

///////// Contenu
$DataSeries='';
$pedagogy=array();
$legend='[';
$nbCandidates=count($candidatesNames);// nombre de candidats
foreach ($candidatesNames as $key => $name) {
    $legend.="'".$name."'".',';
    $candidateData="{itemStyle: {normal : {color: '".$candidatesColors[$key]."'}},name:'".$name."',type:'line',data:[";        
    foreach ($AllData1 as $date => $table) {
        //print_r($table);
        $f=array_keys($table['candidates'],$name);// tableau avec les index des éléments mentionnant $name
        $score=0;
        foreach ($f as $key => $s) {// score cumulé d'attention de $name            
                $score+=$table['count'][$s];                                        
        }

        //$candidateData.=trunc(($score)).',';   
        $pedagogy[$name][]=100-trunc(($score));     
    }
    echo 'Idéalisme de '.$name.' :'.(array_sum(array_slice($pedagogy[$name],-30)) / 30).' --- ';
    $convol=array_convolution($pedagogy[$name],$time_windows);
    $avg_idealisme[$name]=trunc($convol[count($convol)-1]);

    foreach ($convol as $pos => $score) {
       $candidateData.=trunc($score).',';
    }

    $candidateData=substr($candidateData, 0,-1).']},';
    $DataSeries.=$candidateData;
}
$legend=substr($legend, 0,-1).']';
$page=str_replace('#legend#',$legend, $page);
$page=str_replace('#autonomie#',$DataSeries, $page);
$page=str_replace('#semaine#',$week, $page);

///////// prosélytisme
$DataSeries='';
$proselytisme=array();
$legend='[';
$nbCandidates=count($candidatesNames);// nombre de candidats
foreach ($candidatesNames as $key => $name) {
    $legend.="'".$name."'".',';
    $candidateData="{itemStyle: {normal : {color: '".$candidatesColors[$key]."'}},name:'".$name."',type:'line',data:[";        
    foreach ($AllData1 as $date => $table) {
        //print_r($table);
        $f=array_keys($table['mention'],$name);// tableau avec les index des éléments mentionnant $name
        $score=0;
        foreach ($f as $key => $s) {// score cumulé d'attention de $name
            if (strcmp($table['mention'][$s],$table['candidates'][$s])==0 ){//
                $score+=$table['count'][$s];    
                
            }            
        }

        //$candidateData.=trunc(($score)).',';   
        $proselytisme[$name][]=trunc(($score));     
    }
    echo 'prosélytisme de '.$name.' :'.(array_sum(array_slice($proselytisme[$name],-30)) / 30).' --- ';
    $convol=array_convolution($proselytisme[$name],$time_windows);
    $avg_proselytisme[$name]=trunc($convol[count($convol)-1]);
    foreach ($convol as $pos => $score) {
       $candidateData.=trunc($score).',';
    }

    $candidateData=substr($candidateData, 0,-1).']},';
    $DataSeries.=$candidateData;
}


// //Processing of mean attention #4ea81b
// $AvgAttention="{itemStyle: {normal : {color: '#4ea81b'}},name:'% moyen de tweets d\'une communauté parlant d\'un candidats concurrents',type:'line',yAxisIndex: 1,data:[";// pour stocker l'attention moyenne au cours du temps
// foreach ($AllData1 as $date => $table) {
//     $sum_attention=0;
//     foreach ($AllData1[$date]['count'] as $key => $value) {
//         if (strcmp($table['mention'][$key],$table['candidates'][$key])==0){
//             $sum_attention+=$AllData1[$date]['count'][$key];
//         }
//     }
//     $AvgAttention.=trunc(($sum_attention/($nbCandidates))).','; 
// }
// $AvgAttention=substr($AvgAttention, 0,-1).']}';
// $DataSeries.=$AvgAttention;

$legend=substr($legend, 0,-1).']';

$page=str_replace('#legend#',$legend, $page);
$page=str_replace('#proselytisme#',$DataSeries, $page);
$page=str_replace('#semaine#',$week, $page);

/////////////////////////////////////////
///////// aggressivité //////////////////
/////////////////////////////////////////

$DataSeries='';
$aggressivite=array();
$legend='[';
$nbCandidates=count($candidatesNames);// nombre de candidats
foreach ($candidatesNames as $key => $name) {
    $legend.="'".$name."'".',';
    $candidateData="{itemStyle: {normal : {color: '".$candidatesColors[$key]."'}},name:'".$name."',type:'line',data:[";        
    foreach ($AllData1 as $date => $table) {
                //print_r($table);
        $f=array_keys($table['candidates'],$name);// tableau avec les index des éléments mentionnant $name
        $score=0;
        foreach ($f as $key => $s) {// score cumulé d'attention de $name
            if (strcmp($table['mention'][$s],$table['candidates'][$s])!==0 ){//
                $score+=$table['count'][$s];                 
            }            
        }

        //$candidateData.=trunc(($score)).',';      
        $aggressivite[$name][]=trunc(($score));  
    }
    echo 'Hostilité de '.$name.' :'.(array_sum(array_slice($aggressivite[$name],-30)) / 30).' --- ';
    $convol=array_convolution($aggressivite[$name],$time_windows);
    $avg_hostilite[$name]=trunc($convol[count($convol)-1]);
    foreach ($convol as $pos => $score) {
       $candidateData.=trunc($score).',';
    }

    $candidateData=substr($candidateData, 0,-1).']},';

    // $AvgPugnacite="{itemStyle: {normal : {color: '#4ea81b'}},name:'% moyen de tweets d\'une communauté parlant d\'un candidats concurrents',type:'line',yAxisIndex: 1,data:[".$AvgAttention."]";
    // $candidateData.=$AvgAttention;
    
    // $candidateData=substr($candidateData, 0,-1).']},';
    $DataSeries.=$candidateData;
}
 //Processing of mean attention #4ea81b
// $AvgAttention="{itemStyle: {normal : {color: '#4ea81b'}},name:'% moyen de tweets d\'une communauté parlant d\'un candidats concurrents',type:'line',yAxisIndex: 1,data:[";// pour stocker l'attention moyenne au cours du temps
// foreach ($AllData1 as $date => $table) {
//     $sum_attention=0;
//     foreach ($AllData1[$date]['count'] as $key => $value) {
//         if (strcmp($table['mention'][$key],$table['candidates'][$key])==0){
//             $sum_attention+=$AllData1[$date]['count'][$key];
//         }
//     }
//     $AvgAttention.=trunc(($sum_attention/($nbCandidates))).','; 
// }
// $AvgAttention=substr($AvgAttention, 0,-1).']}';
// $DataSeries.=$AvgAttention;

$legend=substr($legend, 0,-1).']';

$page=str_replace('#legend#',$legend, $page);
$page=str_replace('#agressivite#',$DataSeries, $page);
$page=str_replace('#semaine#',$week, $page);

/// génération de la tables des indicateurs moyens

$table='
 <table style="width:40%">
  <tr>
    <th style="text-align:center">Communauté</th>
    <th style="text-align:center">Idéalisme</th>
    <th style="text-align:center">Prosélytisme</th>
    <th style="text-align:center">Hostilité</th>
    <th style="text-align:center">Capital de focus</th>
  </tr>';

foreach (array_keys($avg_hostilite) as $key => $value) {
      $table.='<tr ><td style="text-align:center"><b>'.$value.'</b></td><td style="text-align:center">'.$avg_idealisme[$value].'</td><td style="text-align:center">'.$avg_proselytisme[$value].'</td><td style="text-align:center">'.$avg_hostilite[$value].'</td><td style="text-align:center">'.$avg_focus[$value].'</td></tr>';
  }

$table.='</table>';

$page=str_replace('#table#',$table, $page);  

// generation of the viz
$chordFile= fopen('candidatesAttention/canditateStudy_'.$normalisation.'_'.$interval.'-'.$selfmention.'.html', "w","UTF-8");
$index= fopen('candidatesAttention/index.html', "w","UTF-8");
fwrite($chordFile,$page);
fwrite($index,$page);
echo '<a href="'.'candidatesAttention'.'" target="blank">'.$nbGraph.' graphs generated : canditateStudy_'.$normalisation.'_'.$interval.'-'.$selfmention.'.html</a>';
fclose($chordFile);
fclose($index);
?>

