<?php

return [
    'title' => 'Ustawienia',
    'sftp' => [
        'heading' => 'Szczegóły SFTP',
        'server_address_label' => 'Adres serwera',
        'username_label' => 'Nazwa użytkownika',
        'password_notice' => 'Twoje hasło SFTP jest takie samo jak hasło używane do logowania do tego panelu.',
        'launch_button' => 'Uruchom SFTP',
    ],
    'debug' => [
        'heading' => 'Informacje debugowania',
        'node_label' => 'Węzeł',
        'server_id_label' => 'ID serwera',
    ],
    'rename' => [
        'heading' => 'Zmień szczegóły serwera',
        'name_label' => 'Nazwa serwera',
        'description_label' => 'Opis serwera',
        'save_button' => 'Zapisz',
    ],
    'reinstall' => [
        'heading' => 'Zainstaluj serwer ponownie',
        'disabled_notice' => 'Ponowna instalacja tego serwera została wyłączona, ponieważ jest skonfigurowany tak, aby pomijał skrypt instalacyjny swojego egg. Jeśli chcesz go zainstalować ponownie, skontaktuj się z administratorem serwera.',
        'body' => 'Ponowna instalacja zatrzyma serwer, a następnie ponownie uruchomi skrypt instalacyjny, który go pierwotnie skonfigurował.',
        'body_warning' => 'Podczas tego procesu niektóre pliki mogą zostać usunięte lub zmodyfikowane — wykonaj kopię zapasową danych przed kontynuowaniem.',
        'reinstall_button' => 'Zainstaluj serwer ponownie',
        'confirm_title' => 'Potwierdź ponowną instalację serwera',
        'confirm_button' => 'Tak, zainstaluj serwer ponownie',
        'confirm_body' => 'Serwer zostanie zatrzymany, a niektóre pliki mogą zostać usunięte lub zmodyfikowane podczas tego procesu. Czy na pewno chcesz kontynuować?',
        'success_message' => 'Twój serwer rozpoczął proces ponownej instalacji.',
    ],
];
