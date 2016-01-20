<?php

function getCoursesData() {
	global $courses_collection;
	$cursor = get_data_from_collection($courses_collection);
	$tracking = get_course_identifiers();
	$courses = "";
	print_r($tracking);
	foreach ($cursor as $doc) {
   		if ($doc["slug"]) {
			$id = $doc["slug"];
		} else {
			$id = $doc["id"];
		}
		echo "Looking for match for " . $id . "<br/>\n";
		if ($tracking[$id]) {
			$id = $tracking[$id];
		}
		if ($courses[$id] != "") {
			echo "merging<br/>\n";
			$courses[$id] = array_merge($courses[$id],$doc);
		} else {
			echo "plain output<br/>\n";
			$courses[$id] = $doc;
		}
	}
	exit();
	return $courses;
}

function get_course_identifiers() {
    $courseIdentifiers = get_data_from_collection("courseIdentifiers");
    foreach ($courseIdentifiers as $doc) {
   		$doc = $doc["identifiers"];
   		foreach ($doc as $key => $value) {
   			for($i=0;$i<count($value);$i++) {
	 	  		$tracking[$value[$i]] = $key;
   			}
   		}
   	}
   	return $tracking;
}

function get_course_credits_by_badge($id) {
	$badge["explorer"] = 0;
	$badge["strategist"] = 0;
	$badge["practitioner"] = 0;
	$badge["pioneer"] = 0;
	$course = get_course_by_id($id);
	$los = $course["_learningOutcomes"];
	for ($i=0;$i<count($los);$i++) {
		$lo = $los[$i];
		$badge[$lo["badge"]] += $lo["credits"];
	}
	return $badge;
}

function get_course_by_id($id) {
	$courses = getCoursesData();
	return $courses[$id];
}

function get_data_from_collection($collection) {
   global $connection_url, $db_name;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$cursor = $col->find();
	
	return $cursor;

	$m->close();

	return $doneCount;
   } catch ( MongoConnectionException $e ) {
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
	return false;
   } catch ( MongoException $e ) {
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
	return false;
   } catch ( Exception $e ) {
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
	return false;
   }
}

function archive_empty_profiles() {
   global $connection_url, $db_name, $collection;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$cursor = $col->find();
	
	$doneCount = 0;

	$col2 = $m->selectDB($db_name)->selectCollection("elearning-deleted");
	foreach ($cursor as $doc) {
		$id = $doc["_id"];
		$query = array('_id' => $id);
		if (!$doc["ODI_lastSave"] || !$doc["theme"]) {
	        	$count = $col2->count($query);
	        	if ($count > 0) {
				$newdata = array('$set' => $doc);
				$col2->update($query,$newdata);
			} else {
				$col2->save($doc);
			}
			$col->remove($doc);
			$doneCount++;
		}
	}
		
	$m->close();

	return $doneCount;
   } catch ( MongoConnectionException $e ) {
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
	return false;
   } catch ( MongoException $e ) {
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
	return false;
   } catch ( Exception $e ) {
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
	return false;
   }
}
function load($email) {
   $email = str_replace('.','．',$email);
   global $connection_url, $db_name, $collection;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);
	
	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$query = array('email' => $email);

	$res = $col->find($query);	
	
	$m->close();
	
	foreach ($res as $doc) {
 	   return json_encode($doc);
	}
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
?>
