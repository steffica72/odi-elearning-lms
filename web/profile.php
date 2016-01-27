<?php
	$location = "profile.php";
	include('_includes/header.php');
	include('_includes/functions.php');

function getProfileData($email) {
	$doc = load($userData["email"]);
	$doc = str_replace("．",".",$doc);
	$data = json_decode($doc,true);
	$user = getProfile($data);
	return $user;
}

function getProfile($user) {
	$courses = getCoursesData();
	foreach($user as $key => $data) {
		$key = str_replace("．",".",$key);
		if (strpos($key,"_cmi.suspend_data") !== false) {
			$course = substr($key,0,strpos($key,"_cmi"));
			$progress = $data;
			if ($courses[$course]) {
				$courses[$course]["progress"] = getProgress($courses[$course],$progress);
				if ($courses[$course]["progress"] > 99) {
					$ret["complete"][] = $courses[$course];
				} else {
					$ret["in_progress"][] = $courses[$course];
				}
			}
		}
	}
	return $ret;
}

function drawProfile($user) {
	print_r($user);
	$complete = $user["complete"];
	$in_progress = $user["in_progress"];
	if (count($complete)>0) {
		echo '<h2>Completed courses</h2>';
		outputCourses($complete,"Complete");
	}
	if (count($in_progress)>0) {
		echo '<h2>Courses in progress</h2>';
		outputCourses($in_progress,"Progress");
	}
}

function outputCourses($courses,$heading) {
	echo '<table style="width: 100%;">';
        echo '<tr><th>Course name</th><th style="width:150px;">Credits</th><th>Type</th><th>'.$heading.'</th></tr>';
	foreach ($courses as $course) {
	        echo outputCourse($doc,$course["progress"]);
	}
	echo '</table>';
}

function getModuleBadgeData($course) {
	global $userBadgeCredits;
	$los = $course["_learningOutcomes"];
	for ($i=0;$i<count($los);$i++) {
		$lo = $los[$i];
		$badge[$lo["badge"]] += $lo["credits"];
		$userBadgeCredits[$lo["badge"]] += $lo["credits"];
	}
	return $badge;
	
}

function getProgress($course,$progress) {
	$spoor = json_decode($progress,true);
	$progress = $spoor["spoor"];
	if ($progress["_isAssessmentPassed"] > 0 || $progress["_isCourseComplete"] > 0) {
		$progress["completion"] = str_replace("0","1",$progress["completion"]);
		$badgeData = getModuleBadgeData($course);
	}
	$total = strlen($progress["completion"]);
	$sub = substr_count($progress["completion"],0);
	$complete = round($sub / $total);
	return $complete;	
}

?>
<h1>Your profile</h1>
<?php
	require_once('_includes/functions.php');
	$user = getProfileData($email);
	drawProfile($user);
	include('_includes/footer.html');

?>
