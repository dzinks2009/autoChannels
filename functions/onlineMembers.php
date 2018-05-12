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

				$text = '[center][size=14][b]Lista dostępnych użytkowników z[/b] '.$channel["group_name"].'[/size][size=9]\n\n';
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
							$text .= '[url=client://0/'.$clientInfo['client_unique_identifier'].']'.$clientInfo['client_nickname'].'[/url] jest aktualnie [color=green][b]dostępny[/color]\n';
						}else{
							$countOffline++;
							$text .= '[url=client://0/'.$clientInfo['client_unique_identifier'].']'.$clientInfo['client_nickname'].'[/url] jest aktualnie [color=red][b]niedostępny[/color]\n';
						}
					}
				}
				$text .= '[/size]\n';

				$channelInfo = $ts->channelInfo($channel[$zConfig["onlineTable"]])["data"];
	
				if($countOffline+$countOnline == 0){
					$channelName = str_replace(array("[CH_NUMER]","[ONLINE]", "[TOGETHER]", "[PERCENT]"), array($channel["num"], 0,0,0), $zConfig["channelsToCreate"][$zConfig["idWhereOnline"]]["name"]);
				}else{
					$channelName = str_replace(array("[CH_NUMER]","[ONLINE]", "[TOGETHER]", "[PERCENT]"), array($channel["num"], $countOnline,($countOnline+$countOffline),(($countOnline)/($countOnline+$countOffline)*100)), $zConfig["channelsToCreate"][$zConfig["idWhereOnline"]]["name"]);
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