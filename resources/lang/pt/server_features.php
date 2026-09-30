<?php

return [
    'gsl_token' => [
        'title' => 'Token GSL inválido!',
        'body_1' => 'Parece que seu Gameserver Login Token (token GSL) é inválido ou expirou.',
        'body_2' => 'Você pode gerar um novo e digitá-lo abaixo, ou deixar o campo em branco para removê-lo por completo.',
        'field_label' => 'Token GSL',
        'field_description' => 'Acesse https://steamcommunity.com/dev/managegameservers para gerar um token.',
        'update_button' => 'Atualizar token GSL',
    ],
    'hytale_oauth' => [
        'title' => 'Autenticação necessária',
        'body' => 'Você precisa se autenticar com sua conta Hytale para baixar ou atualizar os arquivos do servidor. Faça login para continuar.',
        'cancel_button' => 'Cancelar',
        'login_button' => 'Entrar',
    ],
    'pid_limit' => [
        'admin_title' => 'Limite de memória ou processos atingido...',
        'admin_body_1' => 'Este servidor atingiu o limite máximo de processos ou de memória.',
        'admin_body_2' => 'Aumentar <0>container_pid_limit</0> na configuração do wings, <1>config.yml</1>, pode ajudar a resolver este problema.',
        'admin_note' => 'Observação: o Wings precisa ser reiniciado para que as alterações no arquivo de configuração tenham efeito',
        'user_title' => 'Possível limite de recursos atingido...',
        'user_body' => 'Este servidor está tentando usar mais recursos do que os alocados. Entre em contato com o administrador e informe o erro abaixo.',
        'close_button' => 'Fechar',
    ],
    'steam_disk_space' => [
        'title' => 'Sem espaço em disco disponível...',
        'admin_body_1' => 'Este servidor ficou sem espaço em disco disponível e não pode concluir a instalação ou atualização.',
        'admin_body_2' => 'Verifique se a máquina tem espaço em disco suficiente digitando <0>df -h</0> na máquina que hospeda este servidor. Exclua arquivos ou aumente o espaço disponível para resolver o problema.',
        'user_body' => 'Este servidor ficou sem espaço em disco disponível e não pode concluir a instalação ou atualização. Entre em contato com o(s) administrador(es) e informe o problema de espaço em disco.',
        'close_button' => 'Fechar',
    ],
    'eula' => [
        'title' => 'Aceitar o EULA do Minecraft®',
        'body_prefix' => 'Ao pressionar "Aceito" abaixo, você indica que concorda com o',
        'body_suffix' => '.',
        'link_text' => 'EULA do Minecraft®',
        'cancel_button' => 'Cancelar',
        'accept_button' => 'Aceito',
    ],
    'java_version' => [
        'title' => 'Versão do Java não suportada',
        'body' => 'Este servidor está executando uma versão do Java não suportada e não pode ser iniciado.',
        'body_select_notice' => ' Selecione uma versão suportada na lista abaixo para continuar iniciando o servidor.',
        'cancel_button' => 'Cancelar',
        'update_button' => 'Atualizar imagem Docker',
    ],
];
