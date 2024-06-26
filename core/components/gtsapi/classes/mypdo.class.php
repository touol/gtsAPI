<?php

class myPdo
{
    /** @var modX $modx */
    public $modx;
    /** @var string $pk Primary key of class */
    protected $pk;
    /** @var array $ancestry Array with ancestors of class */
    protected $ancestry = array();
    /** @var xPDOQuery $query */
    protected $query;
    /** @var array $aliases Array with aliases of classes */
    public $aliases;

    public $timings = array();
    /** @var array $config Array with class config */
    public $config = array();
    protected $time;
    protected $start = 0;
    /** @var integer $idx Index of iterator of rows processing */
    public $idx = 1;
    /** @var integer $count Total number of results for chunks processing */
    protected $count = 0;
    /** @var array $store Array for cache elements and user data */
    public $store = array(
        'chunk' => array(),
        'snippet' => array(),
        'tv' => array(),
        'data' => array(),
        'resource' => array(),
    );
    /**
     * @param modX $modx
     * @param array $config
     */
    public function __construct(modX & $modx, $config = array())
    {
        $this->modx = $modx;
        $this->time = $this->start = microtime(true);

        $this->setConfig($config);
    }

    /**
     * Set config from default values and given array.
     *
     * @param array $config
     * @param bool $clean_timings Clean timings array
     */
    public function setConfig0(array $config = array(), $clean_timings = true)
    {
        $this->config = array_merge(array(
            'fastMode' => false,
            'nestedChunkPrefix' => 'pdotools_',
            'offset' => 0,

            'checkPermissions' => '',
            'loadModels' => '',
            'prepareSnippet' => '',
            'prepareTVs' => '',
            'processTVs' => '',

            'outputSeparator' => "\n",
            'decodeJSON' => true,
            'scheme' => '',
            'fenomSyntax' => $this->modx->getOption('pdotools_fenom_syntax', null, '#\{(\$|\/|\w+(\s|\(|\|)|\(|\')#', true),
            'elementsPath' => $this->modx->getOption('pdotools_elements_path', null, '{core_path}elements/', true),
            'cachePath' => '{core_path}cache/default/pdotools',
        ), $config);

        if ($clean_timings) {
            $this->timings = array();
        }

        if (empty($this->config['scheme'])) {
            $this->config['scheme'] = $this->modx->getOption('link_tag_scheme');
        }
        if (is_numeric($this->config['scheme'])) {
            $this->config['scheme'] = (int)$this->config['scheme'];
        }
        $this->config['useFenom'] = $this->modx->getOption('pdotools_fenom_default', null, true);
        $this->config['useFenomParser'] = $this->modx->getOption('pdotools_fenom_parser', null, false);
        $this->config['useFenomCache'] = $this->modx->getOption('pdotools_fenom_cache', null, false);
        $this->config['useFenomMODX'] = $this->modx->getOption('pdotools_fenom_modx', null, false);
        $this->config['useFenomPHP'] = $this->modx->getOption('pdotools_fenom_php', null, false);
        $this->config['useFenomSoftMode'] = $this->modx->getOption('pdotools_fenom_soft_mode', null, false);
        
        // Prepare paths
        $pl = array(
            'core_path' => MODX_CORE_PATH,
            'assets_path' => MODX_ASSETS_PATH,
            'base_path' => MODX_BASE_PATH,
        );
        $pl1 = $this->makePlaceholders($pl, '', '{', '}', false);
        $pl2 = $this->makePlaceholders($pl, '', '[[+', ']]', false);
        foreach (array('elementsPath', 'cachePath') as $k) {
            $this->config[$k] = str_replace($pl1['pl'], $pl1['vl'],
                str_replace($pl2['pl'], $pl2['vl'], $this->config[$k])
            );
        }
    }
    /**
     * {@inheritdoc}
     */
    public function setConfig(array $config = array(), $clean_timings = true)
    {
        $this->setConfig0(
            array_merge(array(
                'class' => 'modResource',
                'limit' => 0,
                'sortby' => '',
                'sortdir' => '',
                'groupby' => '',
                'totalVar' => 'total',
                'setTotal' => false,
                'tpl' => '',
                'return' => 'data',    // chunks, data, sql or ids

                'select' => '',
                'leftJoin' => '',
                'rightJoin' => '',
                'innerJoin' => '',

                'includeTVs' => '',
                'tvPrefix' => '',
                'tvsJoin' => array(),
                'tvsSelect' => array(),

                'tvFiltersAndDelimiter' => ',',
                'tvFiltersOrDelimiter' => '||',

                'additionalPlaceholders' => '',
                'useWeblinkUrl' => false,
                'subpdo'=> false,
                'with'=> false,
            ), $config),
            $clean_timings);

        if (empty($this->config['class'])) {
            $this->config['class'] = 'modResource';
        }
        $this->loadModels();
        $this->ancestry = $this->modx->getAncestry($this->config['class']);
        $pk = $this->modx->getPK($this->config['class']);
        $this->pk = is_array($pk)
            ? implode(',', $pk)
            : $pk;
        if (!isset($this->config['idx']) || !is_numeric($this->config['idx'])) {
            $this->config['idx'] = 1;
        }
        $this->idx = !empty($this->config['offset'])
            ? (int)$this->config['offset'] + $this->config['idx']
            : (int)$this->config['idx'];
    }
    /**
     * Loads specified list of packages models
     */
    public function loadModels()
    {
        if (empty($this->config['loadModels'])) {
            return;
        }

        $time = microtime(true);
        $models = array();
        if (strpos(ltrim($this->config['loadModels']), '{') === 0) {
            $tmp = json_decode($this->config['loadModels'], true);
            foreach ($tmp as $k => $v) {
                if (!is_array($v)) {
                    $v = array(
                        'path' => trim($v),
                    );
                }
                $v = array_merge(array(
                    'path' => MODX_CORE_PATH . 'components/' . strtolower($k) . '/model/',
                    'prefix' => null,
                ), $v);
                if (strpos($v['path'], MODX_CORE_PATH) === false) {
                    $v['path'] = MODX_CORE_PATH . ltrim($v['path'], '/');
                }
                $models[$k] = $v;
            }
        } else {
            $tmp = array_map('trim', explode(',', $this->config['loadModels']));
            foreach ($tmp as $v) {
                $parts = explode(':', $v, 2);
                $models[$parts[0]] = array(
                    'path' => MODX_CORE_PATH . 'components/' . strtolower($parts[0]) . '/model/',
                    'prefix' => count($parts) > 1 ? $parts[1] : null,
                );
            }
        }

        if (!empty($models)) {
            foreach ($models as $k => $v) {
                $t = '/' . str_replace(array(MODX_BASE_PATH, MODX_CORE_PATH), '', $v['path']);
                if ($this->modx->addPackage(strtolower($k), $v['path'], $v['prefix'])) {
                    $this->addTime('Loaded model "' . $k . '" from "' . $t . '"', microtime(true) - $time);
                } else {
                    $this->addTime('Could not load model "' . $k . '" from "' . $t . '"', microtime(true) - $time);
                }
                $time = microtime(true);
            }
        }

        $this->config['loadModels'] = '';
    }
     /**
     * Transform array to placeholders
     *
     * @param array $array
     * @param string $plPrefix
     * @param string $prefix
     * @param string $suffix
     * @param bool $uncacheable
     *
     * @return array
     */
    public function makePlaceholders(
        array $array = array(),
        $plPrefix = '',
        $prefix = '[[+',
        $suffix = ']]',
        $uncacheable = true
    ) {
        $result = array('pl' => array(), 'vl' => array());

        $uncached_prefix = str_replace('[[', '[[!', $prefix);
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $result = array_merge_recursive($result,
                    $this->makePlaceholders($v, $plPrefix . $k . '.', $prefix, $suffix, $uncacheable));
            } else {
                $pl = $plPrefix . $k;
                $result['pl'][$pl] = $prefix . $pl . $suffix;
                $result['vl'][$pl] = $v;
                if ($uncacheable) {
                    $result['pl']['!' . $pl] = $uncached_prefix . $pl . $suffix;
                    $result['vl']['!' . $pl] = $v;
                }
            }
        }

        return $result;
    }
    /**
     * Add new record to time log
     *
     * @param $message
     * @param null $delta
     */
    public function addTime($message, $delta = null)
    {
        $time = microtime(true);
        if (!$delta) {
            $delta = $time - $this->time;
        }

        $this->timings[] = array(
            'time' => number_format(round(($delta), 7), 7),
            'message' => $message,
        );
        $this->time = $time;
    }


    /**
     * Return timings log
     *
     * @param bool $string Return array or formatted string
     *
     * @return array|string
     */
    public function getTime($string = true)
    {
        $this->timings[] = array(
            'time' => number_format(round(microtime(true) - $this->start, 7), 7),
            'message' => '<b>Total time</b>',
        );
        $this->timings[] = array(
            'time' => number_format(round((memory_get_usage(true)), 2), 0, ',', ' '),
            'message' => '<b>Memory usage</b>',
        );

        if (!$string) {
            return $this->timings;
        } else {
            $res = '';
            foreach ($this->timings as $v) {
                $res .= $v['time'] . ': ' . $v['message'] . "\n";
            }

            return $res;
        }
    }
    /**
     * Checks user permissions to view the results
     *
     * @param array $rows
     *
     * @return array
     */
    public function checkPermissions($rows = array())
    {
        $permissions = array();
        if (!empty($this->config['checkPermissions'])) {
            $tmp = array_map('trim', explode(',', $this->config['checkPermissions']));
            foreach ($tmp as $v) {
                $permissions[$v] = true;
            }
        } else {
            return $rows;
        }
        $total = !empty($this->config['totalVar'])
            ? $this->modx->getPlaceholder($this->config['totalVar'])
            : 0;

        foreach ($rows as $key => $row) {
            /** @var modAccessibleObject $object */
            $object = $this->modx->newObject($this->config['class']);
            $object->_fields['id'] = $row['id'];
            if ($object instanceof modAccessibleObject && !$object->checkPolicy($permissions)) {
                unset($rows[$key]);
                $this->addTime($this->config['class'] . ' #' . $row['id'] . ' was excluded from results, because you do not have enough permissions');
                $total--;
            }
        }

        $this->addTime('Checked for permissions "' . implode(',', array_keys($permissions)) . '"');
        if (!empty($this->config['totalVar']) && !empty($this->config['setTotal'])) {
            $this->modx->setPlaceholder($this->config['totalVar'], $total);
        }

        return $rows;
    }
    /**
     * Get data from cache
     *
     * @param $name
     * @param string $type
     *
     * @return mixed|null
     */
    public function getStore($name, $type = 'data')
    {
        return isset($this->store[$type][$name])
            ? $this->store[$type][$name]
            : null;
    }
    /**
     * Set data to cache
     *
     * @param $name
     * @param $object
     * @param string $type
     */
    public function setStore($name, $object, $type = 'data')
    {
        $this->store[$type][$name] = $object;
    }
    /**
     * Prepares fetched rows and process template variables
     *
     * @param array $rows
     *
     * @return array
     */
    public function prepareRows(array $rows = array())
    {
        $time = microtime(true);
        $prepare = $process = $prepareTypes = array();
        if (!empty($this->config['includeTVs']) && (!empty($this->config['prepareTVs']) || !empty($this->config['processTVs']))) {
            $tvs = array_map('trim', explode(',', $this->config['includeTVs']));
            $prepare = ($this->config['prepareTVs'] == 1)
                ? $tvs
                : array_map('trim', explode(',', $this->config['prepareTVs']));
            $prepareTypes = array_map('trim',
                explode(',', $this->modx->getOption('manipulatable_url_tv_output_types', null, 'image,file')));
            $process = ($this->config['processTVs'] == 1)
                ? $tvs
                : array_map('trim', explode(',', $this->config['processTVs']));

            $prepare = array_flip($prepare);
            $prepareTypes = array_flip($prepareTypes);
            $process = array_flip($process);
        }

        foreach ($rows as & $row) {
            // Extract JSON fields
            if ($this->config['decodeJSON']) {
                foreach ($row as $k => $v) {
                    if (!empty($v) && is_string($v) && ($v[0] == '[' || $v[0] == '{')) {
                        $tmp = json_decode($v, true);
                        if (json_last_error() == JSON_ERROR_NONE) {
                            $row[$k] = $tmp;
                        }
                    }
                }
            }

            // Prepare and process TVs
            if (!empty($tvs)) {
                foreach ($tvs as $tv) {
                    if (!isset($process[$tv]) && !isset($prepare[$tv])) {
                        continue;
                    }

                    /** @var modTemplateVar $templateVar */
                    if (!$templateVar = $this->getStore($tv, 'tv')) {
                        if ($templateVar = $this->modx->getObject('modTemplateVar', array('name' => $tv))) {
                            $sourceCache = isset($prepareTypes[$templateVar->type])
                                ? $templateVar->getSourceCache($this->modx->context->get('key'))
                                : null;
                            $templateVar->set('sourceCache', $sourceCache);
                            $this->setStore($tv, $templateVar, 'tv');
                        } else {
                            $this->addTime('Could not process or prepare TV "' . $tv . '"');
                            continue;
                        }
                    }

                    $tvPrefix = !empty($this->config['tvPrefix']) ?
                        trim($this->config['tvPrefix'])
                        : '';
                    $key = $tvPrefix . $templateVar->name;
                    if (isset($process[$tv])) {
                        $row[$key] = $templateVar->renderOutput($row['id']);
                    } elseif (isset($prepare[$tv]) && is_string($row[$key]) && strpos($row[$key],
                            '://') === false && method_exists($templateVar, 'prepareOutput')
                    ) {
                        if (isset($templateVar->sourceCache) && $source = $templateVar->sourceCache) {
                            if ($source['class_key'] == 'modFileMediaSource') {
                                if (!empty($source['baseUrl']) && !empty($row[$key])) {
                                    $row[$key] = $source['baseUrl'] . $row[$key];
                                    if (isset($source['baseUrlRelative']) && !empty($source['baseUrlRelative'])) {
                                        $row[$key] = $this->modx->context->getOption('base_url', null,
                                                MODX_BASE_URL) . $row[$key];
                                    }
                                }
                            } else {
                                $row[$key] = $templateVar->prepareOutput($row[$key]);
                            }
                        }
                    }
                }
            }
        }

        if (!empty($tvs)) {
            $this->addTime('Prepared and processed TVs', microtime(true) - $time);
        }

        return $rows;
    }
    /**
     * @param $id
     * @param array $options
     * @param array $args
     *
     * @return mixed|string
     */
    public function makeUrl($id, $options = array(), $args = array())
    {
        $scheme = !empty($options['scheme'])
            ? $options['scheme']
            : $this->config['scheme'];
        if (strtolower($scheme) == 'uri' && !empty($options['uri'])) {
            $url = $options['uri'];
            if (!empty($args)) {
                if (is_array($args)) {
                    $args = rtrim(modX::toQueryString($args), '?&');
                }
                $url .= strpos($url, '?') !== false
                    ? '&'
                    : '?';
                $url .= ltrim(trim($args), '?&');
            }
        } else {
            if (!empty($options['context_key'])) {
                $context = $options['context_key'];
            } elseif (!empty($options['context'])) {
                $context = $options['context'];
            } else {
                $context = '';
            }
            if (strtolower($scheme) == 'uri') {
                $scheme = -1;
            }
            $url = $this->modx->makeUrl($id, $context, $args, $scheme, $options);
        }

        return $url;
    }
    /**
     * Main method for query processing and fetching rows
     * It can return string with SQL query, array or raw rows or processed HTML chunks
     *
     * @return array|bool|string
     */
    public function run()
    {
        $this->makeQuery();
        $this->genSubPdo();
        $this->addTVFilters();
        $this->addTVs();
        $this->addJoins();
        $this->addGrouping();
        $this->addSelects();
        $this->addWhere();
        $this->addSort();
        $this->prepareQuery();

        $output = '';
        
        if (strtolower($this->config['return']) == 'sql') {
            $this->addTime('Returning raw sql query');
            $output = $this->query->toSQL();
        } else {
            $this->modx->exec('SET SQL_BIG_SELECTS = 1');
            $this->addTime('SQL prepared <small>"' . $this->query->toSQL() . '"</small>');
            $tstart = microtime(true);
            if ($this->query->stmt->execute()) {
                $this->modx->queryTime += microtime(true) - $tstart;
                $this->modx->executedQueries++;
                $this->addTime('SQL executed', microtime(true) - $tstart);
                $this->setTotal();

                $rows = $this->query->stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->addTime('Rows fetched');
                $rows = $this->checkPermissions($rows);
                $this->count = count($rows);
                if($this->config['with']){
                    $rows = $this->with($this->config['with'],$rows);
                }
                if (strtolower($this->config['return']) == 'ids') {
                    $ids = array();
                    foreach ($rows as $row) {
                        $ids[] = $row[$this->pk];
                    }
                    $output = implode(',', $ids);
                } elseif (strtolower($this->config['return']) == 'data') {
                    $rows = $this->prepareRows($rows);
                    $this->addTime('Returning raw data');
                    $output = &$rows;
                } elseif (strtolower($this->config['return']) == 'json') {
                    $rows = $this->prepareRows($rows);
                    $this->addTime('Returning raw data as JSON string');
                    $output = json_encode($rows);
                } elseif (strtolower($this->config['return']) == 'serialize') {
                    $rows = $this->prepareRows($rows);
                    $this->addTime('Returning raw data as serialized string');
                    $output = serialize($rows);
                }
                // } else {
                //     $rows = $this->prepareRows($rows);
                //     $time = microtime(true);
                //     $output = array();
                //     foreach ($rows as $row) {
                //         if (!empty($this->config['additionalPlaceholders'])) {
                //             $row = array_merge($this->config['additionalPlaceholders'], $row);
                //         }
                //         $row['idx'] = $this->idx++;

                //         // Add placeholder [[+link]] if specified
                //         if (!empty($this->config['useWeblinkUrl'])) {
                //             if (!isset($row['context_key'])) {
                //                 $row['context_key'] = '';
                //             }
                //             if (isset($row['class_key']) && ($row['class_key'] == 'modWebLink')) {
                //                 $row['link'] = isset($row['content']) && is_numeric(trim($row['content'], '[]~ '))
                //                     ? $this->makeUrl(intval(trim($row['content'], '[]~ ')), $row)
                //                     : (isset($row['content']) ? $row['content'] : '');
                //             } else {
                //                 $row['link'] = $this->makeUrl($row['id'], $row);
                //             }
                //         } elseif (!isset($row['link'])) {
                //             $row['link'] = '';
                //         }

                //         $tpl = $this->defineChunk($row);
                //         if (empty($tpl)) {
                //             $output[] = '<pre>' . $this->getChunk('', $row) . '</pre>';
                //         } else {
                //             $output[] = $this->getChunk($tpl, $row, $this->config['fastMode']);
                //         }
                //     }
                //     $this->addTime('Returning processed chunks', microtime(true) - $time);

                //     if (!empty($this->config['toSeparatePlaceholders'])) {
                //         $this->modx->setPlaceholders($output, $this->config['toSeparatePlaceholders']);
                //         $output = '';
                //     } else {
                //         $output = implode($this->config['outputSeparator'], $output);
                //     }
                // }
            } else {
                $this->modx->log(modX::LOG_LEVEL_INFO, '[pdoTools] ' . $this->query->toSQL());
                $errors = $this->query->stmt->errorInfo();
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[pdoTools] Error ' . $errors[0] . ': ' . $errors[2]);
                //$this->modx->log(modX::LOG_LEVEL_ERROR, '[pdoTools] Error ' . $this->query->toSQL());
                
                $this->addTime('Could not process query, error #' . $errors[1] . ': ' . $errors[2]);
            }
        }

        return $output;
    }


    /**
     * Create object with xPDOQuery
     */
    public function makeQuery()
    {
        $time = microtime(true);
        $this->query = $this->modx->newQuery($this->config['class']);
        //Add alias for FROM table.
        if(!empty($this->config['alias'])) $this->query->setClassAlias($this->config['alias']);
        $this->addTime('xPDO query object created', microtime(true) - $time);
    }
    
    /**
     * genSubPdo
     */
    public function genSubPdo()
    {
        if(!empty($this->config['subpdo'])){
            $sub_keys = [];
            $sub_values = [];
            $tmp = $this->config['subpdo'];
            if (is_string($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
                $tmp = json_decode($tmp, true);
            }
            foreach($tmp as $k => $subpdo){
                $sub_keys[] = '{$subpdo.'.$k.'}';
				$sql0 = $this->getSubSelectSQL($subpdo);
				
                $sub_values[] = $sql0;
            }
            $pdoKeys = array('innerJoin', 'leftJoin', 'rightJoin', 'select', 'where', 'sortby', 'limit', 'groupby', 'having');
            $pdoConfig = array();
            foreach($pdoKeys as $pdoKey){
                if(!empty($this->config[$pdoKey])) $pdoConfig[$pdoKey] = $this->config[$pdoKey];
            }
            //$this->addTime("genSubPdo1 ".print_r($pdoConfig,1));
            //array_walk_recursive($pdoConfig,array(&$this, 'walkFunc'),['sub_keys'=>$sub_keys,'sub_values'=>$sub_values]);
			$pdoConfig = $this->walkFunc2($pdoConfig,['sub_keys'=>$sub_keys,'sub_values'=>$sub_values]);
			//$this->addTime("genSubPdo2 ".print_r($pdoConfig,1));
            $this->config = array_merge($this->config, $pdoConfig);
        }
    }
    public function walkFunc(&$item, $key, $sub){
        if(is_string($item)){
            $item = str_replace($sub['sub_keys'],$sub['sub_values'],$item);
        }
		$key = str_replace($sub['sub_keys'],$sub['sub_values'],$key);
    }
	public function walkFunc2($array,$sub) {
		if (!is_array($array)) return;
		$helper = array();
		foreach ($array as $key => $value) $helper[$this->sub_replace($key,$sub)] = is_array($value) ? $this->walkFunc2($value,$sub) : $this->sub_replace($value,$sub);
		return $helper;
	}
	
    public function sub_replace($item,$sub) {
		return str_replace($sub['sub_keys'],$sub['sub_values'],$item);
	}
	
    /**
     * Adds where and having conditions
     */
    public function addWhere()
    {
        $time = microtime(true);
        $where = array();
        if (!empty($this->config['where'])) {
            $tmp = $this->config['where'];
            if (is_string($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
                $tmp = json_decode($tmp, true);
            }
            if (!is_array($tmp)) {
                $tmp = array($tmp);
            }
            $where = $this->replaceTVCondition($tmp);
        }
        $where = $this->additionalConditions($where);
        if (!empty($where)) {
            $this->query->where($where);
			//$this->query->prepare();
			//$this->addTime('Added where condition: <b>' . $this->query->toSQL() . '</b>', microtime(true) - $time);
            $condition = array();
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    if (isset($v[0])) {
                        $condition[] = is_array($v) ? $k . '(' . implode(',', $v) . ')' : $k . '=' . $v;
                    } else {
                        foreach ($v as $k2 => $v2) {
                            $condition[] = is_array($v2) ? $k2 . '(' . implode(',', $v2) . ')' : $k2 . '=' . $v2;
                        }
                    }
                } else {
                    $condition[] = $k . '=' . $v;
                }
            }
            $this->addTime('Added where condition: <b>' . implode(', ', $condition) . '</b>', microtime(true) - $time);
        }
        $time = microtime(true);
        if (!empty($this->config['having'])) {
            $tmp = $this->config['having'];
            if (is_string($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
                $tmp = json_decode($tmp, true);
            }
            if (!is_array($tmp)) {
                $tmp = array($tmp);
            }
            $having = $this->replaceTVCondition($tmp);
            $this->query->having($having);

            $condition = array();
            foreach ($having as $k => $v) {
                if (is_array($v)) {
                    $condition[] = $k . '(' . implode(',', $v) . ')';
                } else {
                    $condition[] = $k . '=' . $v;
                }
            }
            $this->addTime('Added having condition: <b>' . implode(', ', $condition) . '</b>', microtime(true) - $time);
        }
    }


    /**
     * Set "total" placeholder for pagination
     */
    public function setTotal()
    {
        if ($this->config['setTotal'] && !in_array($this->config['return'], array('sql', 'ids'))) {
            $time = microtime(true);

            $q = $this->modx->prepare("SELECT FOUND_ROWS();");

            $tstart = microtime(true);
            $q->execute();
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;

            $total = $q->fetch(PDO::FETCH_COLUMN);
            $this->modx->setPlaceholder($this->config['totalVar'], $total);

            $this->addTime('Total rows: <b>' . $total . '</b>', microtime(true) - $time);
        }
    }


    /**
     * Add tables join to query
     */
    public function addJoins()
    {
        $time = microtime(true);
        // left join is always needed because of TVs
        if (empty($this->config['leftJoin'])) {
            $this->config['leftJoin'] = '[]';
        }

        $joinSequence = array('innerJoin', 'leftJoin', 'rightJoin');
        if (!empty($this->config['joinSequence'])) {
            if (is_string($this->config['joinSequence'])) {
                $this->config['joinSequence'] = array_map('trim', explode(',', $this->config['joinSequence']));
            }
            if (is_array($this->config['joinSequence'])) {
                $joinSequence = $this->config['joinSequence'];
            }
        }

        foreach ($joinSequence as $join) {
            if (!empty($this->config[$join])) {
                $tmp = $this->config[$join];
                if (is_string($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
                    $tmp = json_decode($tmp, true);
                }
                if ($join == 'leftJoin' && !empty($this->config['tvsJoin'])) {
                    $tmp = array_merge($tmp, $this->config['tvsJoin']);
                }
                foreach ($tmp as $k => $v) {
                    $class = !empty($v['class']) ? $v['class'] : $k;
                    $alias = !empty($v['alias']) ? $v['alias'] : $k;
                    $on = !empty($v['on']) ? $v['on'] : array();
                    
                    if (!is_numeric($alias) && !is_numeric($class)) {
                        if(!empty($v['subpdo'])){
                            if(is_string($v['subpdo'])) $subSQL = $v['subpdo'];
                            if(is_array($v['subpdo'])) $subSQL = $this->getSubSelectSQL($v['subpdo']);
                            if($subSQL){
                                $this->addTime('subSQL prepared <small>"' . $subSQL . '"</small>');
                                //https://prisma-cms.com/topics/dzhoinyi-podzaprosov-sredstvami-xpdo-2159.html
                                $joinSubSequence = array('innerJoin'=>'inner join', 'leftJoin'=>'left join', 'rightJoin'=>'right join');
                                $this->query->query['from']['joins'][] = array(
                                    "type" => $joinSubSequence[$join],
                                    "table" => "({$subSQL})",
                                    "alias" => $alias,
                                    "conditions" => array(
                                        new xPDOQueryCondition(array(
                                            "sql" => $on,
                                        )),
                                    ),
                                );
                            }
                        }else{
                            $this->query->$join($class, $alias, $on);
                        }
                        $this->addTime($join . 'ed <i>' . $class . '</i> as <b>' . $alias . '</b>',
                            microtime(true) - $time);
                        $this->aliases[$alias] = $class;
                        
                    } else {
                        $this->addTime('Could not ' . $join . ' <i>' . $class . '</i> as <b>' . $alias . '</b>',
                            microtime(true) - $time);
                    }
                    $time = microtime(true);
                    
                }
            }
        }
    }
    //$rows = $this->with($rows);
    public function with($configs = array(), $rows)
    {
        if(empty($configs)) return $rows;

        foreach($configs as $name=>$config){
            $ids_field = $config['ids_field'];
            if(empty($ids_field)) $ids_field = 'id';
            $search_field = $config['search_field'];
            $sort_field = $config['sort_field'];
            if(empty($sort_field)) $sort_field = 'id';

            $ids = [];
            foreach($rows as $row){
                $ids[] = $row[$ids_field];
            }

            $default = [
                'class'=>$name,
                'where'=>[
                    "$name.$search_field:IN"=>$ids,
                ],
                'sortby'=>[
                    "$name.$sort_field"=>'ASC',
                ],
            ];
            $withs = $this->_with($default);

            foreach($rows as $k=>$row){
                foreach($withs as $with){
                    if($with[$search_field] == $row[$ids_field]){
                        $rows[$k][$name][] = $with;
                    }
                }
            }
        }
        return $rows;
    }
    /**
     * PDO getSubSelectSQL
     *
     * @param $class
     * @param string $where
     * @param array $config
     *
     * @return sql|string
     */
    public function _with($config = array())
    {
        /** @var pdoFetch $instance */
        $fqn = $this->modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
        $path = $this->modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/',
            true);
        if (!$pdoClass = $this->modx->loadClass($fqn, $path, false, true)) {
            return false;
        }
        $instance = new $pdoClass($this->modx, $config);
        if (empty($config['class'])){
            $this->addTime('Could not subpdo! empty class');
            return false;
        }
        //$config['class'] = $class;
        $config['limit'] = !isset($config['limit'])
            ? 0
            : (int)$config['limit'];

        $instance->setConfig($config, true);
        $instance->makeQuery();
        $instance->genSubPdo();
        $instance->addTVFilters();
        $instance->addTVs();
        $instance->addJoins();
        $instance->addGrouping();
        $instance->addSelects();
        $instance->addWhere();
        $instance->addSort();
        $instance->prepareQuery();
        //$instance->modx->exec('SET SQL_BIG_SELECTS = 1');
        $rows = [];
        if ($instance->query->stmt->execute()) {
            $rows = $instance->query->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $rows;
    }
    /**
     * PDO getSubSelectSQL
     *
     * @param $class
     * @param string $where
     * @param array $config
     *
     * @return sql|string
     */
    public function getSubSelectSQL($config = array())
    {
        /** @var pdoFetch $instance */
        $fqn = $this->modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
        $path = $this->modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/',
            true);
        if (!$pdoClass = $this->modx->loadClass($fqn, $path, false, true)) {
            return false;
        }
        $instance = new $pdoClass($this->modx, $config);
        if (empty($config['class'])){
            $this->addTime('Could not subpdo! empty class');
            return false;
        }
        //$config['class'] = $class;
        $config['limit'] = !isset($config['limit'])
            ? 0
            : (int)$config['limit'];

        $instance->setConfig($config, true);
        $instance->makeQuery();
        $instance->genSubPdo();
        $instance->addTVFilters();
        $instance->addTVs();
        $instance->addJoins();
        $instance->addGrouping();
        $instance->addSelects();
        $instance->addWhere();
        $instance->addSort();
        $instance->prepareQuery();
        //$instance->modx->exec('SET SQL_BIG_SELECTS = 1');
        $sql = $instance->query->toSQL();
        //$this->modx->setPlaceholder('pdoTools.log', $instance->getTime());
        $this->addTime($instance->getTime());
        $this->addTime('SQL prepared <small>"' . $sql . '"</small>');
        return $sql;
    }

    /**
     * Add select of fields
     */
    public function addSelects()
    {
        $time = microtime(true);

        if ($this->config['return'] == 'ids') {
            $this->query->select('`' . $this->config['class'] . '`.`' . $this->pk . '`');
            $this->addTime('Parameter "return" set to "ids", so we select only primary key', microtime(true) - $time);
        } elseif ($tmp = $this->config['select']) {
            if (!is_array($tmp)) {
                $tmp = (!empty($tmp) && $tmp[0] == '{' || $tmp[0] == '[')
                    ? json_decode($tmp, true)
                    : array($this->config['class'] => $tmp);
            }
			//$this->addTime('Add selection of fields 0<b>' . print_r($tmp,1), microtime(true) - $time);
            if (!is_array($tmp)) {
                $tmp = array();
            }
			//$this->addTime('Add selection of fields 01<b>' . print_r($tmp,1), microtime(true) - $time);
            $tmp = array_merge($tmp, $this->config['tvsSelect']);
            $i = 0;
            foreach ($tmp as $class => $fields) {
                if (is_numeric($class)) {
                    $class = $alias = $this->config['class'];
                } elseif (isset($this->aliases[$class])) {
                    $alias = $class;
                    $class = $this->aliases[$alias];
                } else {
                    $alias = $class;
                }
                if ((is_string($fields) && !preg_match('/\b' . $alias . '\b|\bAS\b|\(|`/i',
                        $fields) && isset($this->modx->map[$class])) || $fields == '*'
                ) {
                    if ($fields == 'all' || $fields == '*' || empty($fields)) {
                        $fields = $this->modx->getSelectColumns($class, $alias);
                        $this->addTime('Add selection of fields 1 <b>' . $class . '</b>: <small>'.$fields." !!!" . str_replace('`' . $alias . '`.',
                            '', $fields) . '</small>', microtime(true) - $time);
                    } elseif(preg_match('/DISTINCT/i',
                        $fields)){
						
					}else {
                        
                        $fields = $this->modx->getSelectColumns($class, $alias, '',
                            array_map('trim', explode(',', $fields)));
                        $this->addTime('Add selection of fields 2<b>' . $class . '</b>: <small>'.$fields." !!!" . str_replace('`' . $alias . '`.',
                            '', $fields) . '</small>', microtime(true) - $time);
                    }
                }

                if ($i == 0 && $this->config['setTotal']) {
                    //Fixed error with SQL_CALC_FOUND_ROWS.
                    if(is_array($fields)){
                        $fields = implode(",",$fields);
                        $fields = 'SQL_CALC_FOUND_ROWS ' . $fields;
                    }else{
                        $fields = 'SQL_CALC_FOUND_ROWS ' . $fields;
                    }
                    
                }

                if (is_string($fields) && strpos($fields, '(') !== false) {
                    // Commas in functions
                    $fields = preg_replace_callback('/\(.*?\bAS\b/i', function($matches) {
						return str_replace(",", "|", $matches[0]);
                    }, $fields);
					
                    $fields = explode(',', $fields);
                    foreach ($fields as &$field) {
						$field = str_replace('|', ',', $field);
                    }
                    $this->query->select($fields);
                    $this->addTime('Added selection of 1 <b>' . $class . '</b>: <small>' . str_replace('`' . $alias . '`.',
                            '', implode(',', $fields)) . '</small>', microtime(true) - $time);
                } else {
                    $this->query->select($fields);
                    if (is_array($fields)) {
                        $fields = current($fields) . ' AS ' . current(array_flip($fields));
                    }
                    $this->addTime('Added selection of 2 <b>' . $class . '</b>: <small>' . str_replace('`' . $alias . '`.',
                            '', $fields) . '</small>', microtime(true) - $time);
                }

                $i++;
                $time = microtime(true);
            }
        } else {
            $columns = array_keys($this->modx->getFieldMeta($this->config['class']));
            if (isset($this->config['includeContent']) && empty($this->config['includeContent']) && empty($this->config['useWeblinkUrl'])) {
                $key = array_search('content', $columns);
                if ($key !== false) {
                    unset($columns[$key]);
                }
            }
            $this->config['select'] = array($this->config['class'] => implode(',', $columns));
            $this->addSelects();
        }
    }


    /**
     * Group query by given field
     */
    public function addGrouping()
    {
        if (!empty($this->config['groupby'])) {
            $time = microtime(true);
            $groupby = $this->config['groupby'];
            $this->query->groupby($groupby);
            $this->addTime('Grouped by <b>' . $groupby . '</b>', microtime(true) - $time);
        }
    }


    /**
     * Add sort to query
     */
    public function addSort()
    {
        $time = microtime(true);
        $tmp = $this->config['sortby'];
        if (empty($tmp) || (is_string($tmp) && in_array(strtolower($tmp), array('resources', 'ids')))) {
            $resources = $this->config['class'] . '.' . $this->pk . ':IN';
            if (!empty($this->config['where'][$resources])) {
                $tmp = array(
                    'FIELD(`' . $this->config['class'] . '`.`' . $this->pk . '`,\'' . implode('\',\'',
                        $this->config['where'][$resources]) . '\')' => '',
                );
            } else {
                $tmp = array(
                    $this->config['class'] . '.' . $this->pk => !empty($this->config['sortdir'])
                        ? $this->config['sortdir']
                        : 'ASC',
                );
            }
        } elseif (is_string($tmp)) {
            $tmp = $tmp[0] == '{' || $tmp[0] == '['
                ? json_decode($tmp, true)
                : array($tmp => $this->config['sortdir']);
        }
        if (!empty($this->config['sortbyTV']) && !array_key_exists($this->config['sortbyTV'], $tmp)) {
            $tmp2[$this->config['sortbyTV']] = !empty($this->config['sortdirTV'])
                ? $this->config['sortdirTV']
                : (!empty($this->config['sortdir'])
                    ? $this->config['sortdir']
                    : 'ASC'
                );
            $tmp = array_merge($tmp2, $tmp);
            if (!empty($this->config['sortbyTVType'])) {
                $tv = strtolower($this->config['sortbyTV']);
                if (array_key_exists($tv, $this->config['tvsJoin'])) {
                    if (!empty($this->config['tvsJoin'][$tv]['tv'])) {
                        $this->config['tvsJoin'][$tv]['tv']['type'] = $this->config['sortbyTVType'];
                    }
                }
            }
        }

        $fields = $this->modx->getFields($this->config['class']);
        $sorts = $this->replaceTVCondition($tmp);
        foreach ($sorts as $sortby => $sortdir) {
            if (preg_match_all('#`TV(.*?)`#', $sortby, $matches)) {
                foreach ($matches[1] as $tv) {
                    if (array_key_exists($tv, $this->config['tvsJoin'])) {
                        $params = $this->config['tvsJoin'][$tv]['tv'];
                        switch ($params['type']) {
                            case 'number':
                            case 'decimal':
                                $sortby = preg_replace('#(TV' . $tv . '\.value|`TV' . $tv . '`\.`value`)#',
                                    'CAST($1 AS DECIMAL(13,3))', $sortby);
                                break;
                            case 'int':
                            case 'integer':
                                $sortby = preg_replace('#(TV' . $tv . '\.value|`TV' . $tv . '`\.`value`)#',
                                    'CAST($1 AS SIGNED INTEGER)', $sortby);
                                break;
                            case 'date':
                            case 'datetime':
                                $sortby = preg_replace('#(TV' . $tv . '\.value|`TV' . $tv . '`\.`value`)#',
                                    'CAST($1 AS DATETIME)', $sortby);
                                break;
                        }
                    }
                }
            } elseif (array_key_exists($sortby, $fields)) {
                $sortby = $this->config['class'] . '.' . $sortby;
            }
            // Escaping of columns names
            $tmp = explode(',', $sortby);
            array_walk($tmp, function (&$value) {
                if (strpos($value, '`') === false) {
                    $value = preg_replace('#(.*?)\.(.*?)\s#', '`$1`.`$2`', $value);
                }
            });
            $sortby = implode(',', $tmp);
            if (!in_array(strtoupper($sortdir), array('ASC', 'DESC', ''), true)) {
                $sortdir = 'ASC';
            }

            // Use reflection to check clause by protected method of xPDOQuery
            $isValidClause = new ReflectionMethod('xPDOQuery', 'isValidClause');
            $isValidClause->setAccessible(true);
            $isValidClause->invoke($this->query, $sortby);
            if (!$isValidClause->invoke($this->query, $sortby)) {
                $message = 'SQL injection attempt detected in sortby column; clause rejected';
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, $message);
                $this->addTime($message . ': ' . $sortby);
            } elseif (!empty($sortby)) {
                $this->query->query['sortby'][] = array(
                    'column' => $sortby,
                    'direction' => $sortdir
                );
                $this->addTime('Sorted by <b>' . $sortby . '</b>, <b>' . $sortdir . '</b>', microtime(true) - $time);
            }
        }
    }


    /**
     * Set parameters and prepare query
     *
     * @return PDOStatement
     */
    public function prepareQuery()
    {
        if ($limit = (int)$this->config['limit']) {
            $offset = (int)$this->config['offset'];
            $time = microtime(true);
            $this->query->limit($limit, $offset);
            $this->addTime('Limited to <b>' . $limit . '</b>, offset <b>' . $offset . '</b>', microtime(true) - $time);
        }
		
        return $this->query->prepare();
    }


    /**
     * Add selection of template variables to query
     */
    public function addTVs()
    {
        $time = microtime(true);

        $includeTVs = $this->config['includeTVs'];
        $tvPrefix = !empty($this->config['tvPrefix']) ?
            trim($this->config['tvPrefix'])
            : '';

        if (!empty($this->config['includeTVList']) && (empty($includeTVs) || is_numeric($includeTVs))) {
            $this->config['includeTVs'] = $includeTVs = $this->config['includeTVList'];
        }
        if (!empty($this->config['sortbyTV'])) {
            $includeTVs .= empty($includeTVs)
                ? $this->config['sortbyTV']
                : ',' . $this->config['sortbyTV'];
        }

        if (!empty($includeTVs)) {
            $class = !empty($this->config['joinTVsTo'])
                ? $this->config['joinTVsTo']
                : $this->config['class'];
            $subclass = preg_grep('#^' . $class . '#i', $this->modx->classMap['modResource']);
            if (!preg_match('#^modResource$#i', $class) && !count($subclass)) {
                $this->modx->log(modX::LOG_LEVEL_ERROR,
                    '[pdoTools] Could not join TVs to the class "' . $class . '" that is not a subclass of the "modResource". Try to specify correct class in the "joinTVsTo" parameter.');
            } else {
                $tvs = array_map('trim', explode(',', $includeTVs));
                $tvs = array_unique($tvs);
                if (!empty($tvs)) {
                    $q = $this->modx->newQuery('modTemplateVar', array('name:IN' => $tvs));
                    $q->select('id,name,type,default_text');
                    $tstart = microtime(true);
                    if ($q->prepare() && $q->stmt->execute()) {
                        $this->modx->queryTime += microtime(true) - $tstart;
                        $this->modx->executedQueries++;
                        $tvs = array();
                        while ($tv = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                            $name = strtolower($tv['name']);
                            $alias = 'TV' . $name;
                            $this->config['tvsJoin'][$name] = array(
                                'class' => 'modTemplateVarResource',
                                'alias' => $alias,
                                'on' => '`TV' . $name . '`.`contentid` = `' . $class . '`.`id` AND `TV' . $name . '`.`tmplvarid` = ' . $tv['id'],
                                'tv' => $tv,
                            );
                            $this->config['tvsSelect'][$alias] = array('`' . $tvPrefix . $tv['name'] . '`' => 'IFNULL(`' . $alias . '`.`value`, ' . $this->modx->quote($tv['default_text']) . ')');
                            $tvs[] = $tv['name'];
                        }

                        $this->addTime('Included list of tvs: <b>' . implode(', ', $tvs) . '</b>',
                            microtime(true) - $time);
                    }
                }
            }
        }
    }


    /**
     * This method handles popular parameters and adds conditions to query
     *
     * @param array $where Current conditions
     *
     * @return array
     */
    public function additionalConditions($where = array())
    {
        $config = $this->config;
        $class = $this->config['class'];

        // These rules works only for descendants of modResource
        if (!in_array('modResource', $this->ancestry) || !empty($config['disableConditions'])) {
            return $where;
        }
        $time = microtime(true);

        $params = array(
            'resources' => 'id',
            'parents' => 'parent',
            'templates' => 'template',
            'showUnpublished' => 'published',
            'showHidden' => 'hidemenu',
            'showDeleted' => 'deleted',
            'hideContainers' => 'isfolder',
            'hideUnsearchable' => 'searchable',
            'context' => 'context_key',
        );

        // Exclude parameters that may already have been processed
        foreach ($params as $param => $field) {
            $found = false;
            if (isset($config[$param])) {
                foreach ($where as $k => $v) {
                    // Usual condition
                    if (!is_numeric($k) && strpos($k, $field) === 0 || strpos($k, $class . '.' . $field) !== false) {
                        $found = true;
                        break;
                    } // Array of conditions
                    elseif (is_numeric($k) && is_array($v)) {
                        foreach ($v as $k2 => $v2) {
                            if (strpos($k2, $field) === 0 || strpos($k2, $class . '.' . $field) !== false) {
                                $found = true;
                                break(2);
                            }
                        }
                    } // Raw SQL string
                    elseif (is_numeric($k) && strpos($v, $class) !== false && preg_match('/\b' . $field . '\b/i', $v)) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    unset($params[$param]);
                } else {
                    $params[$param] = $config[$param];
                }
            } else {
                unset($params[$param]);
            }
        }

        // Process the remaining parameters
        foreach ($params as $param => $value) {
            switch ($param) {
                case 'showUnpublished':
                    if (empty($value)) {
                        $where[$class . '.published'] = 1;
                    }
                    break;
                case 'showHidden':
                    if (empty($value)) {
                        $where[$class . '.hidemenu'] = 0;
                    }
                    break;
                case 'showDeleted':
                    if (empty($value)) {
                        $where[$class . '.deleted'] = 0;
                    }
                    break;
                case 'hideContainers':
                    if (!empty($value)) {
                        $where[$class . '.isfolder'] = 0;
                    }
                    break;
                case 'hideUnsearchable':
                    if (!empty($value)) {
                        $where[$class . '.searchable'] = 1;
                    }
                    break;
                case 'context':
                    if (!empty($value)) {
                        $context = array_map('trim', explode(',', $value));
                        if (!empty($context) && is_array($context)) {
                            if (count($context) == 1) {
                                $where[$class . '.context_key'] = $context[0];
                            } else {
                                $where[$class . '.context_key:IN'] = $context;
                            }
                        }
                    }
                    break;
                case 'resources':
                    if (!empty($value)) {
                        $resources = array_map('trim', explode(',', $value));
                        $resources_in = $resources_out = array();
                        foreach ($resources as $v) {
                            if (!is_numeric($v)) {
                                continue;
                            }
                            if ($v < 0) {
                                $resources_out[] = abs($v);
                            } else {
                                $resources_in[] = abs($v);
                            }
                        }
                        if (!empty($resources_in)) {
                            $where[$class . '.id:IN'] = $resources_in;
                        }
                        if (!empty($resources_out)) {
                            $where[$class . '.id:NOT IN'] = $resources_out;
                        }
                    }
                    break;
                case 'parents':
                    if (!empty($value)) {
                        $parents = array_map('trim', explode(',', $value));
                        $parents_in = $parents_out = array();
                        foreach ($parents as $v) {
                            if (!is_numeric($v)) {
                                continue;
                            }
                            if ($v[0] == '-') {
                                $parents_out[] = abs($v);
                            } else {
                                $parents_in[] = abs($v);
                            }
                        }
                        $depth = (isset($config['depth']) && $config['depth'] !== '')
                            ? (int)$config['depth']
                            : 10;
                        if (!empty($depth) && $depth > 0) {
                            $pids = array();
                            $q = $this->modx->newQuery($class,
                                array('id:IN' => array_merge($parents_in, $parents_out)));
                            $q->select('id,context_key');
                            $tstart = microtime(true);
                            if ($q->prepare() && $q->stmt->execute()) {
                                $this->modx->queryTime += microtime(true) - $tstart;
                                $this->modx->executedQueries++;
                                while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $pids[$row['id']] = $row['context_key'];
                                }
                            }
                            foreach ($pids as $k => $v) {
                                if (!is_numeric($k)) {
                                    continue;
                                } elseif (in_array($k, $parents_in)) {
                                    $parents_in = array_merge($parents_in,
                                        $this->modx->getChildIds($k, $depth, array('context' => $v)));
                                } else {
                                    $parents_out = array_merge($parents_out,
                                        $this->modx->getChildIds($k, $depth, array('context' => $v)));
                                }
                            }
                            if (empty($parents_in)) {
                                $parents_in = $this->modx->getChildIds(0, $depth,
                                    array('context' => $this->config['context']));
                            }
                        }
                        // Support of miniShop2 categories
                        $members = array();
                        if (in_array('msCategory', $this->modx->classMap['modResource']) &&
                            empty($this->config['disableMS2'])
                        ) {
                            if (!empty($parents_in) || !empty($parents_out)) {
                                $q = $this->modx->newQuery('msCategoryMember');
                                if (!empty($parents_in)) {
                                    $q->where(array('category_id:IN' => $parents_in));
                                }
                                if (!empty($parents_out)) {
                                    $q->where(array('category_id:NOT IN' => $parents_out));
                                }
                                $q->select('product_id');
                                $tstart = microtime(true);
                                if ($q->prepare() && $q->stmt->execute()) {
                                    $this->modx->queryTime += microtime(true) - $tstart;
                                    $this->modx->executedQueries++;
                                    $members = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
                                }
                            }
                        }
                        // Add parent to conditions
                        if (!empty($parents_in) && !empty($members)) {
                            if (!empty($this->config['includeParents'])) {
                                $members = array_merge($members, $parents_in);
                            }
                            $where[] = array(
                                $class . '.parent:IN' => $parents_in,
                                'OR:' . $class . '.id:IN' => $members,
                            );
                        } elseif (!empty($parents_in) && !empty($this->config['includeParents'])) {
                            $where[] = array(
                                $class . '.parent:IN' => $parents_in,
                                'OR:' . $class . '.id:IN' => $parents_in,
                            );
                        } elseif (!empty($parents_in)) {
                            $where[$class . '.parent:IN'] = $parents_in;
                        }
                        if (!empty($parents_out) && !empty($this->config['includeParents'])) {
                            $where[] = array(
                                $class . '.parent:NOT IN' => $parents_out,
                                'AND:' . $class . '.id:NOT IN' => $parents_out,
                            );
                        } elseif (!empty($parents_out)) {
                            $where[$class . '.parent:NOT IN'] = $parents_out;
                        }
                    }
                    break;
                case 'templates':
                    if (!empty($value)) {
                        $templates = array_map('trim', explode(',', $value));
                        $templates_in = $templates_out = array();
                        foreach ($templates as $v) {
                            if (!is_numeric($v)) {
                                continue;
                            }
                            if ($v[0] == '-') {
                                $templates_out[] = abs($v);
                            } else {
                                $templates_in[] = abs($v);
                            }
                        }
                        if (!empty($templates_in)) {
                            $where[$class . '.template:IN'] = $templates_in;
                        }
                        if (!empty($templates_out)) {
                            $where[$class . '.template:NOT IN'] = $templates_out;
                        }
                    }
                    break;
            }
        }
        $this->config['where'] = $where;

        $this->addTime('Processed additional conditions', microtime(true) - $time);

        return $where;
    }


    /**
     * Convert tvFilters string to SQL and add to "where" condition
     * This algorithm taken from snippet getResources by opengeek
     */
    public function addTVFilters()
    {
        $time = microtime(true);

        if (empty($this->config['tvFilters'])) {
            return;
        }
        $tvFiltersAndDelimiter = $this->config['tvFiltersAndDelimiter'];
        $tvFiltersOrDelimiter = $this->config['tvFiltersOrDelimiter'];

        $tvFilters = array_map('trim', explode($tvFiltersOrDelimiter, $this->config['tvFilters']));
        $operators = array(
            '<=>' => '<=>',
            '===' => '=',
            '!==' => '!=',
            '<>' => '<>',
            '==' => 'LIKE',
            '!=' => 'NOT LIKE',
            '<<' => '<',
            '<=' => '<=',
            '=<' => '=<',
            '>>' => '>',
            '>=' => '>=',
            '=>' => '=>',
        );

        $includeTVs = !empty($this->config['includeTVs'])
            ? array_map('trim', explode(',', $this->config['includeTVs']))
            : array();
        $where = array();
        if (!empty($this->config['where'])) {
            $tmp = $this->config['where'];
            if (is_string($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
                $where = json_decode($tmp, true);
            }
            if (!is_array($where)) {
                $where = array($where);
            }
        }

        $conditions = array();
        foreach ($tvFilters as $tvFilter) {
            $condition = array();
            $filters = explode($tvFiltersAndDelimiter, $tvFilter);
            foreach ($filters as $filter) {
                $operator = '==';
                $sqlOperator = 'LIKE';
                foreach ($operators as $op => $opSymbol) {
                    if (strpos($filter, $op, 1) !== false) {
                        $operator = $op;
                        $sqlOperator = $opSymbol;
                        break;
                    }
                }
                $filter = array_map('trim', explode($operator, $filter));
                if (!in_array($filter[0], $includeTVs)) {
                    $includeTVs[] = $filter[0];
                }
                $condition[] = $filter[0] . ' ' . $sqlOperator . ' ' . $this->modx->quote($filter[1]);
            }
            $conditions[] = implode(' AND ', $condition);
        }
        if (count($conditions) > 1) {
            $where[] = '((' . implode(') OR (', $conditions) . '))';
        } else {
            $where[] = $conditions[0];
        }

        $this->config['includeTVs'] = implode(',', $includeTVs);
        $this->config['where'] = $where;
        $this->addTime('Added TVs filters', microtime(true) - $time);
    }


    /**
     * Replaces tv fields to full name format.
     * For example, field "test" will be replaced with "TVtest.value", if template variable "test" was joined in query.
     *
     * @param array $array Array for replacement
     *
     * @return array $sorts Array with replaced conditions
     */
    public function replaceTVCondition(array $array)
    {
        if (empty($this->config['tvsJoin'])) {
            return $array;
        }

        $time = microtime(true);
        $tvs = implode('|', array_keys($this->config['tvsJoin']));

        $sorts = array();
        foreach ($array as $k => $v) {
            $callback = function($matches) {
                return '`TV' . strtolower($matches[1]). '`.`value`';
            };
            if (is_numeric($k) && is_string($v)) {
                $tmp = preg_replace_callback('/\b(' . $tvs . ')\b/i', $callback, $v);
                $sorts[$k] = $tmp;
            } elseif (is_numeric($k) && is_array($v)) {
                $sorts[$k] = $this->replaceTVCondition($v);
            } else {
                $tmp = preg_replace_callback('/\b(' . $tvs . ')\b/i', $callback, $k);
                $sorts[$tmp] = $v;
            }
        }
        $this->addTime('Replaced TV conditions', microtime(true) - $time);

        return $sorts;
    }


    /**
     * Alias for getArray method
     *
     * @param $class
     * @param string $where
     * @param array $config
     *
     * @return array
     */
    public function getObject($class, $where = '', $config = array())
    {
        return $this->getArray($class, $where, $config);
    }


    /**
     * PDO replacement for modX::getObject()
     * Returns array instead of object
     *
     * @param $class
     * @param string $where
     * @param array $config
     *
     * @return array
     */
    public function getArray($class, $where = '', $config = array())
    {
        $config['limit'] = 1;
        $rows = $this->getCollection($class, $where, $config);

        return !empty($rows[0])
            ? $rows[0]
            : array();
    }


    /**
     * PDO replacement for modX::getCollection()
     *
     * @param $class
     * @param string $where
     * @param array $config
     *
     * @return array|boolean
     */
    public function getCollection($class, $where = '', $config = array())
    {
        /** @var pdoFetch $instance */
        $fqn = $this->modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
        $path = $this->modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/',
            true);
        if ($pdoClass = $this->modx->loadClass($fqn, $path, false, true)) {
            $instance = new $pdoClass($this->modx, $config);
        } else {
            return false;
        }

        $config['class'] = $class;
        $config['limit'] = !isset($config['limit'])
            ? 0
            : (int)$config['limit'];
        if (!empty($where)) {
            unset($config['where']);
            if (is_numeric($where)) {
                $where = array($instance->modx->getPK($class) => (int)$where);
            } elseif (is_string($where) && ($where[0] == '{' || $where[0] == '[')) {
                $where = json_decode($where, true);
            }
            if (is_array($where)) {
                $config['where'] = $where;
            }
        }

        $instance->setConfig($config, true);
        $instance->makeQuery();
        $instance->genSubPdo();
        $instance->addTVFilters();
        $instance->addTVs();
        $instance->addJoins();
        $instance->addGrouping();
        $instance->addSelects();
        $instance->addWhere();
        $instance->addSort();
        $instance->prepareQuery();
        $instance->modx->exec('SET SQL_BIG_SELECTS = 1');
        $instance->addTime('SQL prepared <small>"' . $instance->query->toSQL() . '"</small>');

        $rows = '';
        $tstart = microtime(true);
        if ($instance->query->stmt->execute()) {
            $instance->addTime('SQL executed', microtime(true) - $tstart);
            $instance->modx->queryTime += microtime(true) - $tstart;
            $instance->modx->executedQueries++;
            $tstart = microtime(true);
            if (!$rows = $instance->query->stmt->fetchAll(PDO::FETCH_ASSOC)) {
                $rows = array();
            } else {
                $rows = $instance->checkPermissions($rows);
                $rows = $instance->prepareRows($rows);
            }
            $instance->addTime('Total rows: ' . count($rows));
            $instance->addTime('Rows are fetched', microtime(true) - $tstart);
        } else {
            $errors = $instance->query->stmt->errorInfo();
            $instance->modx->log(modX::LOG_LEVEL_ERROR,
                '[pdoTools] Could not load collection of "' . $class . '": Error ' . $errors[0] . ': ' . $errors[2]);
            $instance->addTime('Could not process query, error #' . $errors[1] . ': ' . $errors[2]);
        }

        $this->modx->setPlaceholder('pdoTools.log', $instance->getTime());

        return $rows;
    }


    /**
     * Gets all of the child objects ids for a given object.
     *
     * @param string $class Object class
     * @param integer $id The object parent id for the starting node.
     * @param integer $depth How many levels max to search for children (default 10).
     * @param array $options An array of options, such as 'where','parent_field','depth', 'sortby' etc.
     *
     * @return array
     */
    public function getChildIds($class, $id, $depth = 10, array $options = array())
    {
        $ids = array();
        $where = isset($options['where']) && is_array($options['where'])
            ? $options['where']
            : array();
        $id_field = !empty($options['id_field'])
            ? $options['id_field']
            : 'id';
        $parent_field = !empty($options['parent_field'])
            ? $options['parent_field']
            : 'parent';
        if (empty($options['select'])) {
            $options['select'] = $id_field;
        }
        $options['return'] = 'ids';

        if ($id !== null && intval($depth) >= 1) {
            $where[$parent_field] = (int)$id;
            $children = $this->getCollection($class, $where, $options);
            foreach ($children as $child) {
                $ids[] = $child[$id_field];
                if ($tmp = $this->getChildIds($class, $child[$id_field], $depth - 1, $options)) {
                    $ids = array_merge($ids, $tmp);
                }
            }
        }

        return $ids;
    }

}
