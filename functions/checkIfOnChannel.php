<?php
class checkIfOnChannel{
	function __construct($ts, $config, $pdo){
		foreach($config["zones"] as $zone => $zConfig){
			$statement = "CREATE TABLE `".$zone."Channels` (`id` int(11) AUTO_INCREMENT,`channel_num` int(11) NOT NULL,`client_database_id` int(11) NOT NULL,";
			foreach($zConfig["channelsToCreate"] as $namesToDb){
				if(isset($namesToDb["database_name"])){
					$statement .= "`".$namesToDb["database_name"]."` int(11) NOT NULL,";
				}
				if(isset($namesToDb["subChannels"])){
					foreach($namesToDb["subChannels"] as $subChannels){
						if(isset($subChannels["database_name"])){
							$statement .= "`".$subChannels["database_name"]."` int(11) NOT NULL,";
						}
						if(isset($subChannels["subChannels"])){
							foreach($subChannels["subChannels"] as $subChannels1){
								if(isset($subChannels1["database_name"])){
									$statement .= "`".$subChannels1["database_name"]."` int(11) NOT NULL,";
								}
							}
						}
					}
				}
			}
			$statement .= "`group_id` int(11) NOT NULL,`group_name` varchar(128) COLLATE utf8_polish_ci NOT NULL,PRIMARY KEY (id))";
			$pdo->query($statement);

			if($ts->channelInfo($zConfig["getChannel"])["data"]["seconds_empty"] == "-1"){
				foreach($ts->channelClientList($zConfig["getChannel"], "-groups")["data"] as $person){
					$channelsCreatedSoFar = [];
					$emptyChannels = [];
					$dbChannels = [];
					$ownerChannels = [];
					$mainChannel = NULL;

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
							$channels = $ts->channelList("-topic")["data"];
							foreach($channels as $channel){
								if(empty($channel["channel_topic"])) continue;
								if(!strcmp($channel["channel_topic"], "#lastChannel:".$zone."")){
									if(strpos($channel["channel_name"], "#lastChannel:".$zone."")){
										$lastChannel = $channel["cid"];
									}
								}
							}

							if(!isset($lastChannel)){
								$ts->clientPoke($person["clid"], "Aplikacja nie mogła znaleźć ostatniego kanału w strefie: ".$zone.".");
								$ts->clientKick($person["clid"], "channel", "Aplikacja nie mogła znaleść ostatniego kanału w strefie: ".$zone.".");
								break;
							}else{
								$statement = $pdo->prepare("SELECT * FROM `".$zone."Channels` ORDER BY channel_num DESC");
								$statement->execute(array($zone));
								$zoneChannels = $statement->fetch(PDO::FETCH_ASSOC);
								(isset($zoneChannels["id"])) ? $numer = $zoneChannels["channel_num"]+1 : $numer = 1;

								if($zConfig["groupToCopy"] !== 0){
									$groupId = $ts->serverGroupCopy($zConfig["groupToCopy"], 0, str_replace("[NUMER]", $numer, $zConfig["groupName"]), $type = 1);
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

									$firstChannel = $ts->channelCreate(array(
										'channel_flag_permanent' => 1,
										'channel_flag_maxclients_unlimited' => ((isset($chToCreate["open"])) ? 1 : 0),
										'channel_flag_maxfamilyclients_unlimited' => ((isset($chToCreate["open"])) ? 1 : 0),
										'channel_maxclients' => ((isset($chToCreate["open"])) ? -1 : 0),
										'channel_maxfamilyclients' => ((isset($chToCreate["open"])) ? -1 : 0),
										'channel_topic' => (isset($chToCreate["channel_topic"]) ? str_replace("[CH_NUMER]", $numer, $chToCreate["channel_topic"]) : "" ),
										'channel_order' => (($id == 0) ? $lastChannel : end($channelsCreatedSoFar)), 
										'channel_name' => str_replace(array("[CH_NUMER]", "[NUMER]", "[ONLINE]", "[TOGETHER]", "[PERCENT]", "[NAME]"), array($numer, $random, 0, 0, 0, $clanName), $chToCreate["name"])
									));

									if(isset($chToCreate["permissions"]) && is_array($chToCreate["permissions"])){
										$ts->channelAddPerm($firstChannel["data"]["cid"], $chToCreate["permissions"]);
									}

									if(isset($chToCreate["type"]) && $chToCreate["type"] == "main"){
										$mainChannelToMove = $firstChannel["data"]["cid"];
									}
									if(isset($chToCreate["database_name"])){
										$dbChannels[] = $firstChannel["data"]["cid"];
									}
									if(isset($chToCreate["assign_owner"])){
										$ownerChannels[] = $firstChannel["data"]["cid"];
									}
									$channelsCreatedSoFar[] = $firstChannel["data"]["cid"];

									if(isset($chToCreate["subChannels"])){
										foreach($chToCreate["subChannels"] as $subChannels){
											if(isset($subChannels["count"])){
												for($i=1; $i <= $subChannels["count"] ; $i++){
													${"secondChannel".$i} = $ts->channelCreate(array(
														'channel_flag_permanent' => 1,
														'channel_flag_maxclients_unlimited' => ((isset($subChannels["open"])) ? 1 : 0),
														'channel_flag_maxfamilyclients_unlimited' => ((isset($subChannels["open"])) ? 1 : 0),
														'channel_maxclients' => ((isset($subChannels["open"])) ? -1 : 0),
														'channel_maxfamilyclients' => ((isset($subChannels["open"])) ? -1 : 0),
														'channel_topic' => (isset($subChannels["channel_topic"]) ? str_replace("[CH_NUMER]", $numer, $subChannels["channel_topic"]) : "" ),
														'cpid' => $firstChannel["data"]["cid"], 
														'channel_name' => str_replace(array("[CH_NUMER]", "[NUMER]", "[ONLINE]", "[TOGETHER]", "[PERCENT]", "[NAME]"), array($numer, $i, 0, 0, 0, $clanName), $subChannels["name"])
													));
												}
												if(isset($subChannels["permissions"]) && is_array($subChannels["permissions"])){
													$ts->channelAddPerm(${"secondChannel".$i}["data"]["cid"], $subChannels["permissions"]);
												}
											}else{
												$secondChannel = $ts->channelCreate(array(
													'channel_flag_permanent' => 1,
													'channel_flag_permanent' => 1,
													'channel_flag_maxclients_unlimited' => ((isset($subChannels["open"])) ? 1 : 0),
													'channel_flag_maxfamilyclients_unlimited' => ((isset($subChannels["open"])) ? 1 : 0),
													'channel_maxclients' => ((isset($subChannels["open"])) ? -1 : 0),
													'channel_maxfamilyclients' => ((isset($subChannels["open"])) ? -1 : 0),
													'channel_topic' => (isset($subChannels["channel_topic"]) ? str_replace("[CH_NUMER]", $numer, $subChannels["channel_topic"]) : "" ),
													'cpid' => $firstChannel["data"]["cid"], 
													'channel_name' => str_replace(array("[CH_NUMER]", "[ONLINE]", "[TOGETHER]", "[PERCENT]", "[NAME]"), array($numer, 0, 0, 0, $clanName), $subChannels["name"])
												));
												if(isset($subChannels["permissions"]) && is_array($subChannels["permissions"])){
													$ts->channelAddPerm($secondChannel["data"]["cid"], $subChannels["permissions"]);
												}
												if(isset($subChannels["database_name"])){
													$dbChannels[] = $secondChannel["data"]["cid"];
												}
												if(isset($subChannels["type"]) && $subChannels["type"] == "main"){
													$mainChannelToMove = $secondChannel["data"]["cid"];
												}
											}

											if(isset($subChannels["subChannels"])){
												foreach($subChannels["subChannels"] as $subChannels1){
													if(isset($secondChannel1)){
														for($i=1; $i <= $subChannels["count"]; $i++){
															${"thirdChannel".$i} = $ts->channelCreate(array(
																'channel_flag_permanent' => 1,
																'channel_flag_maxclients_unlimited' => ((isset($subChannels1["open"])) ? 1 : 0),
																'channel_flag_maxfamilyclients_unlimited' => ((isset($subChannels1["open"])) ? 1 : 0),
																'channel_maxclients' => ((isset($subChannels1["open"])) ? -1 : 0),
																'channel_maxfamilyclients' => ((isset($subChannels1["open"])) ? -1 : 0),
																'channel_topic' => (isset($subChannels1["channel_topic"]) ? str_replace("[CH_NUMER]", $numer, $subChannels1["channel_topic"]) : "" ),
																'cpid' => ${"secondChannel".$i}["data"]["cid"], 
																'channel_name' => str_replace(array("[CH_NUMER]", "[NUMER]", "[ONLINE]", "[TOGETHER]", "[PERCENT]", "[NAME]"), array($numer, $i, 0, 0, 0, $clanName), $subChannels1["name"])
															));
															if(isset($subChannels1["permissions"]) && is_array($subChannels1["permissions"])){
																$ts->channelAddPerm(${"thirdChannel".$i}["data"]["cid"], $subChannels1["permissions"]);
															}
														}
													}else{
														if(isset($subChannels1["count"])){
															for($i=1; $i <= $subChannels1["count"]; $i++){
																${"thirdChannel".$i} = $ts->channelCreate(array(
																	'channel_flag_permanent' => 1,
																	'channel_flag_maxclients_unlimited' => ((isset($subChannels1["open"])) ? 1 : 0),
																	'channel_flag_maxfamilyclients_unlimited' => ((isset($subChannels1["open"])) ? 1 : 0),
																	'channel_maxclients' => ((isset($subChannels1["open"])) ? -1 : 0),
																	'channel_maxfamilyclients' => ((isset($subChannels1["open"])) ? -1 : 0),
																	'channel_topic' => (isset($subChannels1["channel_topic"]) ? str_replace("[CH_NUMER]", $numer, $subChannels1["channel_topic"]) : "" ),
																	'cpid' => $secondChannel["data"]["cid"], 
																	'channel_name' => str_replace(array("[CH_NUMER]", "[ONLINE]", "[TOGETHER]", "[PERCENT]", "[NAME]", "[NUMER]"), array($numer, 0, 0, 0, $clanName, $i), $subChannels1["name"])
																));
																if(isset($subChannels1["permissions"]) && is_array($subChannels1["permissions"])){
																	$ts->channelAddPerm(${"thirdChannel".$i}["data"]["cid"], $subChannels1["permissions"]);
																}
															}
														}else{
															$thirdChannel = $ts->channelCreate(array(
																'channel_flag_permanent' => 1,
																'channel_flag_permanent' => 1,
																'channel_flag_maxclients_unlimited' => ((isset($subChannels1["open"])) ? 1 : 0),
																'channel_flag_maxfamilyclients_unlimited' => ((isset($subChannels1["open"])) ? 1 : 0),
																'channel_maxclients' => ((isset($subChannels1["open"])) ? -1 : 0),
																'channel_maxfamilyclients' => ((isset($subChannels1["open"])) ? -1 : 0),
																'channel_topic' => (isset($subChannels1["channel_topic"]) ? str_replace("[CH_NUMER]", $numer, $subChannels1["channel_topic"]) : "" ),
																'cpid' => $secondChannel["data"]["cid"], 
																'channel_name' => str_replace(array("[CH_NUMER]", "[ONLINE]", "[TOGETHER]", "[PERCENT]", "[NAME]"), array($numer, 0, 0, 0, $clanName), $subChannels1["name"])
															));
															if(isset($subChannels1["permissions"]) && is_array($subChannels1["permissions"])){
																$ts->channelAddPerm($thirdChannel["data"]["cid"], $subChannels1["permissions"]);
															}
															if(isset($subChannels1["database_name"])){
																$dbChannels[] = $thirdChannel["data"]["cid"];
															}
															if(isset($subChannels1["type"]) && $subChannels1["type"] == "main"){
																$mainChannelToMove = $thirdChannel["data"]["cid"];
															}
														}
													}
												}
											}
										}
									}
								}
	
								$statement = "INSERT INTO ".$zone."Channels (`client_database_id`, `channel_num`,";
								foreach($zConfig["channelsToCreate"] as $namesToDb){
									if(isset($namesToDb["database_name"])){
										$statement .= "`".$namesToDb["database_name"]."`,";
									}
									if(isset($namesToDb["subChannels"])){
										foreach($namesToDb["subChannels"] as $subChannels){
											if(isset($subChannels["database_name"])){
												$statement .= "`".$subChannels["database_name"]."`,";
											}
											if(isset($subChannels["subChannels"])){
												foreach($subChannels["subChannels"] as $subChannels1){
													if(isset($subChannels1["database_name"])){
														$statement .= "`".$subChannels1["database_name"]."`,";
													}
												}
											}
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

								foreach($ownerChannels as $channelsToGiveRank){
                       							$ts->setClientChannelGroup($zConfig["hcaGroup"], $channelsToGiveRank, $person["client_database_id"]); 
								}
							}
						}
						$ts->channelEdit($lastChannel, ['channel_topic' => '']);
					}else{
						$ts->clientKick($person['clid'], 'channel', "Juz posiadasz kanal.");
						$ts->clientPoke($person['clid'], "Witaj [color=red][b]".$person["client_nickname"]."[/color], juz posiadasz kanal.");
					}
					unset($channelsCreatedSoFar, $emptyChannels, $dbChannels, $ownerChannels, $channelsOfPerson);
				}
			}
		}
	}
}