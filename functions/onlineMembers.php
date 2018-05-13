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

				$text = '[center][size=14][b]Lista dostępnych użytkowników z[/b] '.$channel["group_name"].'[/center][size=11][list]\n';
				$members = $ts->serverGroupClientList($channel["group_id"], true)["data"];
				if(empty($members[0]["cldbid"])){
					$countOnline = 0;
					$countOffline = 0;
					$text .= '[size=9]Brak użytkowników w tej grupie.[/size]\n';
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

							$text .= '[*][url=client://0/'.$clientInfo['client_unique_identifier'].']'.$clientInfo['client_nickname'].'[/url] jest aktualnie [color=green][b]dostępny[/color] od '.$timeString.' na kanale [b][url=channelID://'.$clientOnlineInfo["cid"].']'.$channelInfo["channel_name"].'[/url][/b].\n\n';
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

							$text .= '[*][url=client://0/'.$clientInfo['client_unique_identifier'].']'.$clientInfo['client_nickname'].'[/url] jest aktualnie [color=red][b]niedostępny[/color] od '.$timeString.'.\n\n';
						}
					}
				}

				$text .= '[/list]\n';

				$channelInfo = $ts->channelInfo($channel[$zConfig["onlineTable"]])["data"];
	
				if($countOffline+$countOnline == 0){
					$channelName = str_replace(array("[CH_NUMER]","[ONLINE]", "[TOGETHER]", "[PERCENT]"), array($channel["num"], 0,0,0), $zConfig["channelsToCreate"][$zConfig["idWhereOnline"]]["name"]);
				}else{
					$channelName = str_replace(array("[CH_NUMER]","[ONLINE]", "[TOGETHER]", "[PERCENT]"), array($channel["num"], $countOnline,($countOnline+$countOffline),floor((($countOnline)/($countOnline+$countOffline)*100))), $zConfig["channelsToCreate"][$zConfig["idWhereOnline"]]["name"]);
				}

				if($channelInfo["channel_name"] !== $channelName){
					$ts->channelEdit($channel[$zConfig["onlineTable"]],array('channel_name' => $channelName));

				}
				if($channelInfo["channel_description"] !== $text){
					$ts->channelEdit($channel[$zConfig["onlineTable"]],array('channel_description' => $text));
				}

			}
		}
	}
}
