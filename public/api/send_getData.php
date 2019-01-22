<?php
require_once __DIR__ . '/_util/firstload.php';
require_once __DIR__ . '/_util/_database.php';

$dbUtil 		= new utildbInit;
$dbManip		= new dbManip;
$pdo 			= $dbUtil->pdo();

$datArr = [
	'data' => [
		'checked' => false,
		'exists' => null,
		'hasPwd' => null,
		'pwdOkay' => null,
		'hasAds' => null,
		'original' => null
	],
	'success' => false,
	'alert' => [],
	'msg' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	array_push($datArr['msg'], 'You are using the correct request method');
	$_DATA = json_decode(file_get_contents('php://input'), true);
	if (!empty($_DATA['domain'])){
		if (!empty($_DATA['short'])){
			array_push($datArr['msg'], 'Custom short code provided');
			if ($stmt = $pdo->prepare('SELECT `id`, `original`, `password`, `allow_ads`, `instant_redirect` FROM `links` WHERE `domain` = ? AND `short` = ?;')){
				array_push($datArr['msg'], '-> PDO statement successfully prepared @ SELECT FROM links');
				if ($stmt->execute([$_DATA['domain'], $_DATA['short']])){
					array_push($datArr['msg'], '-> PDO statement successfully executed @ SELECT FROM links');
					$datArr['data']['checked'] = true;
					if ($url = $stmt->fetch()){
						array_push($datArr['msg'], '-> Custom short code exists');
						$datArr['alert'] = ['txt'=>'Custom short code exists', 'type'=>'info'];
						$datArr['data']['exists'] = true;
						$datArr['data']['hasAds'] = $url['allow_ads'];
						$datArr['data']['instant'] = $url['instant_redirect'] == 1 ? true : false;

						if (!empty($url['password'])) {
							$datArr['data']['hasPwd'] = true;
							if (!empty($_DATA['pwd'])){
								if (password_verify($_DATA['pwd'], $url['password'])){
									array_push($datArr['msg'], '-> Password is correct');
									$datArr['alert'] = ['txt'=>'Password is correct.', 'type'=>'success'];
									$datArr['data']['original'] = $url['original'];
									$datArr['data']['pwdOkay'] = true;
									$datArr['success'] = true;
								} else {
									array_push($datArr['msg'], '/!\ Password is not correct');
									$datArr['alert'] = ['txt'=>'Password is not correct!', 'type'=>'error'];
									$datArr['data']['pwdOkay'] = false;
								}
							} else {
								array_push($datArr['msg'], '/!\ Password is required');
								$datArr['alert'] = ['txt'=>'Password is required!', 'type'=>'error'];
								$datArr['data']['pwdOkay'] = false;
							}
						} else {
							$datArr['alert'] = ['txt'=>'URL successfully fetched', 'type'=>'success'];
							$datArr['data']['hasPwd'] = false;
							$datArr['data']['original'] = $url['original'];
							$datArr['success'] = true;
						}

					} else {
						$datArr['data']['exists'] = false;
						array_push($datArr['msg'], '/!\ custom short code does not exist!');
						$datArr['alert'] = ['txt'=>'This custom short does not exist. You might have the wrong one, or it has expired', 'type'=>'info'];
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
			array_push($datArr['msg'], '/!\ You must provide the custom short code');
			$datArr['alert'] = ['txt'=>'Error 400 - Bad Request ~ missing custom short code data', 'type'=>'warning'];
		}
	} else {
		array_push($datArr['msg'], '/!\ You must provide the domain');
		$datArr['alert'] = ['txt'=>'Error 400 - Bad Request ~ missing domain data', 'type'=>'warning'];
	}
} else {
	array_push($datArr['msg'], '/!\ Use required request method, documented by RESTFul API docs! Method used: '.$_SERVER['REQUEST_METHOD']);
	$datArr['alert'] = ['txt'=>'Error 405 - Method Not Allowed ~ invalid request method is used', 'type'=>'error'];
}


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
Header('Content-type: application/json');
echo json_encode($datArr, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
