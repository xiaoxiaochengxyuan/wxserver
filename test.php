<?php
use Wx\Wxserver\Sdk\WXApi;
require_once __DIR__.'/vendor/autoload.php';
$users = WXApi::instance()->load('user')->getAll();
print_r($users);