<?php

$config = [
	'teamSpeak' => [
		/*
			'serverIP' => Adres serwera z którym bot ma sie polaczyc.
			'serverPort' => Port UDP z którym bot ma sie polaczyc.
			'serverQueryPort' => Port query z którym bot ma sie polaczyc.
			'serverQueryLogin' => Nazwa uzytkownika która bot uzyje przy logowaniu.
			'serverQueryPassw' => Haslo uzytkownika które bot uzyje przy logowaniu.
			'botName' => Nazwa bota.
			'botChannel' => Kanał bota.
			'interval' => Co ile mają się wykonywać akcje.
		*/

		'serverIP' => '127.0.0.1',
		'serverPort' => 9987,
		'serverQueryPort' => 10011,
		'serverQueryLogin' => "serveradmin",
		'serverQueryPassw' => "",
		'botName' => 'FlameSpeak.PL @Kanaly',
		'botChannel' => 2,
		'interval' => 1,

	],

	'database' => [
		/*
			'dbAddress' => Adres serwera SQL.
			'dbLogin' => Nazwa uzytkownika aby zalogowac sie do serwera SQL.
			'dbPassw' => Haslo uzytkownika aby zalogowac sie do serwera SQL.
			'dbTable' => Nazwa tabelki gdzie beda sie umieszczane informacje o kanalach VIP.
		*/

		'dbAddress' => 'localhost',
		'dbLogin' => 'root',
		'dbPassw' => '',
		'dbTable' => 'db'
	],

	'zones' => [
		'elite' => [
			/*
				'getChannel' => Id kanału gdzię się dostaje kanał
				'groupToCopy' => Grupę którą bot będzie kopiował
				'groupName' => Nazwa grupy która będzie ustawiana
				'hcaGroup' => Id grupy kanałowej właściciela
				'verifyChannelGroup' => Id grupy kanałowej która zostanie nadana po wejściu na kanał nadaj/zabierz rangę
				'guestChannelGroup' => Grupa kanałowa gościa
			*/

			'getChannel' => 2,
			'groupToCopy' => 11,
			'groupName' => ' Elite: #[NUMER] ',
			'hcaGroup' => 9,
			'verifyChannelGroup' => 11,
			'guestChannelGroup' => 8,
			'online_description' => [
				'top' => '[center][size=14][b]Lista dostępnych użytkowników z[/b] [NAME] [/center]\n[size=11][list]',

				'usermsg' => [
					'online' => '[*][url=client://0/[CLUID]][NICK][/url] jest aktualnie [color=green][b]dostępny[/color] od [CZAS] na kanale [b][url=channelID://[CID]][CH_NAME][/url][/b].\n\n',
					'offline' => '[*][url=client://0/[CLUID]][NICK][/url] jest aktualnie [color=red][b]niedostępny[/color] od [CZAS].\n\n',
					'noneInGroup' => '[/size][size=9]Brak użytkowników w tej grupie.[/size]\n',
				],

				'bottom' => '[/size][hr][right]Wygenerowane przez [b]Lolka(?)[/b].',
			],
			'channelsToCreate' => [
				/*
					'name' => Nazwa kanału
					'database_name' => Jeżeli ta linijka jest podana w konfiguracji kanału id stworzonego kanału trafi do bazy
					'type' => Kanały z typem 'online','rank', lub 'main' są potrzebne do innych funkcji takich jak onlineMembers lub addGroup
					'assign_owner' => Zostanie nadana ranga właściciela
					'permissions' => Zostaną nadane permissje kanałowi
					'channel_topic' => Zostanie ustawiony "temat" kanału
				*/

				0 => [
					'name' => '[cspacer[NUMER]]———————————————',
					'database_name' => 'spacer1',
				],
				1 => [
					'name' => '[cspacer[CH_NUMER]][ [CH_NUMER] ] Kanał - Elite',
					'database_name' => 'nameChannel',
				],
				2 => [
					'name' => '[cspacer[CH_NUMER]]Status Online: [ONLINE]/[TOGETHER] ([PERCENT]%)',
					'database_name' => 'statusChannel',
					'type' => 'online',
				],
				3 => [
					'name' => '[cspacer[NUMER]]———————————————',
					'database_name' => 'spacer2',
				],
				4 => [
					'name' => '[cspacer[NUMER]]┄┄┉┉[ Weryfikacja ]┉┉┄┄',
					'database_name' => 'verifyChannel',
					'assign_owner' => true,
					'permissions' => [
						'i_channel_needed_join_power' => 50,
					],

					'subChannels' => [
						0 => [
							'name' => '• Daj/Zabierz Rangę [ [NAME] ]',
							'correct' => true,
							'database_name' => 'rankChannel',
							'type' => 'rank',
						],
					],
				],
				5 => [
					'name' => '[cspacer[NUMER]]┄┄┉┉[ Liderówka ]┉┉┄┄',
					'database_name' => 'leadersChannel',
					'assign_owner' => true,
					'permissions' => [
						'i_channel_join_power' => 50,
					],

					'subChannels' => [
						0 => [
							'name' => '• [Lider] Kanał [NUMER]',
							'count' => 3,
			
							'subChannels' => [
								0 => [
									'name' => 'Podkanał Lidera',
									'open' => true,
								],
							],
						],
					],
				],
				6 => [
					'name' => '[cspacer[NUMER]]┄┄┉┉[ Kanał Główny ]┉┉┄┄',
					'database_name' => 'mainChannel',
					'type' => 'main',
					'assign_owner' => true,
					'permissions' => [
						'i_channel_join_power' => 50,
					],

					'subChannels' => [
						0 => [
							'name' => '[NUMER]. Podkanał',
							'count' => 9,
						],
						1 => [
							'name' => '10. Rekrutacja',
							'open' => true,
						],
					],
				],
				7 => [
					'name' => '[spacer[NUMER]#lastChannel:elite]',
					'database_name' => 'spacer3',
					'channel_topic' => '#lastChannel:elite',
				],
			],
		],
		'mc' => [
			'getChannel' => 4689,
			'groupToCopy' => 80,
			'groupName' => ' MC: #[NUMER] ',
			'hcaGroup' => 12,
			'verifyChannelGroup' => 11,
			'guestChannelGroup' => 8,
			'online_description' => [
				'top' => '[center][size=14][b]Lista dostępnych użytkowników z[/b] [NAME] [/center]\n[size=11][list]',

				'usermsg' => [
					'online' => '[*][url=client://0/[CLUID]][NICK][/url] jest aktualnie [color=green][b]dostępny[/color] od [CZAS] na kanale [b][url=channelID://[CID]][CH_NAME][/url][/b].\n\n',
					'offline' => '[*][url=client://0/[CLUID]][NICK][/url] jest aktualnie [color=red][b]niedostępny[/color] od [CZAS].\n\n',
					'noneInGroup' => '[/size][size=9]Brak użytkowników w tej grupie.[/size]\n',
				],

				'bottom' => '[/size][hr][right]Wygenerowane przez [b]Lolka(?)[/b].',
			],
			'channelsToCreate' => [
				0 => [
					'name' => '[spacer][»] Kanał MC | [CH_NUMER] |',
					'database_name' => 'nameChannel',
					'assign_owner' => true,

					'subChannels' => [
						0 => [
							'name' => 'Automatyzacja:',
							'database_name' => 'verifyChannel',

							'subChannels' => [
								0 => [
									'name' => '● Daj / Zabierz rangę',
									'database_name' => 'rankChannel',
									'type' => 'rank'
								],
								1 => [
									'name' => '● Online z [NAME]: [ONLINE]/[TOGETHER]',
									'database_name' => 'statusChannel',
									'type' => 'online'
								],
							],
						],
						1 => [
							'name' => 'Kanał Główny: [NAME]',
							'database_name' => 'mainChannel',
							'type' => 'main',
							'correct' => true,

							'subChannels' => [
								0 => [
									'name' => '[NUMER]. Podkanał',
									'count' => 4,
								],
								1 => [
									'name' => '5. Rekrutacja',
									'open' => true,
								],						
							],
						],
					],
				],
				1 => [
					'name' => '[spacer[NUMER]#lastChannel:mc]',
					'database_name' => 'spacer1',
					'channel_topic' => '#lastChannel:mc',
				],
			],
		],
	],
];

