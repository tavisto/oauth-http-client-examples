<?php
# Set up phoauth autoloading
require __DIR__ . '/../lib/phoauth/autoload.php.dist';

# Set up ZF autoloading
$pathToZendFramework = __DIR__ . '/../lib/zf2/library';
set_include_path(get_include_path() . PATH_SEPARATOR . $pathToZendFramework);

$config = array(
    'Zend\Loader\StandardAutoloader' => array(
        'autoregister_zf' => true,
        'namespaces' => array(
            'ExampleClients' => __DIR__ . '/ExampleClients',
        ),
    ),
    'Zend\Loader\ClassMapAutoloader' => array(
        __DIR__ . '/../lib/zf2/autoload_classmap.php',
    ),
);
require_once 'Zend/Loader/AutoloaderFactory.php';
Zend\Loader\AutoloaderFactory::factory($config);
