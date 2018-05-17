<?php
class checkNames{
	function __construct($ts, $config, $pdo){
		foreach($config["zones"] as $zone => $zConfig){
			$statement = $pdo->prepare("SELECT * FROM `".$zone."Channels`");
			$statement->execute();
			$channels = $statement->fetchAll(PDO::FETCH_ASSOC);
			foreach($channels as $channel){
				$groups = $ts->serverGroupList()["data"];
				foreach($groups as $group) {
					if($group['sgid'] == $channel["group_id"]) {
						$cGroup = $group;
					}
				}

				if(strcmp($channel["group_name"], $cGroup['name'])){
					$statement = $pdo->prepare("UPDATE `".$zone."Channels` SET `group_name`=? WHERE id=?");
					$statement->execute(array($cGroup["name"], $channel["id"]));
				}

				foreach($zConfig["channelsToCreate"] as $id => $channelsToEdit){
					if(isset($channelsToEdit["dbName"]) && isset($channelsToEdit["correctName"])){
						$channelInfo = $ts->channelInfo($channel[$channelsToEdit["dbName"]])["data"];
						if($channelInfo["channel_name"] !== str_replace(array("[CH_NUMER]", "[NAME]"), array($channel["num"], $cGroup['name']), $channelsToEdit["name"])){
							$ts->channelEdit($channel[$channelsToEdit["dbName"]],array('channel_name' => str_replace(array("[CH_NUMER]", "[NAME]"), array($channel["num"], $channel["group_name"]), $channelsToEdit["name"])));
						}
					}

					if(isset($channelsToEdit["subChannels"]["dbName"]) && isset($channelsToEdit["subChannels"]["correctName"])){
						$channelInfo = $ts->channelInfo($channel[$channelsToEdit["subChannels"]["dbName"]])["data"];
						if($channelInfo["channel_name"] !== str_replace(array("[CH_NUMER]", "[NAME]"), array($channel["num"], $cGroup['name']), $channelsToEdit["subChannels"]["name"])){
							$ts->channelEdit($channel[$channelsToEdit["subChannels"]["dbName"]],array('channel_name' => str_replace(array("[CH_NUMER]", "[NAME]"), array($channel["num"], $channel["group_name"]), $channelsToEdit["subChannels"]["name"])));
						}
					}
				}
			}
		}
	}
}