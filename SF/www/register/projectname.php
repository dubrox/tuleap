<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    // Initial db and session library, opens session
session_require(array('isloggedin'=>'1'));
require "account.php";

// push received vars
if ($insert_purpose && $form_purpose) { 

	srand((double)microtime()*1000000);
	$random_num=rand(0,1000000);

	// make group entry
	$result = db_query("INSERT INTO groups (group_name,is_public,unix_group_name,http_domain,homepage,status,"
		. "unix_box,cvs_box,license,register_purpose,required_software,patents_ips,other_comments,register_time,license_other,rand_hash) VALUES ("
		. "'__$random_num',"
		. "1," // public
		. "'__$random_num',"
		. "'__$random_num',"
		. "'__$random_num',"
		. "'I'," // status
		. "'shell1'," // unix_box
		. "'cvs1'," // cvs_box
		. "'__$random_num',"
		. "'".htmlspecialchars($form_purpose)."',"
		. "'".htmlspecialchars($form_required_sw)."',"
		. "'".htmlspecialchars($form_patents)."',"
		. "'".htmlspecialchars($form_comments)."',"
		. time() . ","
		. "'__$random_num','__".md5($random_num)."')");

	if (!$result) {
		exit_error('ERROR','INSERT QUERY FAILED. Please notify '.$GLOBALS['sys_email_admin']);
	} else {
		$group_id=db_insertid($result);
	}

} else {
	exit_error('Error','Missing Information. <B>PLEASE</B> fill in all required information.');
}

$HTML->header(array('title'=>'Project Name'));

util_get_content('register/projectname');

$HTML->footer(array());

?>

