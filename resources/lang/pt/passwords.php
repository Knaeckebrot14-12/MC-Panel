<?php

return [
    'password' => 'As senhas devem ter pelo menos seis caracteres e coincidir com a confirmação.',
    'reset' => 'Sua senha foi redefinida!',
    'sent' => 'Enviamos por e-mail o link para redefinir sua senha!',
    'token' => 'Este token de redefinição de senha é inválido.',
    'user' => 'Não encontramos um usuário com esse endereço de e-mail.',
    'generated_password_sent' => 'Se existir uma conta com este e-mail ou nome de usuário, enviamos a ela um e-mail com um link de confirmação.',
    'confirm_mail' => [
        'subject' => 'Confirme a redefinição da sua senha',
        'intro' => 'Alguém pediu uma nova senha para sua conta (:username).',
        'button' => 'Enviar uma nova senha',
        'expires' => 'O link vale por 60 minutos. Depois do clique você recebe uma nova senha por e-mail.',
        'ignore' => 'Se não foi você, ignore este e-mail; sua senha continua a mesma.',
    ],
    'confirm_page' => [
        'sent_title' => 'Nova senha enviada',
        'sent_text' => 'Enviamos uma nova senha por e-mail. Depois de entrar você escolhe sua própria senha.',
        'invalid_title' => 'Link não é mais válido',
        'invalid_text' => 'Este link expirou ou já foi usado. Solicite um novo na página de login.',
        'login' => 'Ir para o login',
    ],
];
