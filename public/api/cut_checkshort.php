<?php
require_once __DIR__ . '/_util/firstload.php';
require_once __DIR__ . '/_util/_database.php';

$dbUtil 		= new utildbInit;
$dbManip		= new dbManip;
$pdo 			= $dbUtil->pdo();

$datArr = [
	'data' => [
		'checked' => false,
		'free' => null
	],
	'alert' => [],
	'msg' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	array_push($datArr['msg'], 'You are using the correct request method');
	if (!empty($_REQUEST['domain'])) {
		if (
			$_REQUEST['domain'] === 'cuturl.it' ||
			$_REQUEST['domain'] === 'pofuki.me' ||
			$_REQUEST['domain'] === 'top-pub.eu'
		) {
			if (!empty($_REQUEST['short'])){
				array_push($datArr['msg'], 'Custom short code provided');
				if (strlen($_REQUEST['short'])<=32) {
					if (preg_match('/^([a-z0-9_\-]*)$/', $_REQUEST['short'])) {
						if ($stmt = $pdo->prepare('SELECT `id` FROM `links` WHERE `domain` = ? AND `short` = ?;')){
							array_push($datArr['msg'], '-> PDO statement successfully prepared @ SELECT FROM links');
							if ($stmt->execute([$_REQUEST['domain'], $_REQUEST['short']])){
								array_push($datArr['msg'], '-> PDO statement successfully executed @ SELECT FROM links');
								$datArr['data']['checked'] = true;
								if ($stmt->fetchColumn() == 0){
									$datArr['data']['free'] = true;
									array_push($datArr['msg'], '-> Custom short code is free and can be used');
									$datArr['alert'] = ['txt'=>'This custom short code is free and can be used', 'type'=>'success'];
								} else {
									$datArr['data']['free'] = false;
									array_push($datArr['msg'], '/!\ custom short code is already in use!');
									$datArr['alert'] = ['txt'=>'This custom short code is already in use', 'type'=>'info'];
								}
							} else {
								array_push($datArr['msg'], '/!\ PDO statement failed to execute @ SELECT FROM links');
								$datArr['alert'] = ['txt'=>'Error 500 - Internal Server Error ~ statement failed to fetch', 'type'=>'error'];
							}
						} else {
							array_push($datArr['msg'], '/!\ PDO statement failed to prepare @ SELECT FROM links');
							$datArr['alert'] = ['txt'=>'Error 500 - Internal Server Error ~ statement failed to prepare', 'type'=>'error'];
						}
					} else {
						array_push($datArr['msg'], '/!\ Custom short code includes disallowed characters!');
						$datArr['alert'] = ['txt'=>'Please insert a valid ending', 'type'=>'warning'];
					}
				} else {
					array_push($datArr['msg'], '/!\ custom short code can be max 32 characters!');
					$datArr['alert'] = ['txt'=>'Maximum 32 characters - '.strlen($_REQUEST['short']).'/32', 'type'=>'warning'];
				}
			} else {
				array_push($datArr['msg'], '/!\ You must provide the custom short code');
				$datArr['alert'] = ['txt'=>'Error 400 - Bad Request ~ missing custom short code data', 'type'=>'warning'];
			}
		} else {
			array_push($datArr['msg'], '/!\ The provided domain is not whitelisted');
			$datArr['alert'] = ['txt'=>'Error 400 - Bad Request ~ The provided domain is not whitelisted', 'type'=>'warning'];
		}
	} else {
		array_push($datArr['msg'], '/!\ You must provide the domain');
		$datArr['alert'] = ['txt'=>'Error 400 - Bad Request ~ missing domain data', 'type'=>'warning'];
	}
} else {
	array_push($datArr['msg'], '/!\ Use required request method, documented by RESTFul API docs!');
	$datArr['alert'] = ['txt'=>'Error 405 - Method Not Allowed ~ invalid request method is used', 'type'=>'error'];
}


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
Header('Content-type: application/json');
echo json_encode($datArr, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
