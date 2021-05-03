<?php

// Register routes
$this->bindClass('Content\\Controller\\Collection', '/content/collection');
$this->bindClass('Content\\Controller\\Models', '/content/models');
$this->bindClass('Content\\Controller\\Content', '/content');

$this->helper('menus')->addLink('modules', [
    'label'  => 'Content',
    'icon'   => 'content:icon.svg',
    'route'  => '/content',
    'active' => false
]);
