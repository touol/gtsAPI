<?php

/**
 * Кэш для SSR рендеринга
 * Улучшает производительность за счет кэширования результатов
 */
class SSRCache {
    
    private $modx;
    private $cacheDir;
    private $defaultTTL = 3600; // 1 час
    
    public function __construct(modX &$modx) {
        $this->modx = $modx;
        $this->cacheDir = $modx->getOption('core_path') . 'cache/gtsapi/ssr/';
        $this->ensureCacheDir();
    }
    
    /**
     * Создание директории кэша если не существует
     */
    private function ensureCacheDir() {
        if (!is_dir($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0755, true)) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Failed to create SSR cache directory: ' . $this->cacheDir);
            }
        }
    }
    
    /**
     * Генерация ключа кэша
     */
    private function generateKey($app, $config) {
        $data = [
            'app' => $app,
            'config' => $config,
            'timestamp' => date('Y-m-d-H') // Обновляем кэш каждый час
        ];
        return 'ssr_' . md5(serialize($data));
    }
    
    /**
     * Получение из кэша
     */
    public function get($app, $config) {
        $key = $this->generateKey($app, $config);
        $filePath = $this->cacheDir . $key . '.cache';
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($filePath));
        
        // Проверяем TTL
        if ($data['expires'] < time()) {
            unlink($filePath);
            return null;
        }
        
        return $data['html'];
    }
    
    /**
     * Сохранение в кэш
     */
    public function set($app, $config, $html, $ttl = null) {
        if ($ttl === null) {
            $ttl = $this->defaultTTL;
        }
        
        $key = $this->generateKey($app, $config);
        $filePath = $this->cacheDir . $key . '.cache';
        
        $data = [
            'html' => $html,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        file_put_contents($filePath, serialize($data));
    }
    
    /**
     * Очистка кэша
     */
    public function clear($app = null) {
        $pattern = $app ? 'ssr_*' . md5($app) . '*.cache' : 'ssr_*.cache';
        $files = glob($this->cacheDir . $pattern);
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return count($files);
    }
    
    /**
     * Очистка устаревшего кэша
     */
    public function cleanup() {
        $files = glob($this->cacheDir . 'ssr_*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}
