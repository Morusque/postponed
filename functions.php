<?php

	// creates the data folder and an empty user index on a fresh install
	// (this way the code can be deployed without any users/ folder)
	if (!file_exists('users/global.xml')) {
		if (!is_dir('users')) @mkdir('users', 0775, true);
		if (is_dir('users') && is_writable('users')) {
			file_put_contents('users/global.xml', "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<global>\n\t<users></users>\n</global>\n");
		}
	}

	// compares two user names, a different capitalization counts as the same name
	function sameName($a, $b) {
		if (function_exists('mb_strtolower')) return mb_strtolower($a, 'UTF-8') == mb_strtolower($b, 'UTF-8');
		return strtolower($a) == strtolower($b);
	}

	// checks a typed password against a stored hash
	// old accounts have an unsalted md5 (32 hex chars), newer ones use password_hash
	function checkPassword($typedPassword, $storedHash) {
		if (strlen($storedHash)==32 && ctype_xdigit($storedHash)) return md5($typedPassword)==$storedHash;
		return password_verify($typedPassword, $storedHash);
	}

	// true if the stored hash is a legacy unsalted md5 that should be upgraded
	function isLegacyHash($storedHash) {
		return strlen($storedHash)==32 && ctype_xdigit($storedHash);
	}

	// checks the credentials sent with a request : either the password itself,
	// or the login token that checkLogin.php handed out (so the password never has to live in a cookie)
	function authenticate($typedPassword, $token, $storedHash, $userXmlPath) {
		if ($typedPassword!="" && checkPassword($typedPassword, $storedHash)) return true;
		if ($token!="" && file_exists($userXmlPath)) {
			$tokenDoc = new DOMDocument();
			$tokenDoc->Load($userXmlPath);
			$storedTokenHash = $tokenDoc->getElementsByTagName('data')->item(0)->getAttribute("tokenHash");
			if ($storedTokenHash!="" && hash('sha256', $token) == $storedTokenHash) return true;
		}
		return false;
	}

	// computes the date at which a post becomes visible
	// one point leaves the default delay of a century, each extra point halves it
	// (computed from the writing date so that a rule change applies to older posts too)
	function displayDateFor($dateWritten, $pointsSpent) {
		$pointsSpent = intval($pointsSpent);
		$delay = 100;
		for ($i=1 ; $i<$pointsSpent ; $i++) $delay /= 2;
		return intval($dateWritten) + (int)round($delay * 365.25 * 24 * 60 * 60);
	}

	// saves an xml file atomically (write to a temporary file then rename)
	// so that a concurrent reader can never see a half-written file
	function saveDocAtomic($doc, $path) {
		$tmp = $path . '.' . getmypid() . '.tmp';
		if (@$doc->save($tmp) === false) {
			$doc->save($path);
			return;
		}
		if (!@rename($tmp, $path)) {
			$doc->save($path);
			@unlink($tmp);
		}
	}

	// gives one point per day to the user since his last visit
	// the caller is responsible for saving $userDoc afterwards
	function grantDailyPoints($userDoc) {
		$dataNode = $userDoc->getElementsByTagName('data')->item(0);
		$points = intval($dataNode->getAttribute("points"));
		$lastPointDate = $dataNode->getAttribute("lastPointDate");
		$now = time();
		if ($lastPointDate=="") {
			$dataNode->setAttribute("lastPointDate", $now);
			return $points;
		}
		$daysElapsed = floor(($now - intval($lastPointDate)) / 86400);
		if ($daysElapsed > 0) {
			$points += $daysElapsed;
			$dataNode->setAttribute("points", $points);
			$dataNode->setAttribute("lastPointDate", intval($lastPointDate) + $daysElapsed * 86400);
		}
		return $points;
	}

?>
