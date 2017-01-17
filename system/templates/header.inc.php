<html>
	<head>
		<title><?=$view->config->appname?></title>
	        <link rel="stylesheet" href="/css/load_all_css.php" type="text/css" />
        	<script type="text/javascript" src="/js/load_all_js.php"></script>
		<meta http-equiv="cache-control" content="max-age=0" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="expires" content="0" />
		<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
		<meta http-equiv="pragma" content="no-cache" />

	</head>
	<body>
	<div id="LtBox" style="display: none;"><div id="btn_Close"><img src="/images/window-close-4.png" /></div><div id="LB_Content"></div></div>
	<div id="LB_Overlay" style="display: none;"></div>
    <div class="leftMenu">
    	<?php $HomeLinkClass= ($view->activeSection===FALSE) ? "leftNavLinkActive" : "leftNavLink";?>
        <div class="<?=$HomeLinkClass?>"><a class="<?=$HomeLinkClass?>" href="<?=$view->config->URL?>">Home</a></div>
        <?php
		if(isset($GLOBALS['leftNav'])) {
	        	if(is_array($GLOBALS['leftNav'])) {
        			ksort($GLOBALS['leftNav']);
        			foreach($GLOBALS['leftNav'] as $link) {
        				print $link . "\n";
					}
        		}
			
        	}

        ?>
        
    </div>
    <div class="mainContent">
    <h1><?=$view->config->appname?></h1>
	<?php
		if(API::hasErrors()) {
			echo "<div id=\"errors\">\n";
			echo "<div id=\"errorImg\"><img src=\"/images/dialog-error-3.png\" sytle=\"float: left;\" /></div>\n";
			echo "<div id=\"errorMsgs\">";
			foreach($_SESSION['error'] as $key => $error) {
				print $error . "<br />\n";
				unset($_SESSION['error'][$key]);
			}
			echo "</div>\n";
			echo "</div>\n";
		}
	?>
	<?php
		if(API::hasMsgs()) {
			echo "<div id=\"messages\">\n";
			echo "<div id=\"msgImg\"><img src=\"/images/dialog-information-3.png\" sytle=\"float: left;\" /></div>\n";
			echo "<div id=\"Msgs\">";
			foreach($_SESSION['msgs'] as $key => $u_msg) {
				print $u_msg . "<br />\n";
				unset($_SESSION['msgs'][$key]);
			}
			echo "</div>\n";
			echo "</div>\n";
		}
	?>
	<div id="dynContent">
