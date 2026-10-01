<?php

return [
    'title' => 'Wersja',
    'unsupported' => 'Tego typu serwer nie może tutaj zmienić wersji Minecrafta.',
    'current_title' => 'Zainstalowane teraz',
    'current_unknown' => 'Jeszcze nie zainstalowano przez tę stronę (działa wersja z konfiguracji serwera).',
    'install_title' => 'Zainstaluj :name',
    'no_versions' => 'Obecnie brak dostępnych wersji.',
    'version_label' => 'Wersja',
    'install_button' => 'Zainstaluj',
    'java_hint' => 'Wymaga Javy :java; odpowiedni obraz Javy zostanie wybrany automatycznie.',
    'installing' => 'Instalowanie… serwer zostaje zatrzymany, a nowa wersja pobrana. Może to potrwać minutę.',
    'confirm' => 'Zainstalować :name :version? Serwer zostanie zatrzymany, a jego plik jar zastąpiony. Światy, pluginy i ustawienia pozostaną.',
    'confirm_downgrade' => 'To starsza wersja niż zainstalowana. Światów Minecrafta zwykle nie da się otworzyć w starszych wersjach i mogą zostać uszkodzone. Najpierw zrób kopię zapasową! Mimo to zainstalować :name :version?',
    'confirm_yes' => 'Tak, zainstaluj',
    'confirm_no' => 'Anuluj',
    'backup_hint' => 'Wskazówka: przed zmianą oprogramowania lub wersji utwórz kopię zapasową.',
    'installed' => 'Zainstalowano :name :version (Java :java). Uruchom serwer, aby z niej korzystać.',
    'types' => [
        'paper' => [
            'name' => 'Paper',
            'description' => 'Szybki, z pluginami (Bukkit/Spigot)',
        ],
        'purpur' => [
            'name' => 'Purpur',
            'description' => 'Paper z wieloma dodatkowymi opcjami',
        ],
        'folia' => [
            'name' => 'Folia',
            'description' => 'Paper dla bardzo dużych serwerów (wielowątkowy)',
        ],
        'fabric' => [
            'name' => 'Fabric',
            'description' => 'Lekki loader modów',
        ],
        'vanilla' => [
            'name' => 'Vanilla',
            'description' => 'Oryginalny serwer od Mojang',
        ],
        'velocity' => [
            'name' => 'Velocity',
            'description' => 'Proxy łączące kilka serwerów',
        ],
    ],
    'errors' => [
        'unsupported' => 'Ten serwer nie może zmienić wersji (jego start nie używa pliku jar).',
        'unknown_version' => 'Ta wersja jest niedostępna.',
        'unknown_type' => 'Nieznane oprogramowanie serwera.',
        'download' => 'Nie udało się pobrać nowej wersji. Spróbuj ponownie później.',
        'no_build' => 'Dla tej wersji nie ma jeszcze pliku do pobrania.',
        'still_running' => 'Nie udało się zatrzymać serwera. Zatrzymaj go i spróbuj ponownie.',
        'api' => 'Nie udało się wczytać listy wersji. Spróbuj ponownie później.',
        'busy' => 'Na tym serwerze trwa już instalacja wersji.',
        'backup_full' => 'Osiągnięto limit kopii zapasowych, więc nie można było najpierw utworzyć kopii. Usuń kopię lub zmień bez kopii.',
        'backup_throttled' => 'Zbyt wiele kopii w krótkim czasie. Poczekaj chwilę i spróbuj ponownie.',
        'backup_failed' => 'Kopia zapasowa się nie powiodła, więc nic nie zmieniono. Spróbuj ponownie lub zmień bez kopii.',
        'backup_timeout' => 'Kopia trwa dłużej niż oczekiwano i nadal się wykonuje; nic nie zmieniono. Spróbuj ponownie, gdy się zakończy.',
    ],
    'backup_first' => 'Najpierw utwórz kopię zapasową',
    'backup_full_note' => 'Lista kopii zapasowych jest pełna. Najpierw usuń kopię lub zmień bez kopii.',
    'installing_backup' => 'Tworzenie kopii zapasowej, potem instalacja… serwer jest w tym czasie zatrzymany. Może to potrwać kilka minut.',
];
