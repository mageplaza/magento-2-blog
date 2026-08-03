<?php
/**
 * Bootstrap for Mageplaza Blog standalone unit tests.
 * Registers Magento autoloader + this module's own namespace.
 */

declare(strict_types=1);

// Use Magento's vendor autoloader so all Magento classes are available
$magentoRoot = dirname(__DIR__, 3); // all-m2-extension/magento-2-blog -> magento root

$autoload = $magentoRoot . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    // fallback: 2 levels up (when module is inside app/code)
    $autoload = dirname(__DIR__, 5) . '/vendor/autoload.php';
}

if (!file_exists($autoload)) {
    throw new \RuntimeException('Cannot find Magento vendor/autoload.php. Check paths in Test/bootstrap.php');
}

$loader = require $autoload;

// Register this module's namespace so PHPUnit can find its classes
// src is the module root (where Block/, Helper/, etc. live)
$moduleRoot = dirname(__DIR__);
$loader->addPsr4('Mageplaza\\Blog\\', $moduleRoot . '/');
$loader->addPsr4('Mageplaza\\Blog\\Test\\', $moduleRoot . '/Test/');
