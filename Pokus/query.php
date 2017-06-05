<?php
$q = $_REQUEST["term"];
//echo $q;
if (strpos($q, " ") == TRUE){
    $data = array(
        "_source"=>"HOTEL_NAME",
        "suggest" => array(
        "hotel-suggest" =>array(
        "prefix" => $q,
        "completion" => array(
        "field" => "HOTEL_NAME_SUGGEST",
        "size" => 2500
    )
    )
    )
    );
}
else{
    $data = array(
        "_source"=>"HOTEL_NAME",
        "suggest" => array(
        "hotel-suggest" =>array(
        "prefix" => $q,
        "completion" => array(
        "field" => "HOTEL_NAME_SUGGEST_TOKENS",
        "size" => 2500
    )
    )
    )
    );
}


//echo json_encode($data);
$data_string = json_encode($data);
$curl = curl_init("localhost:9200/new_index/_search?pretty&pretty");
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);                                                                  
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);                                                                      
curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
           ); 
$result = curl_exec($curl);
$delka = strpos($result,'options"');
//$result = strchr($result, "options\" : [");
//echo strlen($result);
$telo = substr($result, $delka);
$pole_tmp = explode('_source" : {', $telo);
$pocet_prvku = count($pole_tmp);
$in = 15;
//echo $pole_tmp[$i];
//cyklus, ktery vymaze mezery pred a za retezci
for ($i = 1; $i < $pocet_prvku; $i++){
    $pole_tmp[$i] = trim($pole_tmp[$i]);}
$par_len = strlen('"HOTEL_NAME" : ');
$name_array = array();
for ($j = 1; $j < $pocet_prvku; $j++){
    $pocatek = strpos($pole_tmp[$j], '"HOTEL_NAME" : ');
    if($j == $pocet_prvku-1){
        $end = strpos($pole_tmp[$j], '}');
    }
    else
        $end = strpos($pole_tmp[$j], ',');
    $name = substr($pole_tmp[$j], $par_len, $end);
    $name = str_replace(['{', ',', '}', '"', ']'], '', $name);
    $name_array[$j] = trim($name);
    /* echo $name;
    //echo PHP_EOL;
    echo $j;
    echo "<br />\n";*/
}
//echo count($name_array);
sort($name_array);
//echo count($name_array);
//pomocny kontrolni vypis pro debug
/*for ($i = 0; $i < $pocet_prvku-1; $i++){
    echo $name_array[$i];
    echo $i;
    echo "<br />\n";
}*/
/*$name_array = array_unique($name_array);
echo "serayeno";
$poceta = count($name_array);
echo $poceta;*/

//print_r(array_unique($name_array, SORT_STRING));
/*vzma6e duplicity y php pole a prevede jej na format JSON*/
$new_ar = json_encode(array_unique($name_array, SORT_STRING));
$new_ar = str_replace(['\n', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '"', ':', '{', '}', '[', ']'],"",$new_ar);
//echo $new_ar;
$new_ara = explode(',', $new_ar);

echo json_encode($new_ar);

?>