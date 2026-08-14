<?php
/**
 * Регистрирует собственные таблицы gtsAPI (gtsapipackages.json) — тот же резолвер,
 * что gtsAPI кладёт в каждый свой пакет, только для самого себя.
 *
 * Имя начинается с z намеренно: резолверы выполняются в алфавитном порядке,
 * а записывать gtsAPITable можно лишь после того, как tables.php создаст
 * сами таблицы. На чистой установке иначе писать было бы некуда.
 *
 * @var xPDOTransport $transport
 * @var array $options
 * @var modX $modx
 */
if (!$transport->xpdo) {
    return true;
}

$resolver = MODX_CORE_PATH . 'components/gtsapi/resolvers/gtsapipackages.php';
if (!is_file($resolver)) {
    $transport->xpdo->log(modX::LOG_LEVEL_ERROR,
        '[gtsAPI] не найден резолвер таблиц: ' . $resolver);

    return true;
}

return include $resolver;
