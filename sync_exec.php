<?php
error_reporting(E_ALL);
require_once 'vendor/autoload.php';
include_once 'calendar_grabber.php';
session_start();

$UserName = $_POST["name"];
$UserPassword = $_POST["password"];
$calendarID = $_POST["calendar-id"];

$url = "https://campus.hochschule-Heidelberg.de/scripts/mgrqispi.dll";
$poststring = "usrname=".$UserName."&pass=".$UserPassword."&APPNAME=CampusNet&PRGNAME=LOGINCHECK&ARGUMENTS=clino%2Cusrname%2Cpass%2Cmenuno%2Cmenu_type%2Cbrowser%2Cplatform&clino=000000000000001&menuno=000299&menu_type=classic&browser=&platform=";
$monate = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");

$New_URL = get_newurl($url, $poststring);
$userid = substr($New_URL, strpos($New_URL, "&ARGUMENTS=") + 11, 17);

$client = new Google_Client();
$client->setAuthConfigFile('../key.json');
$client->addScope('https://www.googleapis.com/auth/calendar');

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
	$client->setAccessToken($_SESSION['access_token']);
	$service = new Google_Service_Calendar($client);
	$events = $service->events->listEvents($calendarID);

	//Termine löschen
	$deleteCounter = 0;
	while(true) {
	  foreach ($events->getItems() as $event) {
	  	$output = $event->getId();
	  	$service->events->delete($calendarID, $output);
	  	$deleteCounter++;
	  }
	  $pageToken = $events->getNextPageToken();
	  if ($pageToken) {
	    $optParams = array('pageToken' => $pageToken);
	    $events = $service->events->listEvents($calendarID, $optParams);
	  } else {
	    break;
	  }
	}
	//Termine grabben
	$alleTermineGesamt = array();
	foreach ($monate as $m) {
		$jahr = "2015";
		$New_URL = get_newurl($url, $poststring);
		$userid = substr($New_URL, strpos($New_URL, "&ARGUMENTS=") + 11, 17);
		$ret = getAllDays($userid, $m, $jahr);
		$alleTermineEinzeln = alleTermineDurchgehenInEinzelneTerminArrays($ret);
		if(is_array($alleTermineEinzeln)) {
			$alleTermineGesamt = array_merge($alleTermineGesamt, $alleTermineEinzeln);
		}
	}
	//Termine erstellen
	$terminCounter = 0;
	foreach ($alleTermineGesamt as $termin) {
		$event = new Google_Service_Calendar_Event();
		$event->setSummary($termin['summary']);
		$event->setLocation($termin['location']);
		$start = new Google_Service_Calendar_EventDateTime();
		$start->setTimeZone('Europe/Berlin');
		$start->setDateTime($termin['datestart']);
		$event->setStart($start);
		$end = new Google_Service_Calendar_EventDateTime();
		$end->setTimeZone('Europe/Berlin');
		$end->setDateTime($termin['dateend']);
		$event->setEnd($end);
		$createdEvent = $service->events->insert($calendarID, $event);
		$terminCounter++;
	}
	echo "Done importing ".$terminCounter." events. Deleted ".$deleteCounter." events";
	echo "<br>";

} else {
  $redirect_uri = 'https://dnb4.me/calendar/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}