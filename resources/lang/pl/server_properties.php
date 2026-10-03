<?php

return [
    'title' => 'Ustawienia serwera',
    'missing' => 'Ten serwer nie ma jeszcze pliku server.properties. Uruchom go raz lub zapisz tutaj, aby utworzyć plik.',
    'saved' => 'Ustawienia zapisane. Zaczną działać przy następnym uruchomieniu.',
    'saved_restart' => 'Ustawienia zapisane. Uruchom serwer ponownie, aby je zastosować.',
    'unsaved' => 'Niezapisane zmiany: :count',
    'reset' => 'Odrzuć',
    'save' => 'Zapisz',
    'locked' => 'Port, IP i RCON są zarządzane przez panel i nie można ich tutaj zmieniać.',
    'groups' => [
        'general' => 'Ogólne',
        'world' => 'Świat',
        'access' => 'Dostęp i bezpieczeństwo',
        'resource_pack' => 'Paczka zasobów',
        'other' => 'Pozostałe ustawienia',
    ],
    'options' => [
        'survival' => 'Przetrwanie',
        'creative' => 'Kreatywny',
        'adventure' => 'Przygodowy',
        'spectator' => 'Obserwator',
        'peaceful' => 'Pokojowy',
        'easy' => 'Łatwy',
        'normal' => 'Normalny',
        'hard' => 'Trudny',
        'flat' => 'Superpłaski',
        'large_biomes' => 'Duże biomy',
        'amplified' => 'Wzmocniony',
    ],
    'server_list' => [
        'title' => 'Lista serwerów',
        'line1' => 'Pierwsza linia',
        'line2' => 'Druga linia (opcjonalnie)',
        'codes_hint' => 'Kody kolorów i formatowania zaczynają się od & (np. &a zielony, &l pogrubienie, &r reset). Kliknij kolor, aby wstawić go przy kursorze. Podgląd pokazuje, jak gracze widzą serwer.',
        'icon_title' => 'Ikona serwera',
        'icon_upload' => 'Prześlij ikonę',
        'icon_remove' => 'Usuń ikonę',
        'icon_hint' => 'PNG, JPG, GIF lub WebP. Zostanie automatycznie przycięta do kwadratu i zmniejszona do 64×64. Działa po restarcie.',
        'icon_saved' => 'Ikona serwera zapisana. Pojawi się po następnym restarcie.',
        'icon_invalid' => 'Ten plik nie jest czytelnym obrazem.',
        'no_icon' => 'Brak ikony',
        'format' => [
            'l' => 'Pogrubienie',
            'o' => 'Kursywa',
            'n' => 'Podkreślenie',
            'm' => 'Przekreślenie',
            'k' => 'Magia',
            'r' => 'Reset',
        ],
    ],
    'fields' => [
        'motd' => [
            'label' => 'Opis serwera (MOTD)',
            'description' => 'Wyświetlany na liście serwerów w trybie wieloosobowym.',
        ],
        'max-players' => [
            'label' => 'Maksymalna liczba graczy',
            'description' => 'Ilu graczy może być online jednocześnie.',
        ],
        'gamemode' => [
            'label' => 'Tryb gry',
            'description' => 'Tryb gry dla nowych graczy.',
        ],
        'difficulty' => [
            'label' => 'Poziom trudności',
            'description' => 'Jak niebezpieczne są moby i głód.',
        ],
        'hardcore' => [
            'label' => 'Hardcore',
            'description' => 'Gracze są banowani po jednej śmierci.',
        ],
        'pvp' => [
            'label' => 'PvP',
            'description' => 'Gracze mogą się nawzajem ranić.',
        ],
        'force-gamemode' => [
            'label' => 'Wymuś tryb gry',
            'description' => 'Gracze zawsze dołączają w domyślnym trybie gry.',
        ],
        'allow-flight' => [
            'label' => 'Zezwól na latanie',
            'description' => 'Potrzebne niektórym pluginom i modom; w przeciwnym razie latający gracze są wyrzucani.',
        ],
        'level-name' => [
            'label' => 'Folder świata',
            'description' => 'Nazwa folderu świata do wczytania lub utworzenia.',
        ],
        'level-seed' => [
            'label' => 'Seed',
            'description' => 'Seed dla nowych światów; puste oznacza losowy.',
        ],
        'level-type' => [
            'label' => 'Typ świata',
            'description' => 'Używane tylko przy generowaniu nowego świata.',
        ],
        'allow-nether' => [
            'label' => 'Nether',
            'description' => 'Gracze mogą podróżować do Netheru.',
        ],
        'generate-structures' => [
            'label' => 'Struktury',
            'description' => 'Generowane są wioski, świątynie i inne struktury.',
        ],
        'spawn-monsters' => [
            'label' => 'Potwory',
            'description' => 'Pojawiają się wrogie moby.',
        ],
        'spawn-npcs' => [
            'label' => 'Mieszkańcy',
            'description' => 'Pojawiają się mieszkańcy wiosek.',
        ],
        'spawn-protection' => [
            'label' => 'Ochrona spawnu',
            'description' => 'Promień wokół spawnu, w którym budować mogą tylko operatorzy (0 = wyłączone).',
        ],
        'view-distance' => [
            'label' => 'Zasięg widzenia',
            'description' => 'Chunki wysyłane do graczy. Niższe wartości oszczędzają pamięć i CPU.',
        ],
        'simulation-distance' => [
            'label' => 'Zasięg symulacji',
            'description' => 'Chunki wokół graczy, w których rzeczy się poruszają i rosną.',
        ],
        'max-world-size' => [
            'label' => 'Granica świata',
            'description' => 'Maksymalny promień świata w blokach.',
        ],
        'white-list' => [
            'label' => 'Whitelista',
            'description' => 'Dołączyć mogą tylko gracze z whitelisty.',
        ],
        'enforce-whitelist' => [
            'label' => 'Egzekwuj whitelistę',
            'description' => 'Wyrzuca graczy online, którzy zostali usunięci z whitelisty.',
        ],
        'online-mode' => [
            'label' => 'Tryb online',
            'description' => 'Sprawdza konta w Mojang. Wyłączaj tylko za proxy, takim jak Velocity lub BungeeCord.',
        ],
        'enforce-secure-profile' => [
            'label' => 'Bezpieczny profil czatu',
            'description' => 'Gracze potrzebują kluczy czatu podpisanych przez Mojang.',
        ],
        'enable-command-block' => [
            'label' => 'Bloki poleceń',
            'description' => 'Bloków poleceń można używać.',
        ],
        'op-permission-level' => [
            'label' => 'Poziom operatora',
            'description' => 'Poziom uprawnień operatorów (1-4).',
        ],
        'player-idle-timeout' => [
            'label' => 'Wyrzucanie za AFK (minuty)',
            'description' => 'Wyrzuca bezczynnych graczy po tylu minutach (0 = nigdy).',
        ],
        'resource-pack' => [
            'label' => 'URL paczki zasobów',
            'description' => 'Bezpośredni link do pobrania paczki zasobów (zip).',
        ],
        'require-resource-pack' => [
            'label' => 'Wymagaj paczki zasobów',
            'description' => 'Gracze, którzy odrzucą paczkę, są rozłączani.',
        ],
    ],
];
