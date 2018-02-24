<?php

$cfg = require_once 'cfg.php';

require_once 'model.php';
require_once 'template.php';

$templ = new template();


$db = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name']);

$user_id = 1;

// main page
if (empty($_GET['r'])) {
	$templ->top();
	$templ->index();
	$templ->bottom();
	exit();
}

// get prize 
if ($_GET['r'] == 'getRandom') {

	$model = new model($db, $cfg);

	$prize = $model->get_random();
	//$model->add_prize($prize);
	
// 	if ($prize['type'] == 'points') {
// 		$model->do_points_credit($prize['prize_id'], $user_id);
// 	}
	
	$templ->top();
	$templ->result($prize);
	$templ->bottom();
	exit();
}

// convert (money)
if ($_GET['r'] == 'convert') {

	$prize_id = (int)$_GET['id'];
	$model = new model($db, $cfg);
	
	if ($prize = $model->get_prize($prize_id)) {
		if ($prize['status'] == 0 && $prize['type'] == 'money') {
			$points = $model->do_convert($prize_id, $user_id);
			if ($points !== false) {
				$templ->top();
				$templ->complete_convert($points);
				$templ->bottom();
			} else {
				echo 'error :(';
			}
		} else {
			echo 'already paid or converted';
		}
	} else {
		echo 'not found';
	}
	exit();
}

// get prize
if ($_GET['r'] == 'get') {

	$prize_id = (int)$_GET['id'];
	$model = new model($db, $cfg);

	if ($prize = $model->get_prize($prize_id)) {
		if ($prize['status'] == 0) {
			if ($model->do_get($prize_id, $user_id)) {
				$templ->top();
				$templ->complete($prize);
				$templ->bottom();
			} else {
				echo 'error :(';
			}
		} else {
			echo 'already got or refused';
		}
	} else {
		echo 'not found';
	}
	exit();
}

// refuse
if ($_GET['r'] == 'refuse') {

	$prize_id = (int)$_GET['id'];
	$model = new model($db, $cfg);

	if ($prize = $model->get_prize($prize_id)) {
		if ($prize['status'] == 0) {
			if ($model->do_refuse($prize_id)) {
				$templ->top();
				$templ->complete_refuse();
				$templ->bottom();
			} else {
				echo 'error :(';
			}
		} else {
			echo 'already got or refused';
		}
	} else {
		echo 'not found';
	}
	exit();
}

if ($_GET['r'] == 'getListPrizes') {
	// debug
	$model = new model($db, $cfg);
	$arr = $model->getListPrizes();
	echo '<pre>'; print_r($arr);
}
