<?php

return [
    'title' => 'Definições do servidor',
    'missing' => 'Este servidor ainda não tem um server.properties. Arranque-o uma vez ou guarde aqui para criar o ficheiro.',
    'saved' => 'Definições guardadas. Aplicam-se no próximo arranque.',
    'saved_restart' => 'Definições guardadas. Reinicie o servidor para as aplicar.',
    'unsaved' => ':count alterações não guardadas',
    'reset' => 'Descartar',
    'save' => 'Guardar',
    'locked' => 'A porta, o IP e o RCON são geridos pelo painel e não podem ser alterados aqui.',
    'groups' => [
        'general' => 'Geral',
        'world' => 'Mundo',
        'access' => 'Acesso e segurança',
        'resource_pack' => 'Pacote de recursos',
        'other' => 'Outras definições',
    ],
    'options' => [
        'survival' => 'Sobrevivência',
        'creative' => 'Criativo',
        'adventure' => 'Aventura',
        'spectator' => 'Espectador',
        'peaceful' => 'Pacífico',
        'easy' => 'Fácil',
        'normal' => 'Normal',
        'hard' => 'Difícil',
        'flat' => 'Superplano',
        'large_biomes' => 'Biomas grandes',
        'amplified' => 'Amplificado',
    ],
    'server_list' => [
        'title' => 'Lista de servidores',
        'line1' => 'Primeira linha',
        'line2' => 'Segunda linha (opcional)',
        'codes_hint' => 'Os códigos de cor e formato começam com & (p. ex., &a verde, &l negrito, &r redefinir). Clique numa cor para a inserir no cursor. A pré-visualização mostra como os jogadores veem o servidor.',
        'icon_title' => 'Ícone do servidor',
        'icon_upload' => 'Enviar ícone',
        'icon_remove' => 'Remover ícone',
        'icon_hint' => 'PNG, JPG, GIF ou WebP. É recortado em quadrado e reduzido para 64×64 automaticamente. Aplica-se após reiniciar.',
        'icon_saved' => 'Ícone do servidor guardado. Aparece após o próximo reinício.',
        'icon_invalid' => 'Este ficheiro não é uma imagem legível.',
        'no_icon' => 'Sem ícone',
        'format' => [
            'l' => 'Negrito',
            'o' => 'Itálico',
            'n' => 'Sublinhado',
            'm' => 'Riscado',
            'k' => 'Mágico',
            'r' => 'Redefinir',
        ],
    ],
    'fields' => [
        'motd' => [
            'label' => 'Descrição do servidor (MOTD)',
            'description' => 'Mostrada na lista de servidores do modo multijogador.',
        ],
        'max-players' => [
            'label' => 'Máximo de jogadores',
            'description' => 'Quantos jogadores podem estar online ao mesmo tempo.',
        ],
        'gamemode' => [
            'label' => 'Modo de jogo',
            'description' => 'Modo de jogo para novos jogadores.',
        ],
        'difficulty' => [
            'label' => 'Dificuldade',
            'description' => 'Quão perigosos são os monstros e a fome.',
        ],
        'hardcore' => [
            'label' => 'Hardcore',
            'description' => 'Os jogadores são banidos depois de morrerem uma vez.',
        ],
        'pvp' => [
            'label' => 'PvP',
            'description' => 'Os jogadores podem causar dano uns aos outros.',
        ],
        'force-gamemode' => [
            'label' => 'Forçar modo de jogo',
            'description' => 'Os jogadores entram sempre no modo de jogo predefinido.',
        ],
        'allow-flight' => [
            'label' => 'Permitir voar',
            'description' => 'Necessário para alguns plugins e mods; caso contrário, os jogadores que voam são expulsos.',
        ],
        'level-name' => [
            'label' => 'Pasta do mundo',
            'description' => 'Nome da pasta do mundo a carregar ou criar.',
        ],
        'level-seed' => [
            'label' => 'Seed',
            'description' => 'Seed para mundos novos; vazio significa aleatório.',
        ],
        'level-type' => [
            'label' => 'Tipo de mundo',
            'description' => 'Usado apenas quando é gerado um mundo novo.',
        ],
        'allow-nether' => [
            'label' => 'Nether',
            'description' => 'Os jogadores podem viajar para o Nether.',
        ],
        'generate-structures' => [
            'label' => 'Estruturas',
            'description' => 'São geradas aldeias, templos e outras estruturas.',
        ],
        'spawn-monsters' => [
            'label' => 'Monstros',
            'description' => 'Aparecem monstros hostis.',
        ],
        'spawn-npcs' => [
            'label' => 'Aldeões',
            'description' => 'Aparecem aldeões.',
        ],
        'spawn-protection' => [
            'label' => 'Proteção do spawn',
            'description' => 'Raio à volta do spawn onde só os operadores podem construir (0 = desativada).',
        ],
        'view-distance' => [
            'label' => 'Distância de visualização',
            'description' => 'Chunks enviados aos jogadores. Valores mais baixos poupam memória e CPU.',
        ],
        'simulation-distance' => [
            'label' => 'Distância de simulação',
            'description' => 'Chunks à volta dos jogadores onde as coisas se movem e crescem.',
        ],
        'max-world-size' => [
            'label' => 'Limite do mundo',
            'description' => 'Raio máximo do mundo em blocos.',
        ],
        'white-list' => [
            'label' => 'Whitelist',
            'description' => 'Só os jogadores da whitelist podem entrar.',
        ],
        'enforce-whitelist' => [
            'label' => 'Impor whitelist',
            'description' => 'Expulsa os jogadores online que sejam removidos da whitelist.',
        ],
        'online-mode' => [
            'label' => 'Modo online',
            'description' => 'Verifica as contas na Mojang. Desative apenas atrás de um proxy como o Velocity ou o BungeeCord.',
        ],
        'enforce-secure-profile' => [
            'label' => 'Perfil de chat seguro',
            'description' => 'Os jogadores precisam de chaves de chat assinadas pela Mojang.',
        ],
        'enable-command-block' => [
            'label' => 'Blocos de comandos',
            'description' => 'Os blocos de comandos podem ser usados.',
        ],
        'op-permission-level' => [
            'label' => 'Nível de operador',
            'description' => 'Nível de permissão dos operadores (1-4).',
        ],
        'player-idle-timeout' => [
            'label' => 'Expulsão por AFK (minutos)',
            'description' => 'Expulsa jogadores inativos após este número de minutos (0 = nunca).',
        ],
        'resource-pack' => [
            'label' => 'URL do pacote de recursos',
            'description' => 'Ligação de transferência direta de um pacote de recursos (zip).',
        ],
        'require-resource-pack' => [
            'label' => 'Exigir pacote de recursos',
            'description' => 'Os jogadores que recusarem o pacote são desligados.',
        ],
    ],
];
