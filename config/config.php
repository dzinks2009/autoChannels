<?php

$config = [
	'teamSpeak' => [
		/*
			Linijka konfiguracji: serverIP

				- Adres serwera z którym bot ma sie polaczyc.
				- Przyklad: 127.0.0.1

			Linijka konfiguracji: serverPort

				- Port UDP z którym bot ma sie polaczyc.
				- Przyklad: 10011

			Linijka konfiguracji: serverQueryPort

				- Port query z którym bot ma sie polaczyc.
				- Przyklad: 10011

			Linijka konfiguracji: serverQueryLogin

				- Nazwa uzytkownika która bot uzyje przy logowaniu.
				- Przyklad: serveradmin

			Linijka konfiguracji: serverQueryPassw

				- Haslo uzytkownika które bot uzyje przy logowaniu.
				- Przyklad: 10011

			Linijka konfiguracji: botName

				- Nazwa bota.
				- Przyklad: FlameSpeak.PL @Kanaly

			Linijka konfiguracji: botChannel

				- Kanał bota.
				- Przyklad: 1

			Linijka konfiguracji: interval

				- Co ile mają się wykonywać akcje.
				- Przyklad: 15
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
			Linijka konfiguracji: dbAddress

				- Adres serwera SQL.
				- Przyklad: 127.0.0.1

			Linijka konfiguracji: dbLogin

				- Nazwa uzytkownika aby zalogowac sie do serwera SQL.
				- Przyklad: root

			Linijka konfiguracji: dbPassw

				- Haslo uzytkownika aby zalogowac sie do serwera SQL.
				- Przyklad: haslo

			Linijka konfiguracji: dbTable

				- Nazwa tabelki gdzie beda sie umieszczane informacje o kanalach VIP.
				- Przyklad: tabelka
		*/

		'dbAddress' => 'localhost',

		'dbLogin' => '',

		'dbPassw' => '',

		'dbTable' => 'db'
	],

	'zones' => [
		'elite' => [
			/*
				Linijka konfiguracji: getChannel
				
					- Gdzie trza wejść by dostać kanał w tej strefie.
					- Przyklad: 2
			*/
			'getChannel' => 2,

			/*
				Linijka konfiguracji: groupToCopy
				
					- ID grupy którą ma kopiować.
					- Przyklad: 2
			*/
			
			'groupToCopy' => 11,

			/*
				Linijka konfiguracji: groupName
				
					- Nazwa grupy po skopiowaniu.
					- Przyklad: Elite: #[NUMER]
			*/
			
			'groupName' => ' Elite: #[NUMER] ',

			/*
				Linijka konfiguracji: hcaGroup
				
					- ID grupy kanałowej właściciela kanału.
					- Przyklad: Elite: #[NUMER]
			*/
			
			'hcaGroup' => 9,

			/*
				Linijka konfiguracji: onlineTable,mainChannelTable,mainNumChannelTable,groupManageTable
				
					- Nazwy te powinny kierować do nazw w bazie danych.
			*/
			
			'onlineTable' => 'onlineFromGroup',
			
			'mainChannelTable' => 'mainChannel',
			
			'mainNumChannelTable' => 'mainChannelName',
			
			'groupManageTable' => 'serverChannelGroup',
			
			/*
				Linijka konfiguracji: idWhereOnline
				
					- ID w channelsToCreate gdzie się znajduje status online.
					- Przyklad: 2
			*/
			
			'idWhereOnline' => 2,

			'channelsToCreate' => [
				/*
					Jeśli konfiguracja kanału zawiera 'name', zostanie mu nadana taka nazwa przy stworzeniu.
					
					Jeśli konfiguracja kanału zawiera 'dbName', zostanie dodanie ten kanał do bazy danych.

					Jeśli konfiguracja kanału zawiera 'correctName', nazwa tego kanału zostanie poprawiana.

					Jeśli konfiguracja kanału zawiera 'assignHCA', zostanie właścicielowi nadana ranga.

					Jeśli konfiguracja kanału zawiera 'subChannels', zostaną tworzone tam podkanały.
					
						Jeśli konfiguracja kanału zawiera 'howMany', zostanie taka liczba użyta do stworzenia podkanałów.

						Jeśli konfiguracja kanału zawiera 'channelsToSubChannels', zostanie stworzony podkanał do podkanału.
						
						Jeśli konfiguracja kanału zawiera 'subChannelsName', podkanał do podkanału będzie zawierał taką nazwę.
				*/
				'0' => [
					'name' => '[cspacer[NUMER]]———————————————',
				],
				'1' => [
					'name' => '[cspacer][ [CH_NUMER] ] Premium | [NAME]',
					'dbName' => 'mainChannelName',
					'correctName' => true,
				],
				'2' => [
					'name' => '[cspacer[CH_NUMER]]Status Online: [ONLINE]/[TOGETHER] ([PERCENT]%)',
					'dbName' => 'onlineFromGroup',
				],
				'3' => [
					'name' => '[cspacer[NUMER]]———————————————',
				],
				'4' => [
					'name' => '[cspacer[NUMER]]┄┄┉┉[ Weryfikacja ]┉┉┄┄',
					'dbName' => 'mainChannelVerification',
					'assignHCA' => true,
					'subChannels' => [
						'type' => 'serverChannelGroup',
						'name' => '• Daj/Zabierz Rangę [ [NAME] ]',
						'dbName' => 'serverChannelGroup',
						'correctName' => true,
					],
				],
				'5' => [
					'name' => '[cspacer[NUMER]]┄┄┉┉[ Liderówka ]┉┉┄┄',
					'dbName' => 'mainChannelLeaders',
					'assignHCA' => true,
					'subChannels' => [
						'type' => 'leadersChannels',
						'howMany' => 3,
						'name' => '• [Lider] Kanał [NUMER]',
						'channelsToSubChannels' => true,
						'subChannelsName' => '[NUMER]. Podkanał',
					],
				],
				'6' => [
					'name' => '[cspacer[NUMER]]┄┄┉┉[ Kanał Główny ]┉┉┄┄',
					'dbName' => 'mainChannel',
					'assignHCA' => true,
					'main_channel' => true,
					'subChannels' => [
						'type' => 'userChannels',
						'howMany' => 10,
						'name' => '[NUMER]. Podkanał',
					],
				],
				'7' => [
					'name' => '[cspacer[NUMER]]',
				],
			],
		],
	],

];

