<?php

return array(
    'modules' => array(
        'Infrastructure',
        'Opg', // placed last to be able to override infrastructure configuration
		'Administration',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            'config/autoload/{,*.}{local}.php',
        ),
        'module_paths' => array(
            './module',
            './vendor',
        ),
    ),
);
