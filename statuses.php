<?php

	include 'functions.php';

	$baseXml = 'users/global.xml';
	$doc = new DOMDocument();
	$doc->Load($baseXml);

	$name = "";
	$typedPassword = "";
	$token = "";
	if (isset($_POST['name'])) $name=$_POST['name'];
	if (isset($_POST['password'])) $typedPassword=$_POST['password'];
	if (isset($_POST['token'])) $token=$_POST['token'];

	$userFound = false;
	$goodPass = false;
	$currentUserId = -1;
	$canonicalName = $name;
	$users = $doc->getElementsByTagName('users')->item(0)->getElementsByTagName('user');
	for ($i=0 ; $i < $users->length && !$userFound ; $i++) {
		$thisUserName = $users->item($i)->getAttribute("name");
		if (sameName($thisUserName, $name)) {
			$userFound = true;
			$canonicalName = $thisUserName;
			$thisHash = $users->item($i)->getAttribute("password");
			$currentUserId = $users->item($i)->getAttribute("id");
			if (authenticate($typedPassword, $token, $thisHash, 'users/'.$currentUserId.'.xml')) {
				$goodPass = true;
			}
		}
	}
	
	if ($userFound) {
		if ($goodPass) {
			$stlPosts = array();
			$stlNames = array();
			$stlDates = array();
			$stlPending = array();
			$stlDisplayDates = array();
			$userXml = 'users/' . $currentUserId . '.xml';
			$userDoc = new DOMDocument();
			$userDoc->Load($userXml);
			// the user's own posts are always listed, the ones not yet visible to everyone are marked as pending
			$theseStatuses = $userDoc->getElementsByTagName('posts')->item(0)->getElementsByTagName('post');
			for ($j=0;$j<$theseStatuses->length;$j++) {
				$thisDisplayDate = displayDateFor($theseStatuses->item($j)->getAttribute("date"), $theseStatuses->item($j)->getAttribute("pointsSpent"));
				$stlPosts[] = $theseStatuses->item($j)->getAttribute("message");
				$stlNames[] = $canonicalName;
				$stlDates[] = $theseStatuses->item($j)->getAttribute("date");
				$stlPending[] = ($thisDisplayDate > time());
				$stlDisplayDates[] = $thisDisplayDate;
			}
			$followsList = $userDoc->getElementsByTagName('follows')->item(0);
			if ($followsList) {
				$follows = $followsList->getElementsByTagName('follow');
				for ($i=0 ; $i < $follows->length ; $i++) {
					$followXml = 'users/' . intval($follows->item($i)->getAttribute("id")) . '.xml';
					if (!file_exists($followXml)) continue;
					$followDoc = new DOMDocument();
					$followDoc->Load($followXml);
					$theseStatuses = $followDoc->getElementsByTagName('posts')->item(0)->getElementsByTagName('post');
					for ($j=0;$j<$theseStatuses->length;$j++) {
						$thisDisplayDate = displayDateFor($theseStatuses->item($j)->getAttribute("date"), $theseStatuses->item($j)->getAttribute("pointsSpent"));
						if ($thisDisplayDate <= time()) {
							$stlPosts[] = $theseStatuses->item($j)->getAttribute("message");
							$stlNames[] = $follows->item($i)->getAttribute("name");
							$stlDates[] = $theseStatuses->item($j)->getAttribute("date");
							$stlPending[] = false;
							$stlDisplayDates[] = $thisDisplayDate;
						}
					}
				}
			}
			$postsToDisplay = array();
			while (count($postsToDisplay)<50 && count($stlPosts)>0) {
				$mostRecent = 0;
				for ($j=0;$j<count($stlPosts);$j++) {
					if ($stlDates[$j] > $stlDates[$mostRecent]) $mostRecent = $j;
				}
				if ($stlPending[$mostRecent]) {
					$appearDate = date("Y / n / j", intval($stlDisplayDates[$mostRecent]));
					$postsToDisplay[] = '<p style="padding:0px;margin:0px;"><div style="color:#c0c0c0;display: inline;">' . $stlNames[$mostRecent] . ' : </div><div style="display: inline;color:#a0a0a0;font-style:italic;">' . $stlPosts[$mostRecent] . '</div><div style="display: inline;color:#c0c0c0;"> (will appear on ' . $appearDate . ')</div></p>';
				} else {
					$postsToDisplay[] = '<p style="padding:0px;margin:0px;"><div style="color:#808080;display: inline;">' . $stlNames[$mostRecent] . ' : </div><div style="display: inline;">' . $stlPosts[$mostRecent] . "</div></p>";
				}
				unset($stlPosts[$mostRecent]);
				$stlPosts = array_values($stlPosts);
				unset($stlNames[$mostRecent]);
				$stlNames = array_values($stlNames);
				unset($stlDates[$mostRecent]);
				$stlDates = array_values($stlDates);
				unset($stlPending[$mostRecent]);
				$stlPending = array_values($stlPending);
				unset($stlDisplayDates[$mostRecent]);
				$stlDisplayDates = array_values($stlDisplayDates);
			}
			for ($i=0;$i<count($postsToDisplay);$i++) {
				echo $postsToDisplay[$i];
			}
		} else {
			echo "wrong password";
		}
	} else {
		echo "sign up first";
	}

?>	