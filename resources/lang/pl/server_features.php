<?php

return [
    'gsl_token' => [
        'title' => 'Nieprawidłowy token GSL!',
        'body_1' => 'Wygląda na to, że Twój Gameserver Login Token (token GSL) jest nieprawidłowy lub wygasł.',
        'body_2' => 'Możesz wygenerować nowy i wpisać go poniżej albo zostawić pole puste, aby całkowicie go usunąć.',
        'field_label' => 'Token GSL',
        'field_description' => 'Odwiedź https://steamcommunity.com/dev/managegameservers, aby wygenerować token.',
        'update_button' => 'Zaktualizuj token GSL',
    ],
    'hytale_oauth' => [
        'title' => 'Wymagane uwierzytelnienie',
        'body' => 'Musisz uwierzytelnić się kontem Hytale, aby pobrać lub zaktualizować pliki serwera. Zaloguj się, aby kontynuować.',
        'cancel_button' => 'Anuluj',
        'login_button' => 'Zaloguj się',
    ],
    'pid_limit' => [
        'admin_title' => 'Osiągnięto limit pamięci lub procesów...',
        'admin_body_1' => 'Ten serwer osiągnął maksymalny limit procesów lub pamięci.',
        'admin_body_2' => 'Zwiększenie <0>container_pid_limit</0> w konfiguracji wings, <1>config.yml</1>, może pomóc rozwiązać ten problem.',
        'admin_note' => 'Uwaga: aby zmiany w pliku konfiguracyjnym zaczęły działać, trzeba zrestartować Wings',
        'user_title' => 'Prawdopodobnie osiągnięto limit zasobów...',
        'user_body' => 'Ten serwer próbuje używać więcej zasobów niż przydzielono. Skontaktuj się z administratorem i przekaż mu poniższy błąd.',
        'close_button' => 'Zamknij',
    ],
    'steam_disk_space' => [
        'title' => 'Brak wolnego miejsca na dysku...',
        'admin_body_1' => 'Na tym serwerze zabrakło wolnego miejsca na dysku i nie może dokończyć instalacji ani aktualizacji.',
        'admin_body_2' => 'Upewnij się, że maszyna ma wystarczająco dużo miejsca na dysku, wpisując <0>df -h</0> na maszynie hostującej ten serwer. Usuń pliki lub zwiększ dostępne miejsce, aby rozwiązać problem.',
        'user_body' => 'Na tym serwerze zabrakło wolnego miejsca na dysku i nie może dokończyć instalacji ani aktualizacji. Skontaktuj się z administratorami i poinformuj ich o problemie z miejscem na dysku.',
        'close_button' => 'Zamknij',
    ],
    'eula' => [
        'title' => 'Zaakceptuj EULA Minecraft®',
        'body_prefix' => 'Naciskając poniżej „Akceptuję”, potwierdzasz zgodę na',
        'body_suffix' => '.',
        'link_text' => 'EULA Minecraft®',
        'cancel_button' => 'Anuluj',
        'accept_button' => 'Akceptuję',
    ],
    'java_version' => [
        'title' => 'Nieobsługiwana wersja Javy',
        'body' => 'Ten serwer używa nieobsługiwanej wersji Javy i nie może zostać uruchomiony.',
        'body_select_notice' => ' Wybierz obsługiwaną wersję z listy poniżej, aby kontynuować uruchamianie serwera.',
        'cancel_button' => 'Anuluj',
        'update_button' => 'Zaktualizuj obraz Docker',
    ],
];
