<?php

return [
    'location' => [
        'no_location_found' => 'Não foi possível encontrar um registo correspondente ao código curto indicado.',
        'ask_short' => 'Código curto da localização',
        'ask_long' => 'Descrição da localização',
        'created' => 'Nova localização (:name) criada com sucesso com o ID :id.',
        'deleted' => 'A localização pedida foi eliminada com sucesso.',
    ],
    'user' => [
        'search_users' => 'Introduza um nome de utilizador, ID de utilizador ou endereço de e-mail',
        'select_search_user' => 'ID do utilizador a eliminar (introduza \'0\' para pesquisar novamente)',
        'deleted' => 'Utilizador eliminado com sucesso do painel.',
        'confirm_delete' => 'Tem a certeza de que quer eliminar este utilizador do painel?',
        'no_users_found' => 'Não foram encontrados utilizadores para o termo de pesquisa indicado.',
        'multiple_found' => 'Foram encontradas várias contas para o utilizador indicado; não é possível eliminar um utilizador por causa da flag --no-interaction.',
        'ask_admin' => 'Este utilizador é administrador?',
        'ask_email' => 'Endereço de e-mail',
        'ask_username' => 'Nome de utilizador',
        'ask_name_first' => 'Nome próprio',
        'ask_name_last' => 'Apelido',
        'ask_password' => 'Palavra-passe',
        'ask_password_tip' => 'Se quiser criar uma conta com uma palavra-passe aleatória enviada por e-mail ao utilizador, volte a executar este comando (CTRL+C) e passe a flag `--no-password`.',
        'ask_password_help' => 'As palavras-passe devem ter pelo menos 8 caracteres e conter pelo menos uma letra maiúscula e um número.',
        '2fa_help_text' => [
            0 => 'Este comando desativa a autenticação de dois fatores na conta de um utilizador, se estiver ativa. Deve ser usado apenas como comando de recuperação de conta se o utilizador não conseguir aceder à conta.',
            1 => 'Se não era isto que queria fazer, prima CTRL+C para terminar este processo.',
        ],
        '2fa_disabled' => 'A autenticação de dois fatores foi desativada para :email.',
    ],
    'schedule' => [
        'output_line' => 'A enviar o job da primeira tarefa em `:schedule` (:hash).',
    ],
    'maintenance' => [
        'deleting_service_backup' => 'A eliminar o ficheiro de cópia de segurança do serviço :file.',
    ],
    'server' => [
        'rebuild_failed' => 'O pedido de reconstrução de ":name" (#:id) no nó ":node" falhou com o erro: :message',
        'reinstall' => [
            'failed' => 'O pedido de reinstalação de ":name" (#:id) no nó ":node" falhou com o erro: :message',
            'confirm' => 'Está prestes a reinstalar um grupo de servidores. Deseja continuar?',
        ],
        'power' => [
            'confirm' => 'Está prestes a executar a ação :action em :count servidores. Deseja continuar?',
            'action_failed' => 'O pedido de energia de ":name" (#:id) no nó ":node" falhou com o erro: :message',
        ],
    ],
    'environment' => [
        'mail' => [
            'ask_smtp_host' => 'Anfitrião SMTP (p. ex., smtp.gmail.com)',
            'ask_smtp_port' => 'Porta SMTP',
            'ask_smtp_username' => 'Nome de utilizador SMTP',
            'ask_smtp_password' => 'Palavra-passe SMTP',
            'ask_mailgun_domain' => 'Domínio Mailgun',
            'ask_mailgun_endpoint' => 'Endpoint Mailgun',
            'ask_mailgun_secret' => 'Segredo Mailgun',
            'ask_mandrill_secret' => 'Segredo Mandrill',
            'ask_postmark_username' => 'Chave API Postmark',
            'ask_driver' => 'Que driver deve ser usado para enviar e-mails?',
            'ask_mail_from' => 'Endereço de e-mail de origem das mensagens',
            'ask_mail_name' => 'Nome com que as mensagens devem aparecer',
            'ask_encryption' => 'Método de encriptação a usar',
        ],
    ],
];
