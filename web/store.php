<?php
header("Access-Control-Allow-Origin: *");

function store($data) {
   global $connection_url, $dbuser, $dbpass, $collection;
   try {
	 // create the mongo connection object
	$m = new MongoClient("mongodb://".$connection_url,array("username" => $dbuser, "password" => $dbpass));

	// extract the DB name from the connection path
	$url = parse_url($connection_url);
	$db_name = preg_replace('/\/(.*)/', '$1', $url['path']);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);

	$data = array('x' => 1);

	$col->save($data);

	syslog(LOG_ERR,"Inserted data?");

	$m->close();

	return true;
   } catch ( MongoConnectionException $e ) {
//	return false;
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
//	return false;
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}


$data = $_POST["data"]; //Fetching all posts
$json = json_decode($data,true);
$json = str_replace(".","\uff0e",$json);
$two = json_encode($json);
$data = json_decode($two);
store($data);

?>
