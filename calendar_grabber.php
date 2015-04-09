<?php
include_once('./lib/simple_html_dom.php');

function get_newurl($url, $fields) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function getAllDays($userid, $monat, $jahr) {
    // create HTML DOM
    $html = file_get_html('https://campus.hochschule-heidelberg.de/scripts/mgrqispi.dll?APPNAME=CampusNet&PRGNAME=MONTH&ARGUMENTS='.$userid.',-N000298,-A01.'.$monat.'.'.$jahr);
    foreach($html->find('div.tbMonthDay') as $day) {
        // check ob ein datum im kalenderfeld steht
        if ($day->find('div.tbsubhead a')) {
            $item = array();
            $unterrichtseinheit = array();
            if ($day->find('div.appMonth')) {
                $date = trim($day->find('div.tbsubhead a', 0)->outertext);
                preg_match('/(title)=("[^"]*")/i', $date, $datearray);
                foreach ($day->find('div.appMonth a') as $index => $einheit) {
                    $time = trim($einheit->outertext);
                    preg_match('/(title)=("[^"]*")/i', $time, $timearray[$i]);
                    $textVorlesung = substr($timearray[$i][0], 7, strlen($timearray[$i][0])-8);
                    //text an jedem / trennen, format ist uhrzeit / raum / fach und in array speichern
                    $textExploded = explode(" / ", $textVorlesung);
                    // datum hinzufügen
                    $unterrichtseinheit[] = substr($datearray[0], 7, 10);
                    //jedes element im array dem tages array hinzufügen
                    foreach ($textExploded as $index=>$value) {
                        $unterrichtseinheit[$index+1] = $value;
                    }
                    $item[] = $unterrichtseinheit;                
                }
            }
            if(!empty($item))
            {
                $ret[] = $item;
            }
        }
    }
    $html->clear();
    unset($html);
    return $ret;
}   

function convertArrayToString($inputArray) {
    $datum = $inputArray[0];
    $zeit = $inputArray[1];
    $ort = $inputArray[2];
    $name = $inputArray[3];

    $zeitArray = explode(" - ", $zeit);
    $zeitBeginn = $zeitArray[0];
    $zeitEnde = $zeitArray[1];

    $datumArray = explode(".", $datum);
    $datum = $datumArray[2].'-'.$datumArray[1].'-'.$datumArray[0];

    $ortArray = preg_split('[;,]/', $ort);
    $ort = implode('/', $ortArray);

    $result['name'] = $name;
    $result['datum'] = $datum;
    $result['zeitBeginn'] = $zeitBeginn;
    $result['zeitEnde'] = $zeitEnde;
    $result['ort'] = $ort;
    return $result;
}

function inICalArrayUmwandelnGoogle($terminArray) {
    $datum = $terminArray['datum'];
    $formattedStart = $datum."T".$terminArray['zeitBeginn'].":00.000";
    $formattedEnd = $datum."T".$terminArray['zeitEnde'].":00.000";
    $result = array(
        'summary' => $terminArray['name'],
        'datestart' => $formattedStart,
        'dateend' => $formattedEnd,
        'address' => $terminArray['ort'],
        'datum' => $terminArray['datum']
        );
    return $result;
}

function alleTermineDurchgehenInEinzelneTerminArrays($inputArray) {
    $index = 0;
    foreach ($inputArray as $tag) {
        foreach ($tag as $termin) {
            $array = convertArrayToString($termin);
            $termin = inICalArrayUmwandelnGoogle($array);
            $result[$index] = array(
                'summary' => $termin['summary'],
                'datestart' => $termin['datestart'],
                'dateend' => $termin['dateend'],
                'location' => $termin['address']
                );
            $index++;
        }
    }
    return $result;
}