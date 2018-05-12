<?php
class checkIfOnChannel{
	function __construct($ts, $config, $pdo){
		if (!file_exists("lastChannel")){
			$fp = fopen("lastChannel","wb");
			fwrite($fp, "1");
			fclose($fp);
		}

		$zonePreviousChannels = [];
		$lines = file("lastChannel");
		foreach ($lines as $line) {
			$explode = explode(";", $line);
			$zonePreviousChannels[$explode[0]] = (int)filter_var($explode[1], FILTER_SANITIZE_NUMBER_INT);
		}

		foreach($config["zones"] as $zone => $zConfig){
			$statement = "CREATE TABLE `".$zone."Channels` (`id` int(11) AUTO_INCREMENT,`num` int(11) NOT NULL,`client_database_id` int(11) NOT NULL,".PHP_EOL;
			foreach($zConfig["channelsToCreate"] as $namesToDb){
				if(isset($namesToDb["dbName"])){
					$statement .= "`".$namesToDb["dbName"]."` int(11) NOT NULL,".PHP_EOL;
					if(isset($namesToDb["subChannels"]["dbName"])){
						$statement .= "`".$namesToDb["subChannels"]["dbName"]."` int(11) NOT NULL,".PHP_EOL;
					}
				}
			}
			$statement .= "`group_id` int(11) NOT NULL,`group_name` varchar(128) COLLATE utf8_polish_ci NOT NULL,PRIMARY KEY (id))";
			$pdo->query($statement);

			if($ts->channelInfo($zConfig["getChannel"])["data"]["seconds_empty"] == "-1"){
				foreach($ts->channelClientList($zConfig["getChannel"], "-groups")["data"] as $person){
					$emptyChannels = [];
					$dbChannels = [];
					$channelsCreatedSoFar = [];
					$assignRankChanels = [];
					$mainChannelToMove = NULL;

					$statement = $pdo->prepare("SELECT * FROM `".$zone."Channels` WHERE client_database_id=?");
					$statement->execute(array($person["client_database_id"]));
					$channelsOfPerson = $statement->fetch(PDO::FETCH_ASSOC);

					if($channelsOfPerson == NULL){
						foreach($ts->channelList("-topic")["data"] as $channel){
							if(strstr($channel['channel_topic'], "#kanal_".$zone."_empty")){
								$emptyChannels[] = $channel;
							}
						}
						if(count($emptyChannels) == 0){
							$statement = $pdo->prepare("SELECT * FROM `".$zone."Channels` ORDER BY num DESC");
							$statement->execute(array($zone));
							$vipChannel = $statement->fetch(PDO::FETCH_ASSOC);
							(isset($vipChannel["id"])) ? $numer = $vipChannel["num"]+1 : $numer = 1;

							if($zConfig["groupToCopy"] !== 0){
								$groupId = $ts->serverGroupCopy(11, 0, str_replace("[NUMER]", $numer, $zConfig["groupName"]), $type = 1);
           							$ts->serverGroupAddClient($groupId["data"]["sgid"], $person['client_database_id']);
							}

							$clanName = str_replace("[NUMER]", $numer, $zConfig["groupName"]);

							foreach($zConfig["channelsToCreate"] as $id => $chToCreate){
								while(true){
									$random = rand(1,5000);
									$channel = $ts->channelFind(str_replace("[NUMER]", $random, $chToCreate["name"]))["data"];
									if(!isset($channel[0])){
										break;
									}
								}

								$channel = $ts->channelCreate(array(
									'channel_flag_semi_permanent' => 0,
									'channel_flag_permanent' => 1,
									'channel_flag_maxclients_unlimited' => 0,
									'channel_flag_maxfamilyclients_unlimited' => 0,
									'channel_flag_maxfamilyclients_inherited' => 0,
									'channel_maxclients' => 0,
									'channel_maxfamilyclients' => 0,
									'channel_topic' => (isset($chToCreate["topic"]) ? str_replace("[CH_NUMER]", $numer, $chToCreate["topic"]) : "" ),
									'channel_order' => (($id == 0) ? (int)$zonePreviousChannels[$zone] : end($channelsCreatedSoFar)), 
									'channel_name' => str_replace(array("[CH_NUMER]", "[NUMER]", "[ONLINE]", "[TOGETHER]", "[PERCENT]", "[NAME]"), array($numer, $random, 0, 0, 0, $clanName), $chToCreate["name"])
								));

								if(isset($chToCreate["dbName"])){
									$dbChannels[] = $channel["data"]["cid"];
								}

								if(isset($chToCreate["subChannels"])){
									if(isset($chToCreate["subChannels"]["howMany"])){
										for($i=1; $i <= $chToCreate["subChannels"]["howMany"] ; $i++){
											$subChannel = $ts->channelCreate(array('channel_flag_semi_permanent' => 0,'channel_flag_permanent' => 1,'channel_flag_maxclients_unlimited' => 0,'channel_flag_maxfamilyclients_unlimited' => 0,'channel_flag_maxfamilyclients_inherited' => 0,	'channel_maxclients' => 0,'channel_maxfamilyclients' => 0,
												'cpid' => $channel["data"]["cid"],'channel_name' => str_replace("[NUMER]", $i, $chToCreate["subChannels"]["name"]),
											));
											if(isset($chToCreate["subChannels"]["channelsToSubChannels"])){
												$ts->channelCreate(array('channel_flag_semi_permanent' => 0,'channel_flag_permanent' => 1,'channel_flag_maxclients_unlimited' => 0,'channel_flag_maxfamilyclients_unlimited' => 0,
													'cpid' => $subChannel["data"]["cid"],'channel_name' => str_replace("[NUMER]", $i, $chToCreate["subChannels"]["subChannelsName"]),
												));
											}
										}
									}else{
										$subChannel = $ts->channelCreate(array('channel_flag_semi_permanent' => 0,'channel_flag_permanent' => 1,'channel_flag_maxclients_unlimited' => 0,'channel_flag_maxfamilyclients_unlimited' => 0,'channel_flag_maxfamilyclients_inherited' => 0,	'channel_maxclients' => 0,'channel_maxfamilyclients' => 0,
											'cpid' => $channel["data"]["cid"],
											'channel_name' => str_replace(array("[CH_NUMER]", "[NUMER]", "[ONLINE]", "[TOGETHER]", "[PERCENT]", "[NAME]"), array($numer, $random, 0, 0, 0, $clanName), $chToCreate["subChannels"]["name"])

										));
										if(isset($chToCreate["subChannels"]["dbName"])){
											$dbChannels[] = $subChannel["data"]["cid"];
										}
									}
								}

								$channelsCreatedSoFar[] = $channel["data"]["cid"];
								if(isset($chToCreate["main_channel"])){
									$mainChannelToMove = $channel["data"]["cid"];
								}
								if(isset($chToCreate["assignHCA"]) && $chToCreate["assignHCA"]){
									$assignRankChanels[] = $channel["data"]["cid"];
								}
							}

							$zonePreviousChannels[$zone] = end($channelsCreatedSoFar);
							$statement = "INSERT INTO ".$zone."Channels (`client_database_id`, `num`,";
							foreach($zConfig["channelsToCreate"] as $namesToDb){
								if(isset($namesToDb["dbName"])){
									$statement .= "`".$namesToDb["dbName"]."`,";
									if(isset($namesToDb["subChannels"]["dbName"])){
										$statement .= "`".$namesToDb["subChannels"]["dbName"]."`,";
									}
								}
							}
							rtrim($statement,',');
							$statement .= "`group_id`,`group_name`) VALUES (";
							$statement .= "".$person["client_database_id"].", ".$numer.",";
							foreach($dbChannels as $toDb){
								$statement .= "".$toDb.",";
							}
							$statement .= "".$groupId["data"]["sgid"].", '".$clanName."');";
							$pdo->query($statement);

							$ts->clientMove($person["clid"], $mainChannelToMove);

							foreach($assignRankChanels as $channelsToGiveRank){
                       						$ts->setClientChannelGroup($zConfig["hcaGroup"], $channelsToGiveRank, $person["client_database_id"]); 
							}
						}else{
							//Jest kanal w strefie ** DO ZROBIENIA **
						}
					}else{
						$ts->clientKick($person['clid'], 'channel', "Juz posiadasz kanal.");
						$ts->clientPoke($person['clid'], "Witaj [color=red][b]".$person["client_nickname"]."[/color], juz posiadasz kanal.");
					}
					unset($channelsCreatedSoFar, $emptyChannels, $dbChannels, $assignRankChanels, $mainChannelToMove);
				}
			}
		}

		$text = "";
		foreach($zonePreviousChannels as $zoneName => $id){
			$text .= $zoneName.";".$id.PHP_EOL;
		}
		file_put_contents("lastChannel", $text);
	}
}
