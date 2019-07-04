<?php
require_once __DIR__ . '/_util/firstload.php';
require_once __DIR__ . '/_util/_database.php';

$dbUtil 		= new utildbInit;
$dbManip		= new dbManip;
$pdo 			= $dbUtil->pdo();

$datArr = [
  'check' => [
    'original' => null,
    'domain' => null,
    'short' => null,
    'ads' => null,
    'pwd' => null,
    'instant' => null
  ],
	'data' => [
    'original' => null,
    'domain' => null,
    'short' => null,
    'ads' => null,
    'pwd' => null,
    'instant' => null
	],
	'alert' => [],
	'msg' => [],
  'inserted' => false
];

/*
*
* CHECK IF THE REQUEST IS USING POST METHOD *required data
*
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	array_push($datArr['msg'], '-> You are using the correct request method');
  $_DATA = json_decode(file_get_contents('php://input'), true);
  /*
  *
  * CHECK FOR ORIGINAL URL *required data
  *
  */
  if (
    !empty($_DATA['original']) &&
    preg_match('/^(https?:\/\/)?[a-z0-9\.\-]*\.[a-z0-9]{2,9}(((\/|\?)([a-z0-9\/<>!\?\=\&_\-\#\.\+\%]*)))?$/i', $_DATA['original'])
  ){
    array_push($datArr['msg'], '-> URI is correctly provided');
    $datArr['check']['original'] = true;
    $datArr['data']['original'] = $_DATA['original'];

      /*
      *
      * CHECK FOR SHORT CODE OPTION *optional data
      *
      */
      if (!empty($_DATA['short'])) {
        array_push($datArr['msg'], '-> Short code provided ->'.$_DATA['short']);
        if (
          strlen($_DATA['short']) <= 32 &&
          preg_match('/^([a-z0-9_\-]*)$/', $_DATA['short'])
        ){
          array_push($datArr['msg'], '-> Short code correctly provided');
          $datArr['check']['short'] = true;
          $datArr['data']['short'] = $_DATA['short'];
        } else {
          array_push($datArr['msg'], '/!\ Short code was incorrectly provided');
          $datArr['check']['short'] = false;
        }
      } else {
        array_push($datArr['msg'], '/!\ Short code was not provided');
        $datArr['check']['short'] = true;
        $datArr['data']['short'] = null;
        $isOkay = false;
        do {
          array_push($datArr['msg'], '-> Generating new random short code');
          $datArr['data']['short'] = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(6/strlen($x)) )),1,6);
          if ($stmt = $pdo->prepare('SELECT `id` FROM `links` WHERE `short` = ?;')){
            array_push($datArr['msg'], '-> PDO statement successfully prepared @ SELECT FROM links');
            if ($stmt->execute([$datArr['data']['short']])){
              array_push($datArr['msg'], '-> PDO statement successfully executed @ SELECT FROM links');
              if ($stmt->fetchColumn() == 0){
                $isOkay = true;
                array_push($datArr['msg'], '-> Random short code is free and can be used');
              } else {
                array_push($datArr['msg'], '-> Random short code is already in use!');
              }
            } else {
              array_push($datArr['msg'], '/!\ PDO statement failed to execute @ SELECT FROM links');
            }
          } else {
            array_push($datArr['msg'], '/!\ PDO statement failed to prepare @ SELECT FROM links');
          }
        } while (!$isOkay);
      }

      /*
      *
      * CHECK FOR ALLOW ADS TOGGLE OPTION *required data
      *
      */
      if (
          is_bool($_DATA['ads'])
      ) {
        array_push($datArr['msg'], '-> \'Allow ads\' provided -> '.($_DATA['ads']?'true':'false'));
        $datArr['check']['ads'] = true;
        $datArr['data']['ads'] = $_DATA['ads'] ? 1 : 0;
      } else {
        array_push($datArr['msg'], '/!\ "Allow ads" was not provided or was incorrectly rovided -> using default "true" value');
        $datArr['check']['ads'] = true;
        $datArr['data']['ads'] = 1;
      }

      /*
      *
      * CHECK FOR PASSWORD INPUT *optional data
      *
      */
      if (!empty($_DATA['pwd'])) {
        array_push($datArr['msg'], '-> Password provided -> '.$_DATA['pwd']);
        if (strlen($_DATA['pwd']) <= 125) {
          array_push($datArr['msg'], '-> Password is correctly provided');
          $datArr['check']['pwd'] = true;
          $datArr['data']['pwd'] = $_DATA['pwd'];
        } else {
          array_push($datArr['msg'], '/!\ Password was incorrectly rovided');
          $datArr['check']['pwd'] = false;
        }
      } else {
        array_push($datArr['msg'], '/!\ Password was not provided');
        $datArr['check']['pwd'] = true;
        $datArr['data']['pwd'] = null;
      }

      /*
      *
      * CHECK FOR INSTANT REDIRECT TOGGLE *optional data
      *
      */
      if (!empty($_DATA['instant'])) {
        array_push($datArr['msg'], '-> Instant redirect toggle -> '.$_DATA['instant']);
        if ( is_bool($_DATA['instant']) ) {
          array_push($datArr['msg'], '-> Instant redirect toggle is correctly provided');
          $datArr['check']['instant'] = true;
          $datArr['data']['instant'] = $_DATA['instant'] ? 1 : 0;
        } else {
          array_push($datArr['msg'], '/!\ Instant redirect toggle was incorrectly rovided');
          $datArr['check']['instant'] = false;
        }
      } else {
        array_push($datArr['msg'], '/!\ Instant redirect toggle was not provided');
        $datArr['check']['instant'] = true;
        $datArr['data']['instant'] = 0;
      }

      /*
      *
      * CHECK FOR DOMAIN INPUT *optional data
      *
      */
      if (!empty($_DATA['domain'])) {
          array_push($datArr['msg'], '-> Domain provided -> '.$_DATA['domain']);
          if (
            $_DATA['domain'] === 'cuturl.it' ||
            $_DATA['domain'] === 'pofuki.me' ||
            $_DATA['domain'] === 'top-pub.eu' 
          ) {
            array_push($datArr['msg'], '-> Domain is correctly provided');
            $datArr['check']['domain'] = true;
            $datArr['data']['domain'] = $_DATA['domain'];
          } else {
            array_push($datArr['msg'], '/!\ Domain was incorrectly rovided');
            $datArr['check']['domain'] = false;
          }
      } else {
        array_push($datArr['msg'], '/!\ Domain was not provided');
        $datArr['check']['domain'] = true;
        $datArr['data']['domain'] = 'cuturl.it';
      }

      /*
      *
      * MAIN PROCESS
      *
      */
      if (
        $datArr['check']['pwd'] !== false &&
        $datArr['check']['short'] !== false &&
        $datArr['check']['ads'] !== false
      ) {
        array_push($datArr['msg'], '-> Everything was correctly provided');
        /*
        *
        * CHECK IF CUSTOM URL IS FREE
        *
        */
        if ($stmt = $pdo->prepare('SELECT `id` FROM `links` WHERE `domain` = ? AND `short` = ?;')){
          array_push($datArr['msg'], '-> PDO statement successfully prepared @ SELECT FROM links');
          if ($stmt->execute([$datArr['data']['domain'] , $datArr['data']['short']])){
            array_push($datArr['msg'], '-> PDO statement successfully executed @ SELECT FROM links');
            $datArr['data']['checked'] = true;
            if ($stmt->fetchColumn() == 0){
                array_push($datArr['msg'], '-> Custom short code is free and can be used');

                /*
                *
                * INSERT THE DATA INTO THE DATABASE
                *
                */
                if ($stmt = $pdo->prepare('INSERT INTO `links` (original, domain, short, time_crated, password, allow_ads, instant_redirect) VALUES (?, ?, ?, ?, ?, ?, ?);')){
                  array_push($datArr['msg'], '-> PDO statement successfully prepared @ INSERT INTO links');
                  if ($stmt->execute([
                    $datArr['data']['original'],
                    $datArr['data']['domain'],
                    $datArr['data']['short'],
                    time(),
                    (($datArr['data']['pwd']=='' || $datArr['data']['pwd']==null) ? null : password_hash($datArr['data']['pwd'], PASSWORD_DEFAULT)),
                    $datArr['data']['ads'],
                    $datArr['data']['instant']
                  ])){
                  array_push($datArr['msg'], '-> PDO statement successfully executed @ INSERT INTO links');
                  if ($stmt->rowCount() === 1){
                    $datArr['inserted'] = true;
                    array_push($datArr['msg'], '-> Data has been successfully inserted into the database');
                    $datArr['alert'] = ['txt'=>'Data has been successfully inserted into the database', 'type'=>'success'];
                  } else {
                    $datArr['dinsertedata'] = false;
                    array_push($datArr['msg'], '/!\ Data failed to insert into the database!');
                    $datArr['alert'] = ['txt'=>'Data failed to insert into the database', 'type'=>'error'];
                  }
                } else {
                  array_push($datArr['msg'], '/!\ PDO statement failed to execute @ INSERT INTO links');
                  $datArr['alert'] = ['txt'=>'Error 500 - Internal Server Error ~ statement failed to fetch', 'type'=>'error'];
                }
              } else {
                array_push($datArr['msg'], '/!\ PDO statement failed to prepare @ INSERT INTO links');
                $datArr['alert'] = ['txt'=>'Error 500 - Internal Server Error ~ statement failed to prepare', 'type'=>'error'];
              }
            } else {
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
        array_push($datArr['msg'], '/!\ Some data was not correctly provided');
      }
  } else {
    array_push($datArr['msg'], '/!\ URI was not correctly provided. Insert a valid URI');
    $datArr['alert'] = ['txt'=>'Error 400 - Method Not Allowed ~ URI was not correctly provided. Insert a valid URI', 'type'=>'warning'];
    $datArr['check']['original'] = false;
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
