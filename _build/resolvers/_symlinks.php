<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/SkladNaryd/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/skladnaryd')) {
            $cache->deleteTree(
                $dev . 'assets/components/skladnaryd/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/skladnaryd/', $dev . 'assets/components/skladnaryd');
        }
        if (!is_link($dev . 'core/components/skladnaryd')) {
            $cache->deleteTree(
                $dev . 'core/components/skladnaryd/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/skladnaryd/', $dev . 'core/components/skladnaryd');
        }
    }
}

return true;