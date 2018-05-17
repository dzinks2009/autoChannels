<?php
class channelGroup{
	function __construct($ts, $config, $pdo){
		foreach($config["zones"] as $zone => $zConfig){
			$statement = $pdo->prepare("SELECT * FROM `".$zone."Channels`");
			$statement->execute();
			$channels = $statement->fetchAll(PDO::FETCH_ASSOC);
			foreach($channels as $channel){
				foreach($zConfig["channelsToCreate"] as $namesToDb){
					if(isset($namesToDb["type"]) && $namesToDb["type"] == "rank"){
						$dbNameRank = $namesToDb["database_name"];
					}
					if(isset($namesToDb["type"]) && $namesToDb["type"] == "main"){
						$dbNameMain = $namesToDb["database_name"];
					}
					if(isset($namesToDb["subChannels"])){
						foreach($namesToDb["subChannels"] as $subChannels){
							if(isset($subChannels["type"]) && $subChannels["type"] == "rank"){
								$dbNameRank = $subChannels["database_name"];
							}	
							if(isset($subChannels["type"]) && $subChannels["type"] == "main"){
								$dbNameMain = $subChannels["database_name"];
							}	
							if(isset($subChannels["subChannels"])){
								foreach($subChannels["subChannels"] as $subChannels1){
									if(isset($subChannels1["type"]) && $subChannels1["type"] == "rank"){
										$dbNameRank = $subChannels1["database_name"];
									}
									if(isset($subChannels1["type"]) && $subChannels1["type"] == "main"){
										$dbNameMain = $subChannels1["database_name"];
									}
								}
							}
						}
					}
				}

				if($ts->channelInfo($channel[$dbNameRank])["data"]["seconds_empty"] == "-1"){
					$peopleOnChannel = $ts->channelClientList($channel[$dbNameRank], "-groups")["data"];
					foreach($peopleOnChannel as $person){
						$clientGroups = explode(",", $person["client_servergroups"]);
						if(in_array($channel["group_id"], $clientGroups)){
							$channelInfo = $ts->channelInfo($channel[$dbNameMain])["data"];
           					$ts->serverGroupDeleteClient($channel["group_id"], $person['client_database_id']);
							$ts->clientKick($person["clid"], "channel", "Wlasnie odszedles z klanu!");
							$ts->clientPoke($person["clid"], "Wlasnie odszedles z klanu!");
                       		$ts->setClientChannelGroup($zConfig["guestChannelGroup"], $channel[$dbNameMain], $person["client_database_id"]); 
						}else{
           					$ts->serverGroupAddClient($channel["group_id"], $person['client_database_id']);
							$ts->clientMove($person["clid"], $channel[$dbNameMain]);
							$ts->clientPoke($person["clid"], "Wlasnie doszedles do klanu!");
                       		$ts->setClientChannelGroup($zConfig["verifyChannelGroup"], $channel[$dbNameMain], $person["client_database_id"]); 
						}
					}
				}
			}
		}
	}
}