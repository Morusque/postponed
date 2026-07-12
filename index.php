<html>
	<head>
		<meta property="og:title" content="Postponed" />
		<meta name="description" content="Postponed is a social utility that connects people with friends and others who work, study and live around them. People use Postponed to keep up with friends, post statuses, and learn more about the people they meet." />
		<meta property="og:description" content="Postponed is a social utility that connects people with friends and others who work, study and live around them. People use Postponed to keep up with friends, post statuses, and learn more about the people they meet." />
		<meta name="keywords" content="Postponed, Social network" />
		<meta property="og:image" content="https://www.officialdatabase.org/postponed/thumbnail_326_326_00.png" />
		<meta property="og:url" content="https://officialdatabase.org/postponed" />
		<meta name="author" content="Official database" />
		<meta charset="UTF-8" />
		<meta http-equiv="content-type" content="content=text/html; charset=UTF-8" />
		<title>Postponed</title>
		<link rel="icon"  type="image/x-icon" href="https://www.officialdatabase.org/postponed/thumbnail_326_326_00.png" />
        <style type="text/css">
			body {
				color:000000;
				font-size:10px;
				background-color:e7ebf2;
				padding:0px;
				margin-top:0px;				
			}
            .box {
                border-width:1px;
				margin-left:auto;
				margin-right:auto;
				text-align:center;
				padding:2px;
				font-family:"Arial",sans-serif;
				font-size:10px;
            }
			#logo {
				color:FFFFFF;
				font-size:13px;
			}
			#login {
				color:d8dfea;
			}
			#wholeThing {
				border-width:1px;
				background-color:FFFFFF;
				width:550px;
				margin-left:auto;
				margin-right:auto;
				padding:0px;
				margin-top:0px;
			}
			table, th, td {
				margin-left:auto;
				margin-right:auto;
			}
			table .listTable, th .listTable, td .listTable {
				font-size:10px;
			}	
			#titlebanner {
				height:28px;
				width:550px;
				margin-left:auto;
				margin-right:auto;				
				background-color:9d72cd;
			}
			input {
				padding:1px;
				margin:1px;
				font-size:100%;
				vertical-align: bottom;
			}
			.txtInputA {
				width:100px;
				height:18px;
				border-color:7d52ad;
				border-style:solid;
				border-width:1px;
				background-color:ffffff;
				color:000000;
			}
			.statusInput {
				padding:1px;
				margin:1px;
				width:297px;
				height:50px;
				border-color:808080;
				border-style:solid;
				border-width:1px;
				background-color:ffffff;
				color:000000;
			}			
			.disabledButton, .enabledButton, td .disabledButton, td .enabledButton {
				padding:1px 1px 17px 1px;
				width:auto;
				height:15px;
				border-color:6d52ad;
				border-style:solid;
				border-width:1px;
				background-color:6d52ad;
				color:FFFFFF;
			}
			.disabledButton, td .disabledButton {
				background-color:A0A0A0;
				color:707070;
			}
			.disabledButton:active, td .disabledButton:active {
				background-color:A0A0A0;
				color:707070;
			}			
			.enabledButton, td .enabledButton {
				background-color:6d52ad;
				color:FFFFFF;
			}
			.enabledButton:active, td .enabledButton:active {
				background-color:5d429d;
				color:FFFFFF;
			}
			a {
				color:FFFFFF;
				text-decoration:none;
			}			
		</style>
		<script type="text/javascript" src="jquery-3.6.4.min.js"></script>
		<script>
			var userName = "";
			var password = "";
			var token = "";
			var loggedIn = false;
			var currentPoints = 0;
            $(document).ready(function () {
				var nameCookie = getCookie('userName');
				var passCookie = getCookie('password'); // legacy cookie from the previous version, replaced by the token after one login
				var tokenCookie = getCookie('token');
				if (nameCookie && (passCookie || tokenCookie)) {
					if (nameCookie.length > 0) {
						$("#loginName").val(nameCookie);
						if (passCookie) {
							$("#loginPass").val(passCookie);
							document.getElementById("loginPass").type = 'password';
						}
						if (tokenCookie) token = tokenCookie;
						logIn();
					}
				}
				$("#loginName").focusin(function(){
					$("#loginName").val("");
				});
				$("#loginPass").focusin(function(){
					$("#loginPass").val("");
					document.getElementById("loginPass").type = 'password';
				});
				$("#statusInput").focusin(function(){
					$("#statusInput").html("");
					updateCharCount();
				});
				$("#statusInput").on("input", updateCharCount);
				updateCharCount();
            });
			function setCookie(c_name, value, exdays) {
                var exdate = new Date();
                exdate.setDate(exdate.getDate() + exdays);
                var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
                document.cookie = c_name + "=" + c_value + "; SameSite=Lax";
            }
			function getCookie(c_name) {
                var i, x, y, ARRcookies = document.cookie.split(";");
                for (i = 0; i < ARRcookies.length; i++) {
                    x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
                    y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
                    x = x.replace(/^\s+|\s+$/g, "");
                    if (x == c_name) return unescape(y);
                }
				return null;
            }
			function updateLoginInfos() {
				userName = $("#loginName").val();
				password = $("#loginPass").val();
			}
			function usernameTyped() {
				var currentUserName = $("#loginName").val();
				var currentPassword = $("#loginPass").val();
				$.ajax({
					type: "POST",
					dataType: "json",
					url: "checkLogin.php",
					data: {name:currentUserName,password:currentPassword},
					success: function (result) {
						if (result.found || name.length<0 || name.length>30) {
							$("#logInButton").removeClass("disabledButton");
							$("#logInButton").addClass("enabledButton");
							$("#signUpButton").removeClass("enabledButton");
							$("#signUpButton").addClass("disabledButton");
						} else {
							$("#logInButton").removeClass("enabledButton");
							$("#logInButton").addClass("disabledButton");
							$("#signUpButton").removeClass("disabledButton");
							$("#signUpButton").addClass("enabledButton");
						}
					}
				});
            }
			function signUp() {
				updateLoginInfos();
				$.ajax({
					type: "POST",
					dataType: "html",
					url: "signUp.php",
					data: {name:userName,password:password,token:token},
					success: function (result) {
						$("#statuses").html(result);
						if (result=="registration done") logIn();
					}
				});
				usernameTyped();
			}
			function logIn() {
				if (!loggedIn) {
					updateLoginInfos();
					$.ajax({
						type: "POST",
						dataType: "json",
						url: "checkLogin.php",
						data: {name:userName,password:password,token:token},
						success: function (result) {
							if (result.goodPass) {
								setCookie('userName', userName, 365);
								if (result.token) {
									token = result.token;
									setCookie('token', token, 365);
								}
								setCookie('password', "", -1); // passwords don't live in cookies anymore
								if (result.name) userName = result.name;
								$("#logInButton").val("log out");
								$("#userLogInInfo").html('logged in as ' + userName);
								$("#userLogInInterface").hide();
								$("#logInButton").removeClass("disabledButton");
								$("#logInButton").addClass("enabledButton");
								$("#signUpButton").removeClass("enabledButton");
								$("#signUpButton").addClass("disabledButton");
								$("#pointsSpent").disabled = false;
								currentPoints = result.points;
								$("#currentPointsDisplay").html(currentPoints);
								updatePointsSlider();
								loggedIn=true;
							}
							// the lists are only requested once checkLogin has answered,
							// otherwise they could race with the login and get rejected
							updateUsersList();
						}
					});
				} else {
					$("#loginName").val("user name");
					$("#loginPass").val("password");
					$("#userLogInInfo").html('');
					$("#userLogInInterface").show();
					document.getElementById("loginPass").type = 'text';
					$("#logInButton").val("log in");
					setCookie('userName', "", -1);
					setCookie('password', "", -1);
					setCookie('token', "", -1);
					userName="";
					password="";
					token="";
					loggedIn=false;
					updateUsersList();
				}
			}
			function updateUsersList() {
				$.ajax({
					type: "POST",
					dataType: "html",
					url: "userList.php",
					data: {name:userName,password:password,token:token},
					success: function (result) {
						$("#users").html(result);
					}
				});
				$.ajax({
					type: "POST",
					dataType: "html",
					url: "statuses.php",
					data: {name:userName,password:password,token:token},
					success: function (result) {
						$("#statuses").html(result);
					}
				});				
			}
			function follow(id, action) {
				$.ajax({
					type: "POST",
					dataType: "html",
					url: "follow.php",
					data: {name:userName,password:password,token:token,id:id,action:action},
					success: function (result) {
						updateUsersList();
					}
				});
			}
			var maxStatusLength = 7000;
			function updateCharCount() {
				var len = $("#statusInput").val().length;
				var counter = document.getElementById('charCount');
				counter.innerText = len + " / " + maxStatusLength;
				counter.style.color = (len > maxStatusLength) ? "#a02020" : "#909090";
			}
			function postStatus() {
				var statusUpdate = $("#statusInput").val();
				if (statusUpdate.length > maxStatusLength) {
					$("#postMessage").html("write a shorter status please (" + statusUpdate.length + " / " + maxStatusLength + ")");
					return;
				}
				var pointsSpent = $("#pointsSpent").val();
				$.ajax({
					type: "POST",
					dataType: "html",
					url: "postStatus.php",
					data: {name:userName,password:password,token:token,statusUpdate:statusUpdate,pointsSpent:pointsSpent},
					success: function (result) {
						var data = JSON.parse(result);
						if (data.error) {
							$("#postMessage").html(data.error);
						} else {
							$("#postMessage").html("");
							$("#statusInput").val("");
							updateCharCount();
							updateUsersList();
						}
						if (data.points !== undefined) {
							currentPoints = data.points;
							$("#currentPointsDisplay").html(currentPoints);
							updatePointsSlider();
						}
						$("#statusInput").focus();
					}
				});
			}
			function updatePointsSlider() {
				var maxSpendable = Math.max(1, Math.min(currentPoints, 25));
				$("#pointsSpent").attr('max', maxSpendable);
				if (parseInt($("#pointsSpent").val()) > maxSpendable) $("#pointsSpent").val(maxSpendable);
				$("#pointsDisplay").html($("#pointsSpent").val());
				document.getElementById('displayDate').innerText = 'Your post will be displayed on: ' + calculateDisplayDate();
			}
			function calculateDisplayDate() {
				var pointsSpent = document.getElementById('pointsSpent').value;
				// one point leaves the default delay of a century, each extra point halves it (same rule as functions.php)
				var delay = 100;
				for (var i=1 ; i<pointsSpent ; i++) delay /= 2;
				var dateToDisplay = new Date(Date.now() + delay * 365.25 * 24 * 60 * 60 * 1000);
				return formatDate(dateToDisplay);
			}
			function formatDate(date) {
				var hours = date.getHours();
				var minutes = date.getMinutes();
				var ampm = hours >= 12 ? 'pm' : 'am';
				hours = hours % 12;
				hours = hours ? hours : 12; // the hour '0' should be '12'
				minutes = minutes < 10 ? '0'+minutes : minutes;
				var strTime = hours + ':' + minutes + ' ' + ampm;
				return date.getFullYear() + " / " + (date.getMonth()+1) + " / " + date.getDate() + " / " + "  " + strTime;
			}
	</script>
	</head>
	<body>
		<div id="wholeThing">
			<div id="titlebanner">
				<table>
					<tr>
						<th><div class="box" id="logo"><a href="https://www.officialdatabase.org/Postponed/">Postponed</a></div></th>
						<th>
							<div class="box" id="login">
								<div id="userLogInInfo" style="line-height:22px; display:inline;" >
								</div>
								<div id="userLogInInterface" style="display: inline;" >
									<input class="txtInputA" id="loginName" type="text" name="name" style="width:150px;" value="user name" onkeyup="usernameTyped()" />
									<input class="txtInputA" id="loginPass" type="text" name="password" style="width:100px;" value="password"  onkeyup="usernameTyped()" />
									<input class="disabledButton" id="signUpButton" type="button" value="sign up" onclick="signUp()" />
								</div>
								<input class="disabledButton" id="logInButton" type="button" value="log in" onclick="logIn()" />
							</div>
						</th>
					</tr>
				</table>
			</div>
			<table>
				<tr>
					<th>
						<div class="box"><textarea class="statusInput" id="statusInput" type="text" name="post status" style="resize:none;" >What will be up ?</textarea>
						<br/>
						<span id="charCount" style="color:#909090;"></span>
						<br/>
						<label for="pointsSpent">Spend points : </label>
						<input type="range" id="pointsSpent" name="pointsSpent" min="1" max="25" value="1" style="width:200px;" /> <!--disabled-->
						<span id="pointsDisplay" >1</span> / <span id="currentPointsDisplay" ></span><br/>
						<p id="displayDate"></p>
						<p id="postMessage" style="color:#a02020;"></p>
						<input id="postStatusButton" class="enabledButton" type="button" value="post" onclick="postStatus()" /></div>
					</th>
				</tr>
			</table>
			<table>
				<tr style="" >					
					<th style="vertical-align:text-top;"><div class="box" style="width:330px;word-wrap:break-word;text-align:left;padding:10px;" id="statuses" /></th>
					<th style="vertical-align:text-top;"><div class="box" style="width:170px;word-wrap:break-word;padding:10px;" id="users" /></th>
				</tr>
			</table>
		</div>
		<script>
			document.getElementById('pointsSpent').addEventListener('input', function() {
				document.getElementById('displayDate').innerText = 'Your post will be displayed on: ' + calculateDisplayDate();
				var pointsSpent = this.value;
				document.getElementById('pointsDisplay').innerText = pointsSpent;
			});
			document.getElementById('displayDate').innerText = 'Your post will be displayed on: ' + calculateDisplayDate();
			updatePointsSlider();
		</script>
	</body>
</html>
