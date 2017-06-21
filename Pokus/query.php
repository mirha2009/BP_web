<?php
/*
PHP soubor, který komunikuje s ES a vrací pole s názvy hotelů pro našeptávač
*/


/*
Předání vstupního parametru
*/
$q = $_REQUEST["term"];

/*
Pokud obsahuje parametr mezeru, musím zvolit pro našeptávač jiné zdrojové pole ES, kvůli obsaženým datům
Sestavení dotazu pro ES
*/
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


/*
Dotaz musí být ve formátu JSON, proto je použita funkce json_encode()
*/
$data_string = json_encode($data);
/*
Nastavení paramterů komunikace s ES skrze libcurl:
    zde lze změnit adresu serveru ES a název indexu
*/
$curl = curl_init("localhost:9200/new_index/_search?pretty&pretty");
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);                                                                  
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);                                                                      
curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
           ); 
/*
Odpověď ES se uloží do pole result
*/
$result = curl_exec($curl);
/*
Odpověď obsahuje nadbytečné informace, které se odstraní funkcí substr(). Tělo odpovědi je za řetězcem "options"
*/
$delka = strpos($result,'options"');
$telo = substr($result, $delka);
/*
Jednotlivé hotely jsou odděleny "{". Rozdělím tedy tělo odpovědi do pole přes funkci explode()
*/
$pole_tmp = explode('_source" : {', $telo);
$pocet_prvku = count($pole_tmp);
/*
Cyklus, který vymaže mezery před a za řetězci
*/
for ($i = 1; $i < $pocet_prvku; $i++){
    $pole_tmp[$i] = trim($pole_tmp[$i]);
}
$par_len = strlen('"HOTEL_NAME" : ');
$name_array = array();
/*
Cyklus, který uloží do pole name_array pouze přesné názvy hotelů. Z řetězce vezme pouze část mezi "HOTEL_NAME :" a ",".
Pokud se jedná o poslední prvek pole_tmp, tak je se znak "," nahradí "}".
*/
for ($j = 1; $j < $pocet_prvku; $j++){
    $pocatek = strpos($pole_tmp[$j], '"HOTEL_NAME" : ');
    if($j == $pocet_prvku - 1){
        $end = strpos($pole_tmp[$j], '}');
    }
    else
        $end = strpos($pole_tmp[$j], ',');
    $name = substr($pole_tmp[$j], $par_len, $end);
    $name = str_replace(['{', ',', '}', '"', ']'], '', $name);
    $name_array[$j] = trim($name);
}
/*
Následně se pole s názvy hotelů seřadí. Toto pole stále obsahuje duplicity.
*/
sort($name_array);

/*
V seřazeném poli  vymažeme duplicity skrze funkci array_unique() a uložíme toto pole do pole new_ar již ve formátu JSON
*/
$new_ar = json_encode(array_unique($name_array, SORT_STRING));
/*
Funkce array_unique() přiřadí klíče k jednotlivým prvkům. Tyto klíče odstraníme pomocí str_replace()
*/
$new_ar = str_replace(['\n', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '"', ':', '{', '}', '[', ']'],"",$new_ar);

/*
Výstupem tohoto souboru je seřazené pole s unikátními názvy hotelů, které je ve formátu JSON
*/
echo json_encode($new_ar);
?>