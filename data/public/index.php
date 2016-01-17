<?php
// define a working directory
define('APP_PATH', __DIR__); // PHP v5.3+
require APP_PATH . '/../vendor/autoload.php';

date_default_timezone_set('UTC');
session_start();

// Prepare app
$app = new \SlimController\Slim(array(
    'templates.path' => APP_PATH . '/../templates',
    'controller.class_prefix' => '\\dealdb\\Controller',
    'controller.method_suffix' => 'Action',
    'controller.template_suffix' => 'twig'
));

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath(APP_PATH . '/../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

// Define routes
$app->addRoutes(array(
    '/' => 'Stat:index',
    '/clearQiwi' => 'Stat:clearQiwi',
    '/add' => [
        'get' => 'Deal:add',
        'post' => 'Deal:addDeal'
    ],
    '/all' => 'Deal:all',
    '/export' => 'Deal:export'
));

// Run app
$app->run();
