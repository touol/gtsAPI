<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            if ($modx instanceof modX) {
                $modx->addExtensionPackage('skladnaryd', '[[++core_path]]components/skladnaryd/model/');
            }
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            if ($modx instanceof modX) {
                $modx->removeExtensionPackage('skladnaryd');
            }
            break;
    }
}
return true;