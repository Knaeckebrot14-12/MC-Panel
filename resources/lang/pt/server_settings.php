<?php

return [
    'title' => 'Configurações',
    'sftp' => [
        'heading' => 'Detalhes do SFTP',
        'server_address_label' => 'Endereço do servidor',
        'username_label' => 'Nome de usuário',
        'password_notice' => 'Sua senha SFTP é a mesma que você usa para acessar este painel.',
        'launch_button' => 'Abrir SFTP',
    ],
    'debug' => [
        'heading' => 'Informações de depuração',
        'node_label' => 'Nó',
        'server_id_label' => 'ID do servidor',
    ],
    'rename' => [
        'heading' => 'Alterar detalhes do servidor',
        'name_label' => 'Nome do servidor',
        'description_label' => 'Descrição do servidor',
        'save_button' => 'Salvar',
    ],
    'reinstall' => [
        'heading' => 'Reinstalar servidor',
        'disabled_notice' => 'A reinstalação deste servidor foi desativada porque ele está configurado para ignorar o script de instalação do seu egg. Se quiser reinstalá-lo, entre em contato com um administrador do servidor.',
        'body' => 'Reinstalar seu servidor o parará e executará novamente o script de instalação que o configurou inicialmente.',
        'body_warning' => 'Alguns arquivos podem ser excluídos ou modificados durante este processo, faça backup dos seus dados antes de continuar.',
        'reinstall_button' => 'Reinstalar servidor',
        'confirm_title' => 'Confirmar reinstalação do servidor',
        'confirm_button' => 'Sim, reinstalar o servidor',
        'confirm_body' => 'Seu servidor será parado e alguns arquivos podem ser excluídos ou modificados durante este processo. Tem certeza de que deseja continuar?',
        'success_message' => 'Seu servidor iniciou o processo de reinstalação.',
    ],
];
