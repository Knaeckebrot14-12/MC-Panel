<?php

return [
    'title' => 'Usuários',
    'empty' => 'Parece que você não tem subusuários.',
    'new_user_button' => 'Novo usuário',
    'row' => [
        'two_factor_label' => '2FA ativada',
        'permissions_label' => 'Permissões',
        'edit_aria' => 'Editar subusuário',
    ],
    'edit_modal' => [
        'title_modify' => 'Modificar permissões de :email',
        'title_view' => 'Ver permissões de :email',
        'title_create' => 'Criar novo subusuário',
        'save_button' => 'Salvar',
        'invite_button' => 'Convidar usuário',
        'permission_notice' => 'Somente as permissões atualmente atribuídas à sua conta podem ser selecionadas ao criar ou modificar outros usuários.',
        'email_label' => 'E-mail do usuário',
        'email_description' => 'Digite o endereço de e-mail do usuário que você deseja convidar como subusuário deste servidor.',
        'validation' => [
            'email_max' => 'Os endereços de e-mail não devem exceder 191 caracteres.',
            'email_invalid' => 'É necessário informar um endereço de e-mail válido.',
        ],
    ],
    'remove' => [
        'title' => 'Excluir este subusuário?',
        'confirm_button' => 'Sim, remover subusuário',
        'body' => 'Tem certeza de que deseja remover este subusuário? Todo o acesso dele a este servidor será revogado imediatamente.',
        'delete_aria' => 'Excluir subusuário',
    ],
];
