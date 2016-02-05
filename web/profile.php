<?php
	$location = "/profile.php";
	include('_includes/header.php');
	include('_includes/functions.php');

$userBadgeCredits["explorer"] = 0;
$userBadgeCredits["strategist"] = 0;
$userBadgeCredits["practitioner"] = 0;
$userBadgeCredits["pioneer"] = 0;

function getProfileData() {
	global $userData;
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
					$user["complete"][] = $courses[$course];
				} else {
					$user["in_progress"][] = $courses[$course];
				}
			}
		}
	}
	return $user;
}

function drawProfile($user) {
	global $userBadgeCredits;
	echo outputUserCredits($userBadgeCredits);
	$complete = $user["complete"];
	$in_progress = $user["in_progress"];
	if (count($complete)>0) {
		echo '<h2 class="profile_h2">Completed courses</h2>';
		outputCourses($complete,"Complete");
	}
	if (count($in_progress)>0) {
		echo '<h2 class="profile_h2">Courses in progress</h2>';
		outputCourses($in_progress,"Progress");
	}
}

function outputCourses($courses,$heading) {
	echo '<table style="width: 100%;">';
        echo '<tr><th width="50%"></th><th style="width:150px;">Credits</th><th width="20%">Type</th><th width="20%">'.$heading.'</th></tr>';
	foreach ($courses as $course) {
	        echo outputCourse($course,$course["progress"]);
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
		return 100;
	}
	$total = strlen($progress["completion"]);
	$sub = substr_count($progress["completion"],0);
	$complete = round(($sub / $total) * 100);
	return $complete;	
}

?>
<?php
	$user = getProfileData();
	drawProfile($user);
	include('_includes/footer.html');

?>
