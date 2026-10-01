<?php

return [
    'password' => 'Hasła muszą mieć co najmniej sześć znaków i zgadzać się z potwierdzeniem.',
    'reset' => 'Twoje hasło zostało zresetowane!',
    'sent' => 'Wysłaliśmy e-mailem link do zresetowania hasła!',
    'token' => 'Ten token resetowania hasła jest nieprawidłowy.',
    'user' => 'Nie możemy znaleźć użytkownika o tym adresie e-mail.',
    'generated_password_sent' => 'Jeśli istnieje konto z tym adresem e-mail lub nazwą użytkownika, wysłaliśmy na nie e-mail z linkiem potwierdzającym.',
    'confirm_mail' => [
        'subject' => 'Potwierdź reset hasła',
        'intro' => 'Ktoś poprosił o nowe hasło do Twojego konta (:username).',
        'button' => 'Wyślij mi nowe hasło',
        'expires' => 'Link jest ważny przez 60 minut. Po kliknięciu otrzymasz nowe hasło e-mailem.',
        'ignore' => 'Jeśli to nie Ty, zignoruj tę wiadomość; Twoje hasło się nie zmieni.',
    ],
    'confirm_page' => [
        'sent_title' => 'Wysłano nowe hasło',
        'sent_text' => 'Wysłaliśmy Ci nowe hasło e-mailem. Po zalogowaniu wybierzesz własne hasło.',
        'invalid_title' => 'Link jest już nieważny',
        'invalid_text' => 'Ten link wygasł lub został już użyty. Poproś o nowy na stronie logowania.',
        'login' => 'Do logowania',
    ],
];
