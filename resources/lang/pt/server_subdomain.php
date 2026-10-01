<?php

return [
    'title' => 'Subdomínio',
    'current' => 'Os jogadores podem entrar com:',
    'name_placeholder' => 'meuservidor',
    'change' => 'Alterar',
    'create' => 'Criar',
    'remove' => 'Remover',
    'hint' => '3–32 caracteres: letras minúsculas, números e hífens. Pode levar alguns minutos até o endereço funcionar em todo lugar.',
    'saved' => ':fqdn agora aponta para este servidor.',
    'errors' => [
        'disabled' => 'Subdomínios não estão disponíveis.',
        'invalid_name' => 'Este nome não é permitido. Use 3–32 letras minúsculas, números ou hífens.',
        'invalid_domain' => 'Este domínio não está disponível.',
        'taken' => 'Este subdomínio já está em uso.',
        'no_ip' => 'Não foi possível determinar o endereço público deste servidor.',
        'cloudflare' => 'Não foi possível criar o registro DNS: :error',
        'no_zone' => 'O domínio :domain não está configurado corretamente. Contate o suporte.',
    ],
];
