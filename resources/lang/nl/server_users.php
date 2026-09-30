<?php

return [
    'title' => 'Gebruikers',
    'empty' => 'Het lijkt erop dat je geen subgebruikers hebt.',
    'new_user_button' => 'Nieuwe gebruiker',
    'row' => [
        'two_factor_label' => '2FA ingeschakeld',
        'permissions_label' => 'Rechten',
        'edit_aria' => 'Subgebruiker bewerken',
    ],
    'edit_modal' => [
        'title_modify' => 'Rechten van :email wijzigen',
        'title_view' => 'Rechten van :email bekijken',
        'title_create' => 'Nieuwe subgebruiker aanmaken',
        'save_button' => 'Opslaan',
        'invite_button' => 'Gebruiker uitnodigen',
        'permission_notice' => 'Bij het aanmaken of wijzigen van andere gebruikers kunnen alleen rechten worden geselecteerd die momenteel aan je account zijn toegewezen.',
        'email_label' => 'E-mailadres van gebruiker',
        'email_description' => 'Voer het e-mailadres in van de gebruiker die je als subgebruiker van deze server wilt uitnodigen.',
        'validation' => [
            'email_max' => 'E-mailadressen mogen niet langer zijn dan 191 tekens.',
            'email_invalid' => 'Een geldig e-mailadres is verplicht.',
        ],
    ],
    'remove' => [
        'title' => 'Deze subgebruiker verwijderen?',
        'confirm_button' => 'Ja, subgebruiker verwijderen',
        'body' => 'Weet je zeker dat je deze subgebruiker wilt verwijderen? Al zijn of haar toegang tot deze server wordt onmiddellijk ingetrokken.',
        'delete_aria' => 'Subgebruiker verwijderen',
    ],
];
