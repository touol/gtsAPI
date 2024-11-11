<?php
/**
 * @package gtsShop
 */

include_once(XPDO_CORE_PATH . 'Om/' . $this->modx->config['dbtype'] . '/xPDOGenerator.php');

class gtsShopManager_mysql extends \xPDO\Om\mysql\xPDOGenerator
{

    private $after_field = 'cost';
    /**
     * active data base to connect to
     * @var (String) $database
     */
    protected $databaseName;

    /**
     * set the database
     *
     */
    public function setDatabase($database = NULL)
    {
        if (empty($database)) {
            $this->databaseName = $this->manager->xpdo->escape($this->manager->xpdo->config['dbname']);
        } else {
            $this->databaseName = $database;
        }
    }

    /**
     * @param string $outputDir
     * @param string $className
     * @return bool
     */
    public function generateMap($outputDir = '', $fields = array(), $className = 'msProductData')
    {
        //$extendFields = array_keys($this->manager->xpdo->getFields('msProductData'));
        $extendFields = array('id', 'article', 'price', 'old_price', 'weight', 'image', 'thumb', 'vendor', 'made_in', 'new', 'popular', 'favorite', 'tags', 'color', 'size', 'source');
        $sql = 'SHOW FULL COLUMNS FROM ' . $this->manager->xpdo->config['dbname'] . '.' . $this->manager->xpdo->escape($this->manager->xpdo->getTableName($className));
        $sql .= ' WHERE Field NOT IN ("' . implode('", "', $extendFields) . '")';
        //$this->manager->xpdo->log(modX::LOG_LEVEL_ERROR, $sql);
        $this->map = array('fields' => array(), 'fieldMeta' => array());
        if ($fieldsStmt = $this->manager->xpdo->query($sql)) {
            $fields = $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $data = array();
                    $Field = '';
                    $Type = '';
                    $Null = '';
                    $Key = '';
                    $Default = '';
                    $Comment = '';
                    $Extra = '';
                    extract($field, EXTR_OVERWRITE);
                    if ($Comment) {
                        if (!$data = unserialize($Comment)) {
                            $data = array();
                        }
                    }
                    $Type = xPDO:: escSplit(' ', $Type, "'", 2);
                    $precisionPos = strpos($Type[0], '(');
                    $dbType = $precisionPos ? substr($Type[0], 0, $precisionPos) : $Type[0];
                    $dbType = strtolower($dbType);
                    $Precision = $precisionPos ? substr($Type[0], $precisionPos + 1, strrpos($Type[0], ')') - ($precisionPos + 1)) : '';
                    /*if (!empty ($Precision)) {
                        $Precision= ' precision="' . trim($Precision) . '"';
                    }*/
                    $attributes = '';
                    if (isset ($Type[1]) && !empty ($Type[1])) {
                        $attributes = ' attributes="' . trim($Type[1]) . '"';
                    }
                    $PhpType = $data['phptype'] ? $data['phptype'] : $this->manager->xpdo->driver->getPhpType($dbType);
                    $Null = $Null === 'NO' ? 'false' : 'true';
                    $Key = $this->getIndex($Key);
                    //$Default= $this->getDefault($Default);

                    $defaultType = $this->manager->xpdo->driver->getPhpType($dbType);
                    $this->map['fields'][$Field] = null;
                    $this->map['fieldMeta'][$Field] = array();
                    if ($Default === 'NULL') {
                        $Default = null;
                    }
                    switch ($defaultType) {
                        case 'integer':
                        case 'boolean':
                        case 'bit':
                            $Default = (integer)$Default;
                            break;
                        case 'float':
                        case 'numeric':
                            $Default = (float)$Default;
                            break;
                        default:
                            break;
                    }
                    $this->map['fields'][$Field] = $Default;

                    $this->map['fieldMeta'][$Field]['dbtype'] = $dbType;
                    $this->map['fieldMeta'][$Field]['label'] = !empty($data['label']) ? $data['label'] : $Field;
                    $this->map['fieldMeta'][$Field]['desc'] = !empty($data['desc']) ? $data['desc'] : '';
                    $this->map['fieldMeta'][$Field]['precision'] = $Precision;
                    $this->map['fieldMeta'][$Field]['phptype'] = $PhpType;
                    $this->map['fieldMeta'][$Field]['default'] = $Default;
                    $this->map['fieldMeta'][$Field]['null'] = (!empty($Null) && strtolower($Null) !== 'false') ? true : false;
                }
            }
        }
        $this->outputMap($outputDir);
        unset($this->map);
        return true;
    }


    /**
     * @param array $map
     */
    public function setMap($map = array())
    {
        $this->map = $map;
    }
    
    /**
     * Gets the map header template.
     *
     * @access public
     * @return string The map header template.
     */
    public function getMapHeader() {
        if ($this->mapHeader) return $this->mapHeader;
        $header= <<<EOD
<?php
EOD;
        return $header;
    }
    /**
     * @param string $path
     * @param string $className
     */
    public function outputMap($path, $className = 'msProductData')
    {
        if (!is_dir($path)) {
            mkdir($path, 0777);
        }
        $fileName = $path . '/' . strtolower($className) . '.map.inc.php';
        $fileContent = $this->getMapHeader();
        $fileContent .= "\n return " . var_export($this->map, true) . ";\n";
        if (is_dir($path)) {
            if ($file = @ fopen($fileName, 'wb')) {
                if (!fwrite($file, $fileContent)) {
                    $this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Could not write to file: {$fileName}");
                }
                fclose($file);
            } else {
                $this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Could not open or create file: {$fileName}");
            }
        } else {
            $this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Could not open or create dir: {$path}");
        }
    }

    /**
     * @param array $data
     * @param string $className
     * @param string $position
     * @return bool
     */
    public function addField($data, $className = 'msProductData', $position = 'first')
    {
        $sql = "ALTER TABLE {$this->manager->xpdo->getTableName($className)} ADD COLUMN " . $this->getColumnDef($data);
        if ($position == 'first') {
            $sql .= " AFTER {$this->manager->xpdo->escape($this->after_field)}";
        } else {
            $sql .= " AFTER {$this->manager->xpdo->escape($position)}";
        }
        if ($this->manager->xpdo->exec($sql) !== false) {
            return true;
        } else {
            $this->manager->xpdo->log(modX::LOG_LEVEL_ERROR, "Error adding field {$className}->{$data['name']}: " . print_r($this->manager->xpdo->errorInfo(), true), '', __METHOD__, __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * @param array $data
     * @param string $className
     * @return bool
     */
    public function alterField($data, $className = 'msProductData', $position = 'first')
    {
        $sql = "ALTER TABLE {$this->manager->xpdo->getTableName($className)} MODIFY COLUMN " . $this->getColumnDef($data);
        if ($position == 'first') {
            $sql .= " AFTER {$this->manager->xpdo->escape($this->after_field)}";
            //$sql .= " FIRST";
        } else {
            $sql .= " AFTER {$this->manager->xpdo->escape($position)}";
        }
        if ($this->manager->xpdo->exec($sql) !== false) {
            return true;
        } else {
            $this->manager->xpdo->log(modX::LOG_LEVEL_ERROR, "Error altering field {$className}->{$data['name']}: " . print_r($this->manager->xpdo->errorInfo(), true), '', __METHOD__, __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * @param string $name
     * @param string $className
     * @return bool
     */
    public function removeField($name, $className = 'msProductData')
    {
        $sql = "ALTER TABLE {$this->manager->xpdo->getTableName($className)} DROP COLUMN {$this->manager->xpdo->escape($name)}";
        if ($this->manager->xpdo->exec($sql) !== false) {
            return true;
        } else {
            $this->manager->xpdo->log(modX::LOG_LEVEL_ERROR, "Error removing field {$className}->{$name}: " . print_r($this->manager->xpdo->errorInfo(), true), '', __METHOD__, __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * @param array $data
     * @param string $className
     * @return bool
     */
    public function addIndex($data = array(), $className = 'msProductData')
    {
        if (!$index = $this->getIndexType($data)) return true;
        $field = $data['name'];
        if (!empty($this->hasIndex($field, $className))) return true;
        $sql = "ALTER TABLE {$this->manager->xpdo->getTableName($className)} ADD {$index} ({$field})";
        //$this->manager->xpdo->log(modX::LOG_LEVEL_ERROR, $sql);
        if ($this->manager->xpdo->exec($sql) !== false) {
            return true;
        } else {
            $this->manager->xpdo->log(modX::LOG_LEVEL_ERROR, "Error add index {$className}->{$data['name']}: " . print_r($this->manager->xpdo->errorInfo(), true), '', __METHOD__, __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * @param array $data
     * @param string $className
     * @return bool
     */
    public function removeIndex($data = array(), $className = 'msProductData')
    {
        if (!$index = $this->getIndexType($data)) return true;
        $field = $data['name'];
        if (empty($this->hasIndex($field, $className))) return true;
        $sql = "ALTER TABLE {$this->manager->xpdo->getTableName($className)} DROP INDEX {$field}";
       // $this->manager->xpdo->log(modX::LOG_LEVEL_ERROR, $sql);
        if ($this->manager->xpdo->exec($sql) !== false) {
            return true;
        } else {
            $this->manager->xpdo->log(modX::LOG_LEVEL_ERROR, "Error remove index {$className}->{$data['name']}: " . print_r($this->manager->xpdo->errorInfo(), true), '', __METHOD__, __FILE__, __LINE__);
            return false;
        }
    }


    private function hasIndex($name, $className = 'msProductData')
    {
        $sql = "SHOW INDEX FROM {$this->manager->xpdo->getTableName($className)} WHERE Key_name = '{$name}';";
        $q = $this->manager->xpdo->prepare($sql);
        $q->execute();
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    private function getIndexType($data = array())
    {
        if (isset($data['dbindex']) && !empty($data['dbindex']) && $data['dbindex'] != 'no') {
            return $data['dbindex'];
        }
        return '';
    }

    protected function getColumnDef($data = array())
    {
        $default = '';
        $dbtype = strtoupper($data['dbtype']);
        $precision = !empty($data['dbprecision']) ? '(' . $data['dbprecision'] . ')' : '';
        $lobs = array('TEXT', 'BLOB');
        $lobsPattern = '/(' . implode('|', $lobs) . ')/';
        $datetimeStrings = array('timestamp', 'datetime');
        $null = $data['dbnull'] == 'true' ? ' NULL' : ' NOT NULL';
        $comment = '';
        //if(empty($data['dbdefault'])) $data['dbdefault'] = 'NULL';
        if (isset ($data['dbdefault']) && $data['dbdefault'] != 'none' && !preg_match($lobsPattern, $dbtype)) {
            $defaultVal = $data['dbdefault'];
            if (($defaultVal === null || strtoupper($defaultVal) === 'NULL') || (in_array($this->manager->xpdo->driver->getPhpType($dbtype), $datetimeStrings) && $defaultVal === 'CURRENT_TIMESTAMP')) {
                $default = ' DEFAULT ' . $defaultVal;
            } elseif (!empty($data['default_value'])) {
                $default = ' DEFAULT \'' . $data['default_value'] . '\'';
            }
        }
        return $result = $this->manager->xpdo->escape($data['name']) . ' ' . $dbtype . $precision . $null . $default . $comment;
    }

    public function varDumpToString($var)
    {
        ob_start();
        var_dump($var);
        $result = ob_get_clean();
        return $result;
    }

    /**
     * Formats a class name to a specific value, stripping the prefix if
     * specified.
     *
     * @access public
     * @param string $string The name to format.
     * @param string $prefix If specified, will strip the prefix out of the
     * first argument.
     * @param boolean $prefixRequired If true, will return a blank string if the
     * prefix specified is not found.
     * @return string The formatting string.
     */
    public function getTableName($string, $prefix = '', $prefixRequired = false)
    {
        if (!empty($prefix) && strpos($string, $prefix) === 0) {
            $string = substr($string, strlen($prefix));
        } elseif ($prefixRequired) {
            $string = '';
        }
        return $string;
    }

    /**
     * Gets a class name from a table name by splitting the string by _ and
     * capitalizing each token.
     *
     * @access public
     * @param string $string The table name to format.
     * @return string The formatted string.
     */
    public function getClassName($string)
    {
        if (is_string($string) && $strArray = explode('_', $string)) {
            $return = '';
            while (list($k, $v) = each($strArray)) {
                $return .= strtoupper(substr($v, 0, 1)) . substr($v, 1) . '';
            }
            $string = $return;
        }
        return trim($string);
    }

    /**
     * set the allowed tables
     *
     */
    public function setAllowedTables(array $tables = array())
    {
        $this->allowed_tables = $tables;
        /*
        echo '<br>Table Array: ';
        print_r($tables);
        echo '<br>';
        */
    }

    public function getAllTables($tablePrefix = '', $restrictPrefix = false)
    {
        if (empty ($tablePrefix))
            $tablePrefix = $this->manager->xpdo->config[xPDO::OPT_TABLE_PREFIX];
        $dbname = $this->databaseName;
        $tableLike = ($tablePrefix && $restrictPrefix) ? " LIKE '{$tablePrefix}%'" : '';
        $tablesStmt = $this->manager->xpdo->prepare("SHOW TABLES FROM {$dbname}{$tableLike}");
        $tablesStmt->execute();
        $tables = $tablesStmt->fetchAll(PDO::FETCH_NUM);
        if ($this->manager->xpdo->getDebug() === true) {
            $this->manager->xpdo->log(xPDO::LOG_LEVEL_DEBUG, print_r($tables, true));
        }
        $out = array();
        foreach ($tables as $table) {
            // End custom
            if (!$tableName = $this->getTableName($table[0], $tablePrefix, $restrictPrefix)) {
                continue;
            }
            $className = $this->getClassName($tableName);
            $out[$table[0]] = $className;
        }
        return $out;
    }

    public function geTableFields($table)
    {
        $sql = 'SHOW FULL COLUMNS FROM ' . $this->manager->xpdo->config['dbname'] . '.' . $this->manager->xpdo->escape($table);
        $out = array();
        if ($fieldsStmt = $this->manager->xpdo->query($sql)) {
            $fields = $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $out[] = $field['Field'];
                }
            }
        }
        return $out;
    }

    /**
     * This only generates scheme files for specified tables rather then the entire database
     *
     * Write an xPDO XML Schema from your database.
     *
     * @param string $schemaFile The name (including path) of the schemaFile you
     * want to write.
     * @param string $package Name of the package to generate the classes in.
     * @param string $baseClass The class which all classes in the package will
     * extend; by default this is set to {@link xPDOObject} and any
     * auto_increment fields with the column name 'id' will extend {@link
     * xPDOSimpleObject} automatically.
     * @param string $tablePrefix The table prefix for the current connection,
     * which will be removed from all of the generated class and table names.
     * Specify a prefix when creating a new {@link xPDO} instance to recreate
     * the tables with the same prefix, but still use the generic class names.
     * @param boolean $restrictPrefix Only reverse-engineer tables that have the
     * specified tablePrefix; if tablePrefix is empty, this is ignored.
     * @return boolean True on success, false on failure.
     */
    public function writeTableSchema($schemaFile, $package = '', $baseClass = '', $tablePrefix = '', $restrictPrefix = false)
    {
        if (empty ($package))
            $package = $this->manager->xpdo->package;
        if (empty ($baseClass))
            $baseClass = 'xPDOObject';
        if (empty ($tablePrefix))
            $tablePrefix = $this->manager->xpdo->config[xPDO::OPT_TABLE_PREFIX];
        $schemaVersion = xPDO::SCHEMA_VERSION;
        $xmlContent = array();
        $xmlContent[] = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $xmlContent[] = "<model package=\"{$package}\" baseClass=\"{$baseClass}\" platform=\"mysql\" defaultEngine=\"MyISAM\" version=\"{$schemaVersion}\">";
        //read list of tables
        $dbname = $this->databaseName;
        //$this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'Database name: ' . $dbname);
        $tableLike = ($tablePrefix && $restrictPrefix) ? " LIKE '{$tablePrefix}%'" : '';
        $tablesStmt = $this->manager->xpdo->prepare("SHOW TABLES FROM {$dbname}{$tableLike}");
        $tablesStmt->execute();
        $tables = $tablesStmt->fetchAll(PDO::FETCH_NUM);
        if ($this->manager->xpdo->getDebug() === true) {
            $this->manager->xpdo->log(xPDO::LOG_LEVEL_DEBUG, print_r($tables, true));
        }
        foreach ($tables as $table) {
            $xmlObject = array();
            $xmlFields = array();
            $xmlIndices = array();
            // the only thing added to this function the rest is copied:
            if (!in_array($table[0], $this->allowed_tables)) {
                //echo '<br>No Table: '.$table[0];
                //$this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'CMPGenerator->my_oPDO0->writeTableSchema -> No Table: '.$table[0]);
                continue;
            }
            //echo '<br>Table: '. $table[0];
            //$this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'CMPGenerator->my_oPDO0->writeTableSchema -> Table: '.$table[0].' - Pre: '.$tablePrefix.' - Restrict: '.$restrictPrefix );

            // End custom
            if (!$tableName = $this->getTableName($table[0], $tablePrefix, $restrictPrefix)) {
                continue;
            }
            $class = $this->getClassName($tableName);
            $extends = $baseClass;
            $sql = 'SHOW COLUMNS FROM ' . $this->manager->xpdo->escape($dbname) . '.' . $this->manager->xpdo->escape($table[0]);
            //$this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'Line: '.__LINE__.' Sql: '.$sql);
            $fieldsStmt = $this->manager->xpdo->query($sql);
            if ($fieldsStmt) {
                $fields = $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);
                if ($this->manager->xpdo->getDebug() === true) $this->manager->xpdo->log(xPDO::LOG_LEVEL_DEBUG, print_r($fields, true));
                if (!empty($fields)) {
                    foreach ($fields as $field) {
                        $Field = '';
                        $Type = '';
                        $Null = '';
                        $Key = '';
                        $Default = '';
                        $Extra = '';
                        extract($field, EXTR_OVERWRITE);
                        $Type = xPDO:: escSplit(' ', $Type, "'", 2);
                        $precisionPos = strpos($Type[0], '(');
                        $dbType = $precisionPos ? substr($Type[0], 0, $precisionPos) : $Type[0];
                        $dbType = strtolower($dbType);
                        $Precision = $precisionPos ? substr($Type[0], $precisionPos + 1, strrpos($Type[0], ')') - ($precisionPos + 1)) : '';
                        if (!empty ($Precision)) {
                            $Precision = ' precision="' . trim($Precision) . '"';
                        }
                        $attributes = '';
                        if (isset ($Type[1]) && !empty ($Type[1])) {
                            $attributes = ' attributes="' . trim($Type[1]) . '"';
                        }
                        $PhpType = $this->manager->xpdo->driver->getPhpType($dbType);
                        $Null = ' null="' . (($Null === 'NO') ? 'false' : 'true') . '"';
                        $Key = $this->getIndex($Key);
                        $Default = $this->getDefault($Default);
                        if (!empty ($Extra)) {
                            if ($Extra === 'auto_increment') {
                                if ($baseClass === 'xPDOObject' && $Field === 'id') {
                                    $extends = 'xPDOSimpleObject';
                                    continue;
                                } else {
                                    $Extra = ' generated="native"';
                                }
                            } else {
                                $Extra = ' extra="' . strtolower($Extra) . '"';
                            }
                            $Extra = ' ' . $Extra;
                        }
                        $xmlFields[] = "\t\t<field key=\"{$Field}\" dbtype=\"{$dbType}\"{$Precision}{$attributes} phptype=\"{$PhpType}\"{$Null}{$Default}{$Key}{$Extra} />";
                    }
                } else {
                    $this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'No columns were found in table ' . $table[0]);
                }
            } else {
                $this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'Error retrieving columns for table ' . $table[0]);
            }
            $whereClause = ($extends === 'xPDOSimpleObject' ? " WHERE `Key_name` != 'PRIMARY'" : '');
            $indexesStmt = $this->manager->xpdo->query('SHOW INDEXES FROM ' . $this->manager->xpdo->escape($dbname) . '.' . $this->manager->xpdo->escape($table[0]) . $whereClause);
            if ($indexesStmt) {
                $indexes = $indexesStmt->fetchAll(PDO::FETCH_ASSOC);
                if ($this->manager->xpdo->getDebug() === true) $this->manager->xpdo->log(xPDO::LOG_LEVEL_DEBUG, "Indices for table {$table[0]}: " . print_r($indexes, true));
                if (!empty($indexes)) {
                    $indices = array();
                    foreach ($indexes as $index) {
                        if (!array_key_exists($index['Key_name'], $indices)) $indices[$index['Key_name']] = array();
                        $indices[$index['Key_name']][$index['Seq_in_index']] = $index;
                    }
                    foreach ($indices as $index) {
                        $xmlIndexCols = array();
                        if ($this->manager->xpdo->getDebug() === true) $this->manager->xpdo->log(xPDO::LOG_LEVEL_DEBUG, "Details of index: " . print_r($index, true));
                        foreach ($index as $columnSeq => $column) {
                            if ($columnSeq == 1) {
                                $keyName = $column['Key_name'];
                                $primary = $keyName == 'PRIMARY' ? 'true' : 'false';
                                $unique = empty($column['Non_unique']) ? 'true' : 'false';
                                $packed = empty($column['Packed']) ? 'false' : 'true';
                                $type = $column['Index_type'];
                            }
                            $null = $column['Null'] == 'YES' ? 'true' : 'false';
                            $xmlIndexCols[] = "\t\t\t<column key=\"{$column['Column_name']}\" length=\"{$column['Sub_part']}\" collation=\"{$column['Collation']}\" null=\"{$null}\" />";
                        }
                        $xmlIndices[] = "\t\t<index alias=\"{$keyName}\" name=\"{$keyName}\" primary=\"{$primary}\" unique=\"{$unique}\" type=\"{$type}\" >";
                        $xmlIndices[] = implode("\n", $xmlIndexCols);
                        $xmlIndices[] = "\t\t</index>";
                    }
                } else {
                    $this->manager->xpdo->log(xPDO::LOG_LEVEL_WARN, 'No indexes were found in table ' . $table[0]);
                }
            } else {
                $this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'Error getting indexes for table ' . $table[0]);
            }
            $xmlObject[] = "\t<object class=\"{$class}\" table=\"{$tableName}\" extends=\"{$extends}\">";
            $xmlObject[] = implode("\n", $xmlFields);
            if (!empty($xmlIndices)) {
                $xmlObject[] = '';
                $xmlObject[] = implode("\n", $xmlIndices);
            }
            $xmlObject[] = "\t</object>";
            $xmlContent[] = implode("\n", $xmlObject);
        }
        $xmlContent[] = "</model>";
        if ($this->manager->xpdo->getDebug() === true) {
            $this->manager->xpdo->log(xPDO::LOG_LEVEL_DEBUG, implode("\n", $xmlContent));
        }
        $file = fopen($schemaFile, 'wb');
        $written = fwrite($file, implode("\n", $xmlContent));
        fclose($file);
        return true;
    }

    public function parseSchema($schemaFile, $outputDir = '', $compile = false)
    {
        $this->schemaFile = $schemaFile;
        $this->classTemplate = $this->getClassTemplate();
        if (!is_file($schemaFile)) {
            $this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Could not find specified XML schema file {$schemaFile}");
            return false;
        }

        $this->schema = new SimpleXMLElement($schemaFile, 0, true);
        if (isset($this->schema)) {
            foreach ($this->schema->attributes() as $attributeKey => $attribute) {
                /** @var SimpleXMLElement $attribute */
                $this->model[$attributeKey] = (string)$attribute;
            }
            if (isset($this->schema->object)) {
                foreach ($this->schema->object as $object) {
                    /** @var SimpleXMLElement $object */
                    $class = (string)$object['class'];
                    $extends = isset($object['extends']) ? (string)$object['extends'] : $this->model['baseClass'];
                    $this->classes[$class] = array('extends' => $extends);
                    $this->map[$class] = array(
                        'package' => $this->model['package'],
                        'version' => $this->model['version']
                    );
                    foreach ($object->attributes() as $objAttrKey => $objAttr) {
                        if ($objAttrKey == 'class') continue;
                        $this->map[$class][$objAttrKey] = (string)$objAttr;
                    }

                    $engine = (string)$object['engine'];
                    if (!empty($engine)) {
                        $this->map[$class]['tableMeta'] = array('engine' => $engine);
                    }

                    $this->map[$class]['fields'] = array();
                    $this->map[$class]['fieldMeta'] = array();
                    if (isset($object->field)) {
                        foreach ($object->field as $field) {
                            $key = (string)$field['key'];
                            $dbtype = (string)$field['dbtype'];
                            $defaultType = $this->manager->xpdo->driver->getPhpType($dbtype);
                            $this->map[$class]['fields'][$key] = null;
                            $this->map[$class]['fieldMeta'][$key] = array();
                            foreach ($field->attributes() as $fldAttrKey => $fldAttr) {
                                $fldAttrValue = (string)$fldAttr;
                                switch ($fldAttrKey) {
                                    case 'key':
                                        continue 2;
                                    case 'default':
                                        if ($fldAttrValue === 'NULL') {
                                            $fldAttrValue = null;
                                        }
                                        switch ($defaultType) {
                                            case 'integer':
                                            case 'boolean':
                                            case 'bit':
                                                $fldAttrValue = (integer)$fldAttrValue;
                                                break;
                                            case 'float':
                                            case 'numeric':
                                                $fldAttrValue = (float)$fldAttrValue;
                                                break;
                                            default:
                                                break;
                                        }
                                        $this->map[$class]['fields'][$key] = $fldAttrValue;
                                        break;
                                    case 'null':
                                        $fldAttrValue = (!empty($fldAttrValue) && strtolower($fldAttrValue) !== 'false') ? true : false;
                                        break;
                                    default:
                                        break;
                                }
                                $this->map[$class]['fieldMeta'][$key][$fldAttrKey] = $fldAttrValue;
                            }
                        }
                    }
                    if (isset($object->alias)) {
                        $this->map[$class]['fieldAliases'] = array();
                        foreach ($object->alias as $alias) {
                            $aliasKey = (string)$alias['key'];
                            $aliasNode = array();
                            foreach ($alias->attributes() as $attrName => $attr) {
                                $attrValue = (string)$attr;
                                switch ($attrName) {
                                    case 'key':
                                        continue 2;
                                    case 'field':
                                        $aliasNode = $attrValue;
                                        break;
                                    default:
                                        break;
                                }
                            }
                            if (!empty($aliasKey) && !empty($aliasNode)) {
                                $this->map[$class]['fieldAliases'][$aliasKey] = $aliasNode;
                            }
                        }
                    }
                    if (isset($object->index)) {
                        $this->map[$class]['indexes'] = array();
                        foreach ($object->index as $index) {
                            $indexNode = array();
                            $indexName = (string)$index['name'];
                            foreach ($index->attributes() as $attrName => $attr) {
                                $attrValue = (string)$attr;
                                switch ($attrName) {
                                    case 'name':
                                        continue 2;
                                    case 'primary':
                                    case 'unique':
                                    case 'fulltext':
                                        $attrValue = (empty($attrValue) || $attrValue === 'false' ? false : true);
                                    default:
                                        $indexNode[$attrName] = $attrValue;
                                        break;
                                }
                            }
                            if (!empty($indexNode) && isset($index->column)) {
                                $indexNode['columns'] = array();
                                foreach ($index->column as $column) {
                                    $columnKey = (string)$column['key'];
                                    $indexNode['columns'][$columnKey] = array();
                                    foreach ($column->attributes() as $attrName => $attr) {
                                        $attrValue = (string)$attr;
                                        switch ($attrName) {
                                            case 'key':
                                                continue 2;
                                            case 'null':
                                                $attrValue = (empty($attrValue) || $attrValue === 'false' ? false : true);
                                            default:
                                                $indexNode['columns'][$columnKey][$attrName] = $attrValue;
                                                break;
                                        }
                                    }
                                }
                                if (!empty($indexNode['columns'])) {
                                    $this->map[$class]['indexes'][$indexName] = $indexNode;
                                }
                            }
                        }
                    }
                    if (isset($object->composite)) {
                        $this->map[$class]['composites'] = array();
                        foreach ($object->composite as $composite) {
                            $compositeNode = array();
                            $compositeAlias = (string)$composite['alias'];
                            foreach ($composite->attributes() as $attrName => $attr) {
                                $attrValue = (string)$attr;
                                switch ($attrName) {
                                    case 'alias' :
                                        continue 2;
                                    case 'criteria' :
                                        $attrValue = $this->manager->xpdo->fromJSON(urldecode($attrValue));
                                    default :
                                        $compositeNode[$attrName] = $attrValue;
                                        break;
                                }
                            }
                            if (!empty($compositeNode)) {
                                if (isset($composite->criteria)) {
                                    /** @var SimpleXMLElement $criteria */
                                    foreach ($composite->criteria as $criteria) {
                                        $criteriaTarget = (string)$criteria['target'];
                                        $expression = (string)$criteria;
                                        if (!empty($expression)) {
                                            $expression = $this->manager->xpdo->fromJSON($expression);
                                            if (!empty($expression)) {
                                                if (!isset($compositeNode['criteria'])) $compositeNode['criteria'] = array();
                                                if (!isset($compositeNode['criteria'][$criteriaTarget])) $compositeNode['criteria'][$criteriaTarget] = array();
                                                $compositeNode['criteria'][$criteriaTarget] = array_merge($compositeNode['criteria'][$criteriaTarget], (array)$expression);
                                            }
                                        }
                                    }
                                }
                                $this->map[$class]['composites'][$compositeAlias] = $compositeNode;
                            }
                        }
                    }
                    if (isset($object->aggregate)) {
                        $this->map[$class]['aggregates'] = array();
                        foreach ($object->aggregate as $aggregate) {
                            $aggregateNode = array();
                            $aggregateAlias = (string)$aggregate['alias'];
                            foreach ($aggregate->attributes() as $attrName => $attr) {
                                $attrValue = (string)$attr;
                                switch ($attrName) {
                                    case 'alias' :
                                        continue 2;
                                    case 'criteria' :
                                        $attrValue = $this->manager->xpdo->fromJSON(urldecode($attrValue));
                                    default :
                                        $aggregateNode[$attrName] = $attrValue;
                                        break;
                                }
                            }
                            if (!empty($aggregateNode)) {
                                if (isset($aggregate->criteria)) {
                                    /** @var SimpleXMLElement $criteria */
                                    foreach ($aggregate->criteria as $criteria) {
                                        $criteriaTarget = (string)$criteria['target'];
                                        $expression = (string)$criteria;
                                        if (!empty($expression)) {
                                            $expression = $this->manager->xpdo->fromJSON($expression);
                                            if (!empty($expression)) {
                                                if (!isset($aggregateNode['criteria'])) $aggregateNode['criteria'] = array();
                                                if (!isset($aggregateNode['criteria'][$criteriaTarget])) $aggregateNode['criteria'][$criteriaTarget] = array();
                                                $aggregateNode['criteria'][$criteriaTarget] = array_merge($aggregateNode['criteria'][$criteriaTarget], (array)$expression);
                                            }
                                        }
                                    }
                                }
                                $this->map[$class]['aggregates'][$aggregateAlias] = $aggregateNode;
                            }
                        }
                    }
                    if (isset($object->validation)) {
                        $this->map[$class]['validation'] = array();
                        $validation = $object->validation[0];
                        $validationNode = array();
                        foreach ($validation->attributes() as $attrName => $attr) {
                            $validationNode[$attrName] = (string)$attr;
                        }
                        if (isset($validation->rule)) {
                            $validationNode['rules'] = array();
                            foreach ($validation->rule as $rule) {
                                $ruleNode = array();
                                $field = (string)$rule['field'];
                                $name = (string)$rule['name'];
                                foreach ($rule->attributes() as $attrName => $attr) {
                                    $attrValue = (string)$attr;
                                    switch ($attrName) {
                                        case 'field' :
                                        case 'name' :
                                            continue 2;
                                        default :
                                            $ruleNode[$attrName] = $attrValue;
                                            break;
                                    }
                                }
                                if (!empty($field) && !empty($name) && !empty($ruleNode)) {
                                    $validationNode['rules'][$field][$name] = $ruleNode;
                                }
                            }
                            if (!empty($validationNode['rules'])) {
                                $this->map[$class]['validation'] = $validationNode;
                            }
                        }
                    }
                }
            } else {
                //  $this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Schema {$schemaFile} contains no valid object elements.");
            }
        } else {
            $this->manager->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Could not read schema from {$schemaFile}.");
        }

        $om_path = XPDO_CORE_PATH . 'om/';
        $path = !empty ($outputDir) ? $outputDir : $om_path;
        if (isset ($this->model['package']) && strlen($this->model['package']) > 0) {
            $path .= strtr($this->model['package'], '.', '/');
            $path .= '/';
        }
        $this->outputMeta($path);
        $this->outputClasses($path);
        $this->outputMaps($path);
        if ($compile) $this->compile($path, $this->model, $this->classes, $this->maps);
        unset($this->model, $this->classes, $this->map);
        return true;
    }

}