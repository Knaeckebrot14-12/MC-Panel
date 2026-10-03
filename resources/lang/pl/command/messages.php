<?php

return [
    'location' => [
        'no_location_found' => 'Nie znaleziono rekordu pasującego do podanego krótkiego kodu.',
        'ask_short' => 'Krótki kod lokalizacji',
        'ask_long' => 'Opis lokalizacji',
        'created' => 'Pomyślnie utworzono nową lokalizację (:name) o ID :id.',
        'deleted' => 'Pomyślnie usunięto żądaną lokalizację.',
    ],
    'user' => [
        'search_users' => 'Wpisz nazwę użytkownika, ID użytkownika lub adres e-mail',
        'select_search_user' => 'ID użytkownika do usunięcia (wpisz \'0\', aby wyszukać ponownie)',
        'deleted' => 'Użytkownik został pomyślnie usunięty z panelu.',
        'confirm_delete' => 'Czy na pewno chcesz usunąć tego użytkownika z panelu?',
        'no_users_found' => 'Nie znaleziono użytkowników dla podanej frazy.',
        'multiple_found' => 'Znaleziono wiele kont dla podanego użytkownika; nie można usunąć użytkownika z powodu flagi --no-interaction.',
        'ask_admin' => 'Czy ten użytkownik jest administratorem?',
        'ask_email' => 'Adres e-mail',
        'ask_username' => 'Nazwa użytkownika',
        'ask_name_first' => 'Imię',
        'ask_name_last' => 'Nazwisko',
        'ask_password' => 'Hasło',
        'ask_password_tip' => 'Jeśli chcesz utworzyć konto z losowym hasłem wysłanym użytkownikowi e-mailem, uruchom to polecenie ponownie (CTRL+C) i dodaj flagę `--no-password`.',
        'ask_password_help' => 'Hasła muszą mieć co najmniej 8 znaków i zawierać co najmniej jedną wielką literę oraz cyfrę.',
        '2fa_help_text' => [
            0 => 'To polecenie wyłącza uwierzytelnianie dwuskładnikowe na koncie użytkownika, jeśli jest włączone. Należy go używać wyłącznie do odzyskiwania konta, gdy użytkownik nie może się zalogować.',
            1 => 'Jeśli nie tego chciałeś, naciśnij CTRL+C, aby zakończyć ten proces.',
        ],
        '2fa_disabled' => 'Uwierzytelnianie dwuskładnikowe zostało wyłączone dla :email.',
    ],
    'schedule' => [
        'output_line' => 'Wysyłanie zadania dla pierwszej akcji w `:schedule` (:hash).',
    ],
    'maintenance' => [
        'deleting_service_backup' => 'Usuwanie pliku kopii zapasowej usługi :file.',
    ],
    'server' => [
        'rebuild_failed' => 'Żądanie przebudowy dla „:name” (#:id) na nodzie „:node” nie powiodło się z błędem: :message',
        'reinstall' => [
            'failed' => 'Żądanie reinstalacji dla „:name” (#:id) na nodzie „:node” nie powiodło się z błędem: :message',
            'confirm' => 'Zamierzasz ponownie zainstalować grupę serwerów. Czy chcesz kontynuować?',
        ],
        'power' => [
            'confirm' => 'Zamierzasz wykonać akcję :action na :count serwerach. Czy chcesz kontynuować?',
            'action_failed' => 'Żądanie zmiany zasilania dla „:name” (#:id) na nodzie „:node” nie powiodło się z błędem: :message',
        ],
    ],
    'environment' => [
        'mail' => [
            'ask_smtp_host' => 'Host SMTP (np. smtp.gmail.com)',
            'ask_smtp_port' => 'Port SMTP',
            'ask_smtp_username' => 'Nazwa użytkownika SMTP',
            'ask_smtp_password' => 'Hasło SMTP',
            'ask_mailgun_domain' => 'Domena Mailgun',
            'ask_mailgun_endpoint' => 'Endpoint Mailgun',
            'ask_mailgun_secret' => 'Sekret Mailgun',
            'ask_mandrill_secret' => 'Sekret Mandrill',
            'ask_postmark_username' => 'Klucz API Postmark',
            'ask_driver' => 'Którego sterownika użyć do wysyłania e-maili?',
            'ask_mail_from' => 'Adres e-mail, z którego mają pochodzić wiadomości',
            'ask_mail_name' => 'Nazwa, pod którą mają się pojawiać wiadomości',
            'ask_encryption' => 'Metoda szyfrowania',
        ],
    ],
];
