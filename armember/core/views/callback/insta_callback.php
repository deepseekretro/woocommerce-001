<?php
$insta_code = isset($_REQUEST['code']) ? sanitize_text_field($_REQUEST['code']) : ''; //phpcs:ignore
if ($insta_code !== '') {
	
	echo "<script type='text/javascript' id='authorize'>";
	echo "function arm_insta_token(){";
	echo "window.opener.document.getElementById('arm_insta_user_data').value = '".json_encode($user_insta_data)."';";
	echo "window.close();";
	echo "window.opener.arm_InstaAuthCallBack('".$insta_code."')"; //phpcs:ignore
	echo "}";
	echo "arm_insta_token();";
	echo "</script>";
}
