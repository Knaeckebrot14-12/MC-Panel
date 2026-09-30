<?php

return [
    'title' => 'Bazy danych',
    'empty' => 'Wygląda na to, że nie masz żadnych baz danych.',
    'disabled' => 'Nie można tworzyć baz danych dla tego serwera.',
    'allocated' => 'Do tego serwera przydzielono :used z :limit baz danych.',
    'new_database_button' => 'Nowa baza danych',
    'rotate_password_button' => 'Zmień hasło',
    'labels' => [
        'endpoint' => 'Punkt końcowy',
        'connections_from' => 'Połączenia z',
        'username' => 'Nazwa użytkownika',
    ],
    'create' => [
        'heading' => 'Utwórz nową bazę danych',
        'name_label' => 'Nazwa bazy danych',
        'name_description' => 'Opisowa nazwa Twojej instancji bazy danych.',
        'connections_from_label' => 'Połączenia z',
        'connections_from_description' => 'Skąd mają być dozwolone połączenia. Zostaw puste, aby zezwolić na połączenia z dowolnego miejsca.',
        'cancel' => 'Anuluj',
        'create_button' => 'Utwórz bazę danych',
        'validation' => [
            'name_required' => 'Należy podać nazwę bazy danych.',
            'name_min' => 'Nazwa bazy danych musi mieć co najmniej 3 znaki.',
            'name_max' => 'Nazwa bazy danych nie może przekraczać 48 znaków.',
            'name_format' => 'Nazwa bazy danych może zawierać tylko znaki alfanumeryczne, podkreślenia, myślniki i/lub kropki.',
            'connections_from_format' => 'Należy podać prawidłowy adres hosta.',
        ],
    ],
    'delete' => [
        'heading' => 'Potwierdź usunięcie bazy danych',
        'body_prefix' => 'Usunięcie bazy danych jest działaniem trwałym i nie można go cofnąć. Spowoduje to trwałe usunięcie bazy danych',
        'body_suffix' => ' i wszystkich powiązanych danych.',
        'confirm_label' => 'Potwierdź nazwę bazy danych',
        'confirm_description' => 'Wpisz nazwę bazy danych, aby potwierdzić usunięcie.',
        'confirm_required' => 'Należy podać nazwę bazy danych.',
        'cancel' => 'Anuluj',
        'delete_button' => 'Usuń bazę danych',
    ],
    'connection' => [
        'heading' => 'Szczegóły połączenia z bazą danych',
        'jdbc_label' => 'Ciąg połączenia JDBC',
        'password_label' => 'Hasło',
        'close_button' => 'Zamknij',
    ],
];
