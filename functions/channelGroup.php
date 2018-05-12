<?php
class channelGroup{
	function __construct($ts, $config, $pdo){
		foreach($config["zones"] as $zone => $zConfig){
			$statement = $pdo->prepare("SELECT * FROM `".$zone."Channels`");
			$statement->execute();
			$channels = $statement->fetchAll(PDO::FETCH_ASSOC);
			foreach($channels as $channel){
				if($ts->channelInfo($channel[$zConfig["groupManageTable"]])["data"]["seconds_empty"] == "-1"){
					$peopleOnChannel = $ts->channelClientList($channel[$zConfig["groupManageTable"]], "-groups")["data"];
					foreach($peopleOnChannel as $person){
						$clientGroups = explode(",", $person["client_servergroups"]);
						if(in_array($channel["group_id"], $clientGroups)){
           						$ts->serverGroupDeleteClient($channel["group_id"], $person['client_database_id']);
							$ts->clientKick($person["clid"], "channel", "Wlasnie odszedles z klanu!");
							$ts->clientPoke($person["clid"], "Wlasnie odszedles z klanu!");

                       					$ts->setClientChannelGroup(0, $channel[$zConfig["mainChannelTable"]], $person["client_database_id"]); 
						}else{
           						$ts->serverGroupAddClient($channel["group_id"], $person['client_database_id']);
							$ts->clientMove($person["clid"], $channel[$zConfig["mainChannelTable"]]);
							$ts->clientPoke($person["clid"], "Wlasnie doszedles do klanu!");

                       					$ts->setClientChannelGroup($zConfig["verifyChannelGroup"], $channel[$zConfig["mainChannelTable"]], $person["client_database_id"]); 

						}
					}
				}
			}
		}
	}
}
