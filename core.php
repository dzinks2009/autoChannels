<?php
date_default_timezone_set('Europe/Warsaw');	
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'UTF-8');

echo "..:: Aplikacja stworzona przez: Casual(Lolek) ::..".PHP_EOL;
echo "..:: Wczytywanie konfiguracji ::..".PHP_EOL;

require_once "config/config.php";

echo "..:: Wczytywanie klasy TeamSpeak3 ::..".PHP_EOL;

require_once "include/ts3admin.class.php";

echo "..:: Łączenie z bazą danych: ::..".PHP_EOL;

$success = true;
try {
	$pdo = new PDO("mysql:host=".$config["database"]["dbAddress"].";dbname=".$config["database"]["dbTable"].";charset=utf8", $config["database"]["dbLogin"], $config["database"]["dbPassw"]);
} catch (PDOException $e) {
	$success = false;
	echo "..:: Połączenie z bazą nie powiodło się: ::..".PHP_EOL;
}

if(!$success){
	echo "..:: Error: ".$e->getMessage()." ::..".PHP_EOL;
}

echo "..:: Ładowanie pluginu: checkIfOnChannel ::..".PHP_EOL;
require_once "functions/checkIfOnChannel.php";

echo "..:: Ładowanie pluginu: checkNames::..".PHP_EOL;
require_once "functions/checkNames.php";

echo "..:: Ładowanie pluginu: onlineMembers ::..".PHP_EOL;
require_once "functions/onlineMembers.php";

echo "..:: Ładowanie pluginu: channelGroup ::..".PHP_EOL;
require_once "functions/channelGroup.php";

echo "..:: Łączenie z TeamSpeakiem: ::..".PHP_EOL;

$ts = new ts3admin($config["teamSpeak"]["serverIP"], $config["teamSpeak"]["serverQueryPort"]);		
if($ts->getElement('success', $ts->connect())){ 
	if($ts->getElement('success', $ts->login($config["teamSpeak"]["serverQueryLogin"], $config["teamSpeak"]["serverQueryPassw"]))){				
		if($ts->getElement('success', $ts->selectServer($config["teamSpeak"]["serverPort"]))){
			$ts->setName($config["teamSpeak"]["botName"]);

			while(true){
				sleep($config["teamSpeak"]["interval"]);
				echo PHP_EOL;

				echo "[ > ] Odpalam funkcje: checkIfOnChannel".PHP_EOL;
				new checkIfOnChannel($ts, $config, $pdo);

				echo "[ > ] Odpalam funkcje: checkNames".PHP_EOL;
				new checkNames($ts, $config, $pdo);

				echo "[ > ] Odpalam funkcje: onlineMembers".PHP_EOL;
				new onlineMembers($ts, $config, $pdo);

				echo "[ > ] Odpalam funkcje: channelGroup".PHP_EOL;
				new channelGroup($ts, $config, $pdo);
			}
		}else{
			echo "..:: Wybranie serwera nie powiodło się ::..".PHP_EOL;
			echo "---------------------- Informacje: ----------------------".PHP_EOL;
			showErrors($ts->getElement('errors', $ts->selectServer($config["teamSpeak"]["serverPort"])));
			echo "---------------------------------------------------------".PHP_EOL;
			die();
		}
	}else{
		echo "..:: Zalogowanie z TeamSpeakiem nie powiodło się ::..".PHP_EOL;
		echo "---------------------- Informacje: ----------------------".PHP_EOL;
		showErrors($ts->getElement('errors', $ts->login($config["teamSpeak"]["serverQueryLogin"], $config["teamSpeak"]["serverQueryPassw"])));
		echo "---------------------------------------------------------".PHP_EOL;
		die();
	}
}else{
	echo "..:: Połączenie z TeamSpeakiem nie powiodło się ::..".PHP_EOL;
	echo "---------------------- Informacje: ----------------------".PHP_EOL;
	showErrors($ts->getElement('errors', $ts->connect()));
	echo "---------------------------------------------------------".PHP_EOL;
	die();
}

function showErrors($errors){
	foreach($errors as $error){
		echo $error.PHP_EOL;
	}
}
