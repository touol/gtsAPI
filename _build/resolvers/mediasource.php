<?php
/**
 * Ресолвер для создания источника медиа для галереи файлов gtsAPIFile
 *
 * @package gtsapi
 * @subpackage build
 */

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */

if ($transport->xpdo) {
    $modx =& $transport->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            
            $modx->log(modX::LOG_LEVEL_INFO, 'Создание источника медиа для gtsAPIFile...');
            
            // Проверяем, существует ли уже источник медиа
            $mediaSource = $modx->getObject('sources.modMediaSource', array('name' => 'gtsAPIFile'));
            
            if (!$mediaSource) {
                // Создаем новый источник медиа
                $mediaSource = $modx->newObject('sources.modMediaSource');
                $mediaSource->fromArray(array(
                    'name' => 'gtsAPIFile',
                    'description' => 'Источник медиа для галереи файлов gtsAPIFile',
                    'class_key' => 'sources.modFileMediaSource',
                    'properties' => array(
                        'basePath' => array(
                            'name' => 'basePath',
                            'desc' => 'Базовый путь к файлам',
                            'type' => 'textfield',
                            'value' => 'assets/uploads/gtsapi/',
                            'lexicon' => 'core:source'
                        ),
                        'baseUrl' => array(
                            'name' => 'baseUrl',
                            'desc' => 'Базовый URL для файлов',
                            'type' => 'textfield',
                            'value' => 'assets/uploads/gtsapi/',
                            'lexicon' => 'core:source'
                        ),
                        'basePathRelative' => array(
                            'name' => 'basePathRelative',
                            'desc' => 'Относительный базовый путь',
                            'type' => 'combo-boolean',
                            'value' => true,
                            'lexicon' => 'core:source'
                        ),
                        'baseUrlRelative' => array(
                            'name' => 'baseUrlRelative',
                            'desc' => 'Относительный базовый URL',
                            'type' => 'combo-boolean',
                            'value' => true,
                            'lexicon' => 'core:source'
                        ),
                        'allowedFileTypes' => array(
                            'name' => 'allowedFileTypes',
                            'desc' => 'Разрешенные типы файлов',
                            'type' => 'textfield',
                            'value' => 'jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,7z,mp3,mp4,avi,mov,wmv',
                            'lexicon' => 'core:source'
                        ),
                        'imageExtensions' => array(
                            'name' => 'imageExtensions',
                            'desc' => 'Расширения изображений',
                            'type' => 'textfield',
                            'value' => 'jpg,jpeg,png,gif,webp,svg',
                            'lexicon' => 'core:source'
                        ),
                        'thumbnailType' => array(
                            'name' => 'thumbnailType',
                            'desc' => 'Тип миниатюр',
                            'type' => 'list',
                            'value' => 'jpg',
                            'options' => array(
                                array('text' => 'JPG', 'value' => 'jpg'),
                                array('text' => 'PNG', 'value' => 'png'),
                                array('text' => 'GIF', 'value' => 'gif')
                            ),
                            'lexicon' => 'core:source'
                        ),
                        'thumbnailQuality' => array(
                            'name' => 'thumbnailQuality',
                            'desc' => 'Качество миниатюр',
                            'type' => 'numberfield',
                            'value' => 90,
                            'lexicon' => 'core:source'
                        ),
                        'skipFiles' => array(
                            'name' => 'skipFiles',
                            'desc' => 'Пропускать файлы',
                            'type' => 'textfield',
                            'value' => '.svn,.git,_notes,nbproject,.idea,.DS_Store',
                            'lexicon' => 'core:source'
                        ),
                        'imageThumbnails' => array(
                            'name' => 'imageThumbnails',
                            'desc' => 'Настройки миниатюр изображений',
                            'type' => 'textarea',
                            'value' => '[{"w":120,"h":90,"q":90,"zc":1,"bg":"fff","f":"jpg"},{"w":300,"h":200,"q":85,"zc":1,"bg":"fff","f":"jpg"}]',
                            'lexicon' => 'core:source'
                        ),
                        'thumbnailName' => array(
                            'name' => 'thumbnailName',
                            'desc' => 'Шаблон имени миниатюры',
                            'type' => 'textfield',
                            'value' => '{name}.{rand}.{w}.{h}.{ext}',
                            'lexicon' => 'core:source'
                        )
                    )
                ), '', true, true);
                
                if ($mediaSource->save()) {
                    $modx->log(modX::LOG_LEVEL_INFO, 'Источник медиа "gtsAPIFile" успешно создан с ID: ' . $mediaSource->get('id'));
                    
                    // Создаем директорию для файлов
                    $basePath = MODX_BASE_PATH . 'assets/uploads/gtsapi/';
                    if (!is_dir($basePath)) {
                        if (mkdir($basePath, 0755, true)) {
                            $modx->log(modX::LOG_LEVEL_INFO, 'Создана директория: ' . $basePath);
                            
                            // Создаем поддиректории
                            $subDirs = array('gallery', 'documents', 'attachments', 'thumbnails');
                            foreach ($subDirs as $subDir) {
                                $subDirPath = $basePath . $subDir . '/';
                                if (!is_dir($subDirPath)) {
                                    if (mkdir($subDirPath, 0755, true)) {
                                        $modx->log(modX::LOG_LEVEL_INFO, 'Создана поддиректория: ' . $subDirPath);
                                    } else {
                                        $modx->log(modX::LOG_LEVEL_ERROR, 'Не удалось создать поддиректорию: ' . $subDirPath);
                                    }
                                }
                            }
                            
                            // Создаем .htaccess для безопасности
                            $htaccessContent = "# Защита от выполнения PHP файлов\n";
                            $htaccessContent .= "<Files *.php>\n";
                            $htaccessContent .= "    Order Deny,Allow\n";
                            $htaccessContent .= "    Deny from all\n";
                            $htaccessContent .= "</Files>\n\n";
                            $htaccessContent .= "# Разрешить доступ к изображениям и документам\n";
                            $htaccessContent .= "<FilesMatch \"\\.(jpg|jpeg|png|gif|webp|svg|pdf|doc|docx|xls|xlsx|txt|zip|rar|mp3|mp4)$\">\n";
                            $htaccessContent .= "    Order Allow,Deny\n";
                            $htaccessContent .= "    Allow from all\n";
                            $htaccessContent .= "</FilesMatch>\n";
                            
                            file_put_contents($basePath . '.htaccess', $htaccessContent);
                            $modx->log(modX::LOG_LEVEL_INFO, 'Создан файл .htaccess для безопасности');
                            
                        } else {
                            $modx->log(modX::LOG_LEVEL_ERROR, 'Не удалось создать директорию: ' . $basePath);
                        }
                    } else {
                        $modx->log(modX::LOG_LEVEL_INFO, 'Директория уже существует: ' . $basePath);
                    }
                    
                    // Обновляем системную настройку для источника медиа по умолчанию для gtsAPIFile
                    $setting = $modx->getObject('modSystemSetting', array('key' => 'gtsapi_default_media_source'));
                    if (!$setting) {
                        $setting = $modx->newObject('modSystemSetting');
                        $setting->fromArray(array(
                            'key' => 'gtsapi_default_media_source',
                            'value' => $mediaSource->get('id'),
                            'xtype' => 'modx-combo-source',
                            'namespace' => 'gtsapi',
                            'area' => 'file_management',
                            'editedon' => date('Y-m-d H:i:s')
                        ), '', true, true);
                        
                        if ($setting->save()) {
                            $modx->log(modX::LOG_LEVEL_INFO, 'Создана системная настройка gtsapi_default_media_source');
                        }
                    } else {
                        $setting->set('value', $mediaSource->get('id'));
                        $setting->save();
                        $modx->log(modX::LOG_LEVEL_INFO, 'Обновлена системная настройка gtsapi_default_media_source');
                    }
                    
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Не удалось создать источник медиа "gtsAPIFile"');
                }
            } else {
                $modx->log(modX::LOG_LEVEL_INFO, 'Источник медиа "gtsAPIFile" уже существует');
                
                // Обновляем свойства существующего источника
                $properties = $mediaSource->get('properties');
                
                // Добавляем новые свойства, если их нет
                $newProperties = array(
                    'imageThumbnails' => '[{"w":120,"h":90,"q":90,"zc":1,"bg":"fff","f":"jpg"},{"w":300,"h":200,"q":85,"zc":1,"bg":"fff","f":"jpg"}]',
                    'thumbnailName' => '{name}.{rand}.{w}.{h}.{ext}',
                    'thumbnailType' => 'jpg',
                    'thumbnailQuality' => 90
                );
                
                $updated = false;
                foreach ($newProperties as $key => $value) {
                    if (!isset($properties[$key])) {
                        $properties[$key] = array(
                            'name' => $key,
                            'desc' => $key,
                            'type' => 'textfield',
                            'value' => $value,
                            'lexicon' => 'core:source'
                        );
                        $updated = true;
                    }
                }
                
                if ($updated) {
                    $mediaSource->set('properties', $properties);
                    if ($mediaSource->save()) {
                        $modx->log(modX::LOG_LEVEL_INFO, 'Обновлены свойства источника медиа "gtsAPIFile"');
                    }
                }
            }
            
            break;
            
        case xPDOTransport::ACTION_UNINSTALL:
            $modx->log(modX::LOG_LEVEL_INFO, 'Удаление источника медиа gtsAPIFile...');
            
            // Удаляем источник медиа
            $mediaSource = $modx->getObject('sources.modMediaSource', array('name' => 'gtsAPIFile'));
            if ($mediaSource) {
                if ($mediaSource->remove()) {
                    $modx->log(modX::LOG_LEVEL_INFO, 'Источник медиа "gtsAPIFile" удален');
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Не удалось удалить источник медиа "gtsAPIFile"');
                }
            }
            
            // Удаляем системную настройку
            $setting = $modx->getObject('modSystemSetting', array('key' => 'gtsapi_default_media_source'));
            if ($setting) {
                if ($setting->remove()) {
                    $modx->log(modX::LOG_LEVEL_INFO, 'Удалена системная настройка gtsapi_default_media_source');
                }
            }
            
            // Примечание: директории и файлы не удаляются автоматически для безопасности данных
            $modx->log(modX::LOG_LEVEL_INFO, 'Директория assets/uploads/gtsapi/ и файлы сохранены для безопасности данных');
            
            break;
    }
}

return true;
