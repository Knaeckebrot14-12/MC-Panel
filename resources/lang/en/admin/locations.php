<?php

return [
    'title' => 'Locations',
    'breadcrumb_locations' => 'Locations',
    'index' => [
        'heading' => 'Locations',
        'subheading' => 'All locations that nodes can be assigned to for easier categorization.',
        'list_heading' => 'Location List',
        'create_new_button' => 'Create New',
        'table' => [
            'id' => 'ID',
            'short_code' => 'Short Code',
            'description' => 'Description',
            'nodes' => 'Nodes',
            'servers' => 'Servers',
        ],
        'modal' => [
            'close_aria' => 'Close',
            'title' => 'Create Location',
            'short_code_label' => 'Short Code',
            'short_code_description' => 'A short identifier used to distinguish this location from others. Must be between 1 and 60 characters, for example, :example.',
            'description_label' => 'Description',
            'description_description' => 'A longer description of this location. Must be less than 191 characters.',
            'cancel_button' => 'Cancel',
            'create_button' => 'Create',
        ],
    ],
    'view' => [
        'title' => 'Locations → View → :location',
        'details_heading' => 'Location Details',
        'short_code_label' => 'Short Code',
        'description_label' => 'Description',
        'save_button' => 'Save',
        'nodes_heading' => 'Nodes',
        'table' => [
            'id' => 'ID',
            'name' => 'Name',
            'fqdn' => 'FQDN',
            'servers' => 'Servers',
        ],
    ],
];
