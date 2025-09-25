<?php

/**
 * Vue SSR Renderer с использованием Node.js
 * Рендерит Vue.js компоненты на сервере через Node.js
 */
class VueNodeSSRRenderer {
    
    private $modx;
    private $cache;
    private $enableCache = true;
    private $nodeJsPath = 'node';
    private $npmPath = 'npm';
    
    public function __construct(modX &$modx, $enableCache = true) {
        $this->modx = $modx;
        $this->enableCache = $enableCache;
        $this->initCache();
        $this->detectNodePaths();
        $this->checkNodeJs();
    }
    
    /**
     * Автоматическое определение путей к Node.js и npm
     */
    private function detectNodePaths() {
        // Возможные пути для Windows
        $possibleNodePaths = [
            'node',
            '"C:\\Program Files\\nodejs\\node.exe"',
            '"C:\\Program Files (x86)\\nodejs\\node.exe"',
            'C:\\nodejs\\node.exe'
        ];
        
        $possibleNpmPaths = [
            'npm',
            '"C:\\Program Files\\nodejs\\npm.cmd"',
            '"C:\\Program Files (x86)\\nodejs\\npm.cmd"',
            'C:\\nodejs\\npm.cmd'
        ];
        
        // Проверяем Node.js
        foreach ($possibleNodePaths as $path) {
            $output = [];
            $return_var = 0;
            exec($path . ' --version 2>nul', $output, $return_var);
            if ($return_var === 0) {
                $this->nodeJsPath = $path;
                break;
            }
        }
        
        // Проверяем npm
        foreach ($possibleNpmPaths as $path) {
            $output = [];
            $return_var = 0;
            exec($path . ' --version 2>nul', $output, $return_var);
            if ($return_var === 0) {
                $this->npmPath = $path;
                break;
            }
        }
    }
    
    /**
     * Проверка доступности Node.js
     */
    private function checkNodeJs() {
        $output = [];
        $return_var = 0;
        exec($this->nodeJsPath . ' --version 2>&1', $output, $return_var);
        
        if ($return_var !== 0) {
            throw new Exception('Node.js not found. Please install Node.js to use SSR functionality.'.$return_var);
        }
        
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Node.js found: ' . implode(' ', $output));
    }
    
    /**
     * Инициализация кэша
     */
    private function initCache() {
        if ($this->enableCache) {
            $cachePath = $this->modx->getOption('core_path') . 'components/gtsapi/classes/SSRCache.class.php';
            if (file_exists($cachePath)) {
                require_once $cachePath;
                $this->cache = new SSRCache($this->modx);
            }
        }
    }
    
    /**
     * Основной метод рендеринга Vue компонента
     */
    public function render($app, $config = [], $componentCode = '') {
        try {
            // Проверяем кэш
            if ($this->enableCache && $this->cache) {
                $cachedHtml = $this->cache->get($app, $config);
                if ($cachedHtml !== null) {
                    $this->modx->log(modX::LOG_LEVEL_INFO, 'SSR: Using cached result for app: ' . $app);
                    return $cachedHtml;
                }
            }
            
            // Если код компонента не передан, пытаемся загрузить из файла
            if (empty($componentCode)) {
                $componentCode = $this->loadComponentCode($app);
            }
            
            // Создаем временные файлы для Node.js SSR
            $tempDir = $this->createTempFiles($app, $config, $componentCode);
            
            // Выполняем SSR через Node.js
            $html = $this->executeNodeSSR($tempDir);
            
            // Очищаем временные файлы
            $this->cleanupTempFiles($tempDir);
            
            // Сохраняем в кэш
            if ($this->enableCache && $this->cache && !empty($html)) {
                $this->cache->set($app, $config, $html);
                $this->modx->log(modX::LOG_LEVEL_INFO, 'SSR: Cached result for app: ' . $app);
            }
            
            return $html;
            
        } catch (Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'SSR rendering failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Загрузка кода компонента из файла
     */
    private function loadComponentCode($app) {
        $assetsPath = $this->modx->getOption('assets_path') . 'components/' . strtolower($app) . '/web/js/';
        $mainJsPath = $assetsPath . 'entry-server.js';
        
        if (file_exists($mainJsPath)) {
            return file_get_contents($mainJsPath);
        }
        
        // Возвращаем базовый компонент если файл не найден
        return $this->getDefaultComponent();
    }
    
    /**
     * Получение baseURL сервера
     */
    private function getServerBaseURL() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
        $port = '';
        if (($protocol === 'http' && $_SERVER['SERVER_PORT'] != 80) || 
            ($protocol === 'https' && $_SERVER['SERVER_PORT'] != 443)) {
            // Проверяем, не содержит ли HTTP_HOST уже порт
            if (strpos($host, ':') === false) {
                $port = ':'.$_SERVER['SERVER_PORT'];
            }
        }
        return $protocol.'://'.$host.$port.'/';
    }

    /**
     * Создание временных файлов для Node.js SSR
     */
    private function createTempFiles($app, $config, $componentCode) {
        $tempDir = sys_get_temp_dir() . '/vue_ssr_' . uniqid();
        if (!mkdir($tempDir, 0755, true)) {
            throw new Exception('Failed to create temp directory: ' . $tempDir);
        }
        
        // Создаем package.json
        $packageJson = [
            'name' => 'vue-ssr-temp',
            'version' => '1.0.0',
            'type' => 'module',
            'dependencies' => [
                'vue' => '^3.4.0',
                '@vue/server-renderer' => '^3.4.0',
            ],
            'browser' => false
        ];
        file_put_contents($tempDir . '/package.json', json_encode($packageJson, JSON_PRETTY_PRINT));
        
        // Создаем компонент
        $componentPath = $tempDir . '/component.js';
        file_put_contents($componentPath, $componentCode);
        
        // Создаем данные с добавлением baseURL
        $configWithBaseURL = $config;
        $configWithBaseURL['SSR_BASE_URL'] = $this->getServerBaseURL();
        $dataPath = $tempDir . '/data.json';
        file_put_contents($dataPath, json_encode($configWithBaseURL));
        
        // Создаем SSR скрипт
        $ssrScript = $this->createNodeSSRScript($app);
        file_put_contents($tempDir . '/ssr.js', $ssrScript);
        
        return $tempDir;
    }
    
    /**
     * Создание Node.js SSR скрипта
     */
    private function createNodeSSRScript($app) {
        return '
import fs from "fs";
import path from "path";

async function renderApp() {
    try {
        // Читаем данные
        const data = JSON.parse(fs.readFileSync("data.json", "utf8"));
        
        let html;
        
        // Заглушка для console в SSR окружении
        // if (typeof globalThis.console === "undefined") {
            globalThis.console = {
                log: () => {},
                error: () => {},
                warn: () => {},
                info: () => {},
                debug: () => {},
                trace: () => {},
                dir: () => {},
                time: () => {},
                timeEnd: () => {},
                group: () => {},
                groupEnd: () => {},
                clear: () => {},
                count: () => {},
                assert: () => {},
                table: () => {}
            };
        // }
        
        // Пытаемся загрузить пользовательский компонент
        try {
            if (fs.existsSync("component.js")) {
                const componentModule = await import("./component.js");
                
                // Проверяем, есть ли функция render (новый формат)
                if (typeof componentModule.render === "function") {
                    // Задаем глобальную переменную с конфигурацией как в mixVue.php
                    globalThis.' . strtolower($app) . 'Configs = data;
                    
                    // Устанавливаем baseURL для API запросов в SSR
                    if (data.SSR_BASE_URL) {
                        globalThis.SSR_BASE_URL = data.SSR_BASE_URL;
                        // console.log("SSR: Установлен baseURL:", data.SSR_BASE_URL);
                    }
                    
                    // Новый формат с функцией render
                    const result = await componentModule.render("/");
                    // Очищаем Vue фрагменты комментарии
                    const cleanHtml = result.html //result.html.replace(/<!--\[-->|<!---->|<!--\]-->/g, "");
                    html = `<div id="' . strtolower($app) . '">${cleanHtml}</div>`;
                } else {
                    throw new Error("No render function found");
                }
            } else {
                throw new Error("Component file not found");
            }
        } catch (componentError) {
            console.error("Failed to load user component:", componentError.message);
            console.error("Stack trace:", componentError.stack);
            
            // Fallback к базовому компоненту
            const { createSSRApp } = await import("vue");
            const { renderToString } = await import("vue/server-renderer");
            
            const app = createSSRApp({
                data() {
                    return {
                        title: data.title || "Vue SSR App",
                        message: data.message || "Rendered on server with Node.js!",
                        items: data.items || [],
                        author: data.author || "",
                        version: data.version || "",
                        ...data
                    };
                },
                template: `
                    <div id="' . strtolower($app) . '">
                        <h1>{{ title }}</h1>
                        <p>{{ message }}</p>
                        <ul v-if="items && items.length">
                            <li v-for="item in items" :key="item.id || item.name">
                                {{ item.name || item.title || item }}
                            </li>
                        </ul>
                        <div v-if="author" class="field-author">
                            <strong>author:</strong> {{ author }}
                        </div>
                        <div v-if="version" class="field-version">
                            <strong>version:</strong> {{ version }}
                        </div>
                    </div>
                `
            });
            
            html = await renderToString(app);
        }
        
        process.stdout.write(html);
        
    } catch (error) {
        process.stdout.write(`<div id="' . strtolower($app) . '">SSR Error: ${error.message}</div>`);
    }
}

renderApp();
        ';
    }
    
    /**
     * Выполнение SSR через Node.js
     */
    private function executeNodeSSR($tempDir) {
        $currentDir = getcwd();
        chdir($tempDir);
        
        // Устанавливаем зависимости
        $output = [];
        $return_var = 0;
        exec($this->npmPath . ' install --silent 2>&1', $output, $return_var);
        
        if ($return_var !== 0) {
            chdir($currentDir);
            throw new Exception('Failed to install npm dependencies: ' . implode("\n", $output));
        }
        
        // ВАЖНО: Копируем pvtables ПОСЛЕ npm install, так как npm install удаляет node_modules
        $this->copyPvtablesToNodeModules($tempDir);
        
        // Выполняем SSR
        $output = [];
        $return_var = 0;
        exec($this->nodeJsPath . ' ssr.js 2>&1', $output, $return_var);
        
        chdir($currentDir);
        
        if ($return_var !== 0) {
            throw new Exception('Node.js SSR execution failed: ' . implode("\n", $output));
        }
        
        return implode("\n", $output);
    }
    
    /**
     * Очистка временных файлов
     */
    private function cleanupTempFiles($tempDir) {
        if (is_dir($tempDir)) {
            $this->deleteDirectory($tempDir);
        }
    }
    
    /**
     * Рекурсивное удаление директории
     */
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
    
    /**
     * Копирование pvtables в Node.js окружение
     */
    private function copyPvtablesToNodeModules($tempDir) {
        $errors = [];
        
        try {
            // Путь к pvtables в assets
            $pvtablesSourcePath = $this->modx->getOption('assets_path') . 'components/gtsapi/js/web/pvtables/';
            $errors[] = "DEBUG: Ищем pvtables в: " . $pvtablesSourcePath;
            
            if (!is_dir($pvtablesSourcePath)) {
                $errors[] = "ERROR: Директория pvtables не найдена: " . $pvtablesSourcePath;
                $errors[] = "DEBUG: assets_path = " . $this->modx->getOption('assets_path');
                
                // Проверяем альтернативные пути
                $altPath1 = $this->modx->getOption('assets_path') . 'components/gtsapi/js/web/';
                $errors[] = "DEBUG: Проверяем альтернативный путь: " . $altPath1;
                if (is_dir($altPath1)) {
                    $files = scandir($altPath1);
                    $errors[] = "DEBUG: Файлы в " . $altPath1 . ": " . implode(', ', array_filter($files, function($f) { return $f !== '.' && $f !== '..'; }));
                }
                
                throw new Exception('pvtables source directory not found: ' . $pvtablesSourcePath . "\n" . implode("\n", $errors));
            }
            
            $errors[] = "DEBUG: Директория pvtables найдена";
            
            // Проверяем содержимое директории pvtables
            $pvtablesFiles = scandir($pvtablesSourcePath);
            $errors[] = "DEBUG: Файлы в pvtables: " . implode(', ', array_filter($pvtablesFiles, function($f) { return $f !== '.' && $f !== '..'; }));
            
            // Создаем директорию node_modules
            $nodeModulesDir = $tempDir . '/node_modules';
            $errors[] = "DEBUG: Создаем node_modules: " . $nodeModulesDir;
            
            if (!is_dir($nodeModulesDir) && !mkdir($nodeModulesDir, 0755, true)) {
                $errors[] = "ERROR: Не удалось создать node_modules";
                throw new Exception('Failed to create node_modules directory: ' . $nodeModulesDir . "\n" . implode("\n", $errors));
            }
            
            $errors[] = "DEBUG: node_modules создан успешно";
            
            // Создаем директорию pvtables
            $pvtablesDir = $nodeModulesDir . '/pvtables';
            $errors[] = "DEBUG: Создаем pvtables dir: " . $pvtablesDir;
            
            if (!is_dir($pvtablesDir) && !mkdir($pvtablesDir, 0755, true)) {
                $errors[] = "ERROR: Не удалось создать pvtables directory";
                throw new Exception('Failed to create pvtables directory: ' . $pvtablesDir . "\n" . implode("\n", $errors));
            }
            
            $errors[] = "DEBUG: pvtables directory создан успешно";
            
            // Создаем папку dist внутри pvtables
            $distDir = $pvtablesDir . '/dist';
            $errors[] = "DEBUG: Создаем dist dir: " . $distDir;
            
            if (!is_dir($distDir) && !mkdir($distDir, 0755, true)) {
                $errors[] = "ERROR: Не удалось создать dist directory";
                throw new Exception('Failed to create pvtables/dist directory: ' . $distDir . "\n" . implode("\n", $errors));
            }
            
            // Копируем всю папку pvtables в dist
            $copiedFiles = $this->copyDirectoryRecursive($pvtablesSourcePath, $distDir, $errors);
            $errors[] = "DEBUG: Скопировано файлов в dist: " . $copiedFiles;
            
            // Создаем package.json для pvtables
            $pvtablesPackage = [
                'name' => 'pvtables',
                'version' => '1.0.0',
                'main' => 'index.js',
                'type' => 'module',
                'exports' => [
                    '.' => './index.js',
                    './dist/pvtables' => './dist/pvtables.js',
                    './dist/pvtables.js' => './dist/pvtables.js'
                ]
            ];
            
            $packageJsonPath = $pvtablesDir . '/package.json';
            $errors[] = "DEBUG: Создаем package.json: " . $packageJsonPath;
            
            if (file_put_contents($packageJsonPath, json_encode($pvtablesPackage, JSON_PRETTY_PRINT)) === false) {
                $errors[] = "ERROR: Не удалось создать package.json";
                throw new Exception('Failed to create package.json: ' . $packageJsonPath . "\n" . implode("\n", $errors));
            }
            
            $errors[] = "DEBUG: package.json создан успешно";
            
            // Создаем index.js который экспортирует из dist
            $indexJs = 'export * from "./dist/pvtables.js";';
            $indexJsPath = $pvtablesDir . '/index.js';
            $errors[] = "DEBUG: Создаем index.js: " . $indexJsPath;
            
            if (file_put_contents($indexJsPath, $indexJs) === false) {
                $errors[] = "ERROR: Не удалось создать index.js";
                throw new Exception('Failed to create index.js: ' . $indexJsPath . "\n" . implode("\n", $errors));
            }
            
            $errors[] = "DEBUG: index.js создан успешно";
            $errors[] = "SUCCESS: pvtables успешно скопирован в Node.js окружение";
            
            // Записываем отладочную информацию в файл для проверки
            file_put_contents($tempDir . '/pvtables_debug.log', implode("\n", $errors));
            
        } catch (Exception $e) {
            // Записываем ошибки в файл для отладки
            file_put_contents($tempDir . '/pvtables_error.log', implode("\n", $errors) . "\nFINAL ERROR: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Базовый компонент по умолчанию
     */
    private function getDefaultComponent() {
        return '
export default {
    data() {
        return {
            message: "Hello from Vue SSR with Node.js!"
        }
    },
    template: `<div><h1>{{ message }}</h1></div>`
};
        ';
    }
    
    /**
     * Очистка кэша для конкретного приложения
     */
    public function clearCache($app = null) {
        if ($this->cache) {
            return $this->cache->clear($app);
        }
        return 0;
    }
    
    /**
     * Очистка устаревшего кэша
     */
    public function cleanupCache() {
        if ($this->cache) {
            return $this->cache->cleanup();
        }
        return 0;
    }
    
    /**
     * Рекурсивное копирование директории
     */
    private function copyDirectoryRecursive($source, $destination, &$errors = []) {
        $copiedFiles = 0;
        
        if (!is_dir($source)) {
            $errors[] = "ERROR: Исходная директория не существует: " . $source;
            return 0;
        }
        
        // Создаем целевую директорию если она не существует
        if (!is_dir($destination) && !mkdir($destination, 0755, true)) {
            $errors[] = "ERROR: Не удалось создать целевую директорию: " . $destination;
            return 0;
        }
        
        $errors[] = "DEBUG: Копируем из " . $source . " в " . $destination;
        
        // Получаем список файлов и папок
        $items = scandir($source);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $sourcePath = $source . DIRECTORY_SEPARATOR . $item;
            $destPath = $destination . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($sourcePath)) {
                // Рекурсивно копируем поддиректорию
                $errors[] = "DEBUG: Копируем поддиректорию: " . $item;
                $copiedFiles += $this->copyDirectoryRecursive($sourcePath, $destPath, $errors);
            } else {
                // Копируем файл
                $errors[] = "DEBUG: Копируем файл: " . $item . " (размер: " . filesize($sourcePath) . " байт)";
                
                if (copy($sourcePath, $destPath)) {
                    $copiedFiles++;
                    $errors[] = "DEBUG: Файл " . $item . " скопирован успешно";
                } else {
                    $errors[] = "ERROR: Не удалось скопировать файл: " . $item;
                }
            }
        }
        
        $errors[] = "DEBUG: Завершено копирование директории " . basename($source) . ", скопировано файлов: " . $copiedFiles;
        return $copiedFiles;
    }
    
    /**
     * Очистка ресурсов рендерера
     */
    public function cleanup() {
        // Очищаем кэш если нужно
        if ($this->cache) {
            $this->cache = null;
        }
        
        // Очищаем ссылки
        $this->modx = null;
    }
}
