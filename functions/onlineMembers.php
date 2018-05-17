<?php
class onlineMembers{
	function __construct($ts, $config, $pdo){
		foreach($config["zones"] as $zone => $zConfig){
			$statement = $pdo->prepare("SELECT * FROM `".$zone."Channels`");
			$statement->execute();
			$channels = $statement->fetchAll(PDO::FETCH_ASSOC);
			foreach($channels as $channel){
				$countOnline = 0;
				$countOffline = 0;

				$text = str_replace("[NAME]", $channel["group_name"], $zConfig["online_description"]["top"]);
				$members = $ts->serverGroupClientList($channel["group_id"], true)["data"];
				if(empty($members[0]["cldbid"])){
					$countOnline = 0;
					$countOffline = 0;
					$text .= $zConfig["online_description"]["usermsg"]["noneInGroup"];
				}else{
					foreach($members as $member){
						$clientInfo = $ts->clientDbInfo($member['cldbid'])["data"];
						$client = $ts->clientGetIds($clientInfo["client_unique_identifier"])["data"][0];

						if(isset($client["cluid"])){
							$countOnline++;

							$clientOnlineInfo = $ts->clientInfo($client["clid"])["data"];
							$channelInfo = $ts->channelInfo($clientOnlineInfo["cid"])["data"];

							$memberOnlineTime = time()- ($clientOnlineInfo['client_lastconnected']);

							$dt = new DateTime('@' . $memberOnlineTime, new DateTimeZone('UTC'));
							$time = array('days'    => $dt->format('z'),'hours'   => $dt->format('G'),'minutes' => ltrim($dt->format('i'), 0),'seconds' => $dt->format('s'));

							$timeString = "";
							if($time["days"] != 0){
								if($time["days"] == 1){ $timeString .= "[b]".$time["days"]."[/b] dnia ";
								}else{ $timeString .= "[b]".$time["days"]."[/b] dni "; }
							}
							if($time["hours"] != 0){
								if($time["hours"] == 1){ $timeString .= "[b]".$time["hours"]."[/b] godziny ";
								}else{ $timeString .= "[b]".$time["hours"]."[/b] godzin "; }
							}
							if($time["minutes"] != 0){
								if($time["minutes"] == 1){ $timeString .= "[b]".$time["minutes"]."[/b] minuty";
								}else{ $timeString .= "[b]".$time["minutes"]."[/b] minut"; }
							}
							if($time["minutes"] == 0 && $time["hours"] == 0 && $time["days"] == 0){ $timeString .= "[b]krótkiej chwili[/b]"; }

							$text .= str_replace(array("[CLUID]","[NICK]","[CZAS]","[CID]","[CH_NAME]"), array($clientInfo['client_unique_identifier'], $clientInfo["client_nickname"], $timeString, $clientOnlineInfo["cid"], $channelInfo["channel_name"]), $zConfig["online_description"]["usermsg"]["online"]);
						}else{
							$countOffline++;

							$memberOnlineTime = time()- ($clientInfo['client_lastconnected']);

							$dt = new DateTime('@' . $memberOnlineTime, new DateTimeZone('UTC'));
							$time = array('days'    => $dt->format('z'),'hours'   => $dt->format('G'),'minutes' => ltrim($dt->format('i'), 0),'seconds' => ltrim($dt->format('s'), 0));

							$timeString = "";
							if($time["days"] != 0){
								if($time["days"] == 1){ $timeString .= "[b]".$time["days"]."[/b] dnia ";
								}else{ $timeString .= "[b]".$time["days"]."[/b] dni "; }
							}
							if($time["hours"] != 0){
								if($time["hours"] == 1){ $timeString .= "[b]".$time["hours"]."[/b] godziny ";
								}else{ $timeString .= "[b]".$time["hours"]."[/b] godzin "; }
							}
							if($time["minutes"] != 0){
								if($time["minutes"] == 1){ $timeString .= "[b]".$time["minutes"]."[/b] minuty";
								}else{ $timeString .= "[b]".$time["minutes"]."[/b] minut"; }
							}
							if($time["minutes"] == 0 && $time["hours"] == 0 && $time["days"] == 0){ $timeString .= "[b]krótkiej chwili[/b]"; }

							$text .= str_replace(array("[CLUID]","[NICK]","[CZAS]"), array($clientInfo['client_unique_identifier'], $clientInfo["client_nickname"], $timeString), $zConfig["online_description"]["usermsg"]["offline"]);
						}
					}
				}
				$text .= $zConfig["online_description"]["bottom"];

				foreach($zConfig["channelsToCreate"] as $namesToDb){
					if(isset($namesToDb["type"]) && $namesToDb["type"] == "online"){
						$dbName = $namesToDb["database_name"];
						$channelName = $namesToDb["name"];
						break;
					}
					if(isset($namesToDb["subChannels"])){
						foreach($namesToDb["subChannels"] as $subChannels){
							if(isset($subChannels["type"]) && $subChannels["type"] == "online"){
								$dbName = $subChannels["database_name"];
								$channelName = $subChannels["name"];
								break;
							}	
							if(isset($subChannels["subChannels"])){
								foreach($subChannels["subChannels"] as $subChannels1){
									if(isset($subChannels1["type"]) && $subChannels1["type"] == "online"){
										$dbName = $subChannels1["database_name"];
										$channelName = $subChannels1["name"];
										break;
									}
								}
							}
						}
					}
				}	

				$channelInfo = $ts->channelInfo($channel[$dbName])["data"];
	
				if($countOffline+$countOnline == 0){
					$channelName = str_replace(array("[CH_NUMER]","[ONLINE]", "[TOGETHER]", "[PERCENT]", "[NAME]"), array($channel["channel_num"], 0,0,0, $channel["group_name"]), $channelName);
				}else{
					$channelName = str_replace(array("[CH_NUMER]","[ONLINE]", "[TOGETHER]", "[PERCENT]", "[NAME]"), array($channel["channel_num"], $countOnline,($countOnline+$countOffline),floor((($countOnline)/($countOnline+$countOffline)*100)), $channel["group_name"]), $channelName);
				}
				if($channelInfo["channel_name"] !== $channelName){
					$ts->channelEdit($channel[$dbName],array('channel_name' => $channelName));
				}
				if($channelInfo["channel_description"] !== $text){
					$ts->channelEdit($channel[$dbName],array('channel_description' => $text));
				}

			}
		}
	}
}