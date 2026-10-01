<?php

return [
    'title' => 'Versão',
    'unsupported' => 'Este tipo de servidor não pode mudar a versão do Minecraft aqui.',
    'current_title' => 'Instalado agora',
    'current_unknown' => 'Ainda não instalado por esta página (está em uso a versão da configuração do servidor).',
    'install_title' => 'Instalar :name',
    'no_versions' => 'Não há versões disponíveis no momento.',
    'version_label' => 'Versão',
    'install_button' => 'Instalar',
    'java_hint' => 'Precisa de Java :java; a imagem Java adequada é escolhida automaticamente.',
    'installing' => 'Instalando… o servidor é parado e a nova versão baixada. Isso pode levar um minuto.',
    'confirm' => 'Instalar :name :version? O servidor será parado e o jar substituído. Mundos, plugins e configurações são mantidos.',
    'confirm_downgrade' => 'Esta versão é mais antiga que a instalada. Mundos do Minecraft geralmente não abrem em versões mais antigas e podem ser danificados. Faça um backup antes! Instalar :name :version mesmo assim?',
    'confirm_yes' => 'Sim, instalar',
    'confirm_no' => 'Cancelar',
    'backup_hint' => 'Dica: crie um backup antes de mudar para outro software ou versão.',
    'installed' => ':name :version foi instalado (Java :java). Inicie o servidor para usá-lo.',
    'types' => [
        'paper' => [
            'name' => 'Paper',
            'description' => 'Rápido, com plugins (Bukkit/Spigot)',
        ],
        'purpur' => [
            'name' => 'Purpur',
            'description' => 'Paper com muitas opções extras',
        ],
        'folia' => [
            'name' => 'Folia',
            'description' => 'Paper para servidores muito grandes (multithread)',
        ],
        'fabric' => [
            'name' => 'Fabric',
            'description' => 'Carregador de mods leve',
        ],
        'vanilla' => [
            'name' => 'Vanilla',
            'description' => 'O servidor original da Mojang',
        ],
        'velocity' => [
            'name' => 'Velocity',
            'description' => 'Proxy que conecta vários servidores',
        ],
    ],
    'errors' => [
        'unsupported' => 'Este servidor não pode mudar de versão (sua inicialização não usa um jar).',
        'unknown_version' => 'Esta versão não está disponível.',
        'unknown_type' => 'Software de servidor desconhecido.',
        'download' => 'Não foi possível baixar a nova versão. Tente novamente mais tarde.',
        'no_build' => 'Ainda não há download para esta versão.',
        'still_running' => 'Não foi possível parar o servidor. Pare-o e tente novamente.',
        'api' => 'Não foi possível carregar a lista de versões. Tente novamente mais tarde.',
        'busy' => 'Uma versão já está sendo instalada neste servidor.',
        'backup_full' => 'O limite de backups foi atingido, então não foi possível fazer um backup antes. Exclua um backup ou troque sem backup.',
        'backup_throttled' => 'Muitos backups em pouco tempo. Aguarde um momento e tente novamente.',
        'backup_failed' => 'O backup falhou, então nada foi alterado. Tente novamente ou troque sem backup.',
        'backup_timeout' => 'O backup está demorando mais que o esperado e continua; nada foi alterado. Tente novamente quando terminar.',
    ],
    'backup_first' => 'Criar um backup antes',
    'backup_full_note' => 'A lista de backups está cheia. Exclua um backup primeiro ou troque sem backup.',
    'installing_backup' => 'Criando o backup e depois instalando… o servidor fica parado nesse meio tempo. Pode levar alguns minutos.',
];
