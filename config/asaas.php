<?php
require_once __DIR__ . '/env-loader.php';

define('ASAAS_KEY',            getenv('ASAAS_KEY'));
define('ASAAS_ENV',            getenv('ASAAS_ENV') ?: 'production');
define('ASAAS_BASE_URL',       getenv('ASAAS_BASE_URL') ?: 'https://www.asaas.com/api/v3');
define('ASAAS_WEBHOOK_SECRET', getenv('ASAAS_WEBHOOK_SECRET'));
define('ASAAS_PAYMENT_LINK',   getenv('ASAAS_PAYMENT_LINK'));
define('ASAAS_PAYMENT_LINK_ID', getenv('ASAAS_PAYMENT_LINK_ID'));
