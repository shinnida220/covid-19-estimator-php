<?php
require __DIR__ . '/src/estimator.php';
require __DIR__ . '/vendor/autoload.php';
use Spatie\ArrayToXml\ArrayToXml;

// Json decimal precision hack.. - https://stackoverflow.com/a/41827056/380138
ini_set('serialize_precision','-1');

$eta = -hrtime(true);

// Remove trailing slashe
// $uri = rtrim($_SERVER['REQUEST_URI'], '/');
$uri = $_SERVER['REQUEST_URI'];
$responseCode = 200;

// $responseCode = (in_array($uri, [
// 	'/xml', '/logs', '/slog', 'json', '/',
// 	'/api/v1/on-covid-19/xml',
// 	'/api/v1/on-covid-19/logs',
// 	'/api/v1/on-covid-19',
// 	'/api/v1/on-covid-19/json',
// 	'/api/v1/on-covid-19/slog'

// ])) ? 200 : 404;

$data = ['status' => 'NOT FOUND 404', 'code' => 404];
if ($_SERVER['REQUEST_METHOD'] == "POST" &&  
		( endsWith($uri, "json") || endsWith($uri, "xml") || endsWith($uri, "/") || $uri == "/api/v1/on-covid-19" ) 
	) {

	// Read the input stream
	$body = file_get_contents("php://input");
	// Decode the JSON object
	$object = json_decode($body, true);
	// Save so we can see..
	saveRqData($object);
	// Send the object for processing...
	$data = covid19ImpactEstimator($object);
}


$eta += hrtime(true);
$eta = floor($eta/1e+6);

// Log the request
logOperation($responseCode);
// Set headers and show response..
setHeadersAndShowResponse($data);

function saveRqData($payload = []){
	file_put_contents("r.file", "\r\n========= NEW RQ =======\r\n", FILE_APPEND | LOCK_EX);
	file_put_contents("r.file", print_r($payload,true), FILE_APPEND | LOCK_EX);
	file_put_contents("r.file", "\r\n========= END RQ =======", FILE_APPEND | LOCK_EX);
}

function logOperation(){
	global $eta;
	global $responseCode;

	$eta = (strlen($eta) < 2) ? "0{$eta}" : $eta;
	$requestString = $_SERVER['REQUEST_METHOD'] ."\t" . $_SERVER['REQUEST_URI'] ."\t". $responseCode. "\t".$eta."ms\n";
	file_put_contents("requests.file", $requestString, FILE_APPEND | LOCK_EX);
}

function setHeadersAndShowResponse($data = []){
	global $responseCode;
	global $uri;
	// Remove trailing slashe
	// $uri = rtrim($_SERVER['REQUEST_URI'], '/');
	$data['uri'] = $uri;
	$data['responseCode'] = $responseCode;

	// Proceed to set the necessary headers..
	if ( endsWith($uri, "xml") ){
		http_response_code($responseCode);
		header ("Content-Type: application/xml");
		echo ArrayToXml::convert($data, 'root');
	}
	else if (endsWith($uri, "logs") ){
		ini_set('default_charset', NULL);
		http_response_code($responseCode);
		header("Content-Type: text/plain");
		echo file_get_contents("requests.file");
	}
	else if (endsWith($uri, "slog")){
		ini_set('default_charset', NULL);
		http_response_code($responseCode);
		header("Content-Type: text/plain");
		echo file_get_contents("r.file");
	}
	else{
		http_response_code($responseCode);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
	}
	// switch($uri){
	// 	case '/xml':
	// 	case '/api/v1/on-covid-19/xml':
	// 		http_response_code($responseCode);
	// 		header ("Content-Type: application/xml");
	// 		echo ArrayToXml::convert($data, 'root');
	// 	break;
	// 	case '/logs':
	// 	case '/api/v1/on-covid-19/logs':
	// 		http_response_code($responseCode);
	// 		header("Content-Type: text/plain;");
	// 		echo file_get_contents("requests.file");
	// 	break;
	// 	case '/slog':
	// 	case '/api/v1/on-covid-19/slog':
	// 		http_response_code($responseCode);
	// 		header("Content-Type: text/plain");
	// 		echo file_get_contents("r.file");
	// 	break;
	// 	case '/json':
	// 	case '/':
	// 	case '/api/v1/on-covid-19':
	// 	case '/api/v1/on-covid-19/json':
	// 	// 	http_response_code($responseCode);
	// 	// 	header('Content-Type: application/json; charset=utf-8');
	// 	// 	echo json_encode($data);
	// 	// break;
	// 	default:
	// 		http_response_code($responseCode);
	// 		header('Content-Type: application/json; charset=utf-8');
	// 		echo json_encode($data);
	// }
}

// https://stackoverflow.com/a/834355/380138
function endsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function debug($array = []){
	print("<pre>".print_r($array,true)."</pre>");
}