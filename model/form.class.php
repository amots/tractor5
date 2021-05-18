<?php

/**
 * Description of form
 * @created on : Jul 1, 2018, 11:09:40 AM
 * @author amots
 */
class form
{

    private $pdo;
    private $dbName;
    private $table;
    private $dataStruct;
    private $keys;
    public $last_id;

    public function __construct($table)
    {
        $this->pdo = db::getInstance();
        $this->table = $table;
        $this->dataStruct = $this->getFields();
        $this->keys = $this->getKeys();
        $this->dbName = $this->pdo->dbname;
    }

    private function getKeys()
    {
        $sql = "select * from information_schema.KEY_COLUMN_USAGE where KEY_COLUMN_USAGE.CONSTRAINT_SCHEMA = :database and KEY_COLUMN_USAGE.TABLE_NAME = :table and  not REFERENCED_TABLE_SCHEMA  is NULL";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(['table' => $this->table, 'database' => $this->pdo->dbname]);
        } catch (Exception $exc) {
            Debug::dump($exc->getTraceAsString(), 'trace form::getFields');
            Debug::dump($stmt->errorInfo(), 'sqlinfo form::getFields');
        }
        $results = $stmt->fetchAll();
        return $results;
    }

    private function getFields()
    {

        $sql = "SELECT * FROM information_schema.columns WHERE `table_schema` = :database and `table_name` =:table";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(['table' => $this->table, 'database' => $this->pdo->dbname]);
        } catch (Exception $exc) {
            Debug::dump($exc->getTraceAsString(), 'trace form::getFields');
            Debug::dump($stmt->errorInfo(), 'sqlinfo form::getFields');
        }
        $results = $stmt->fetchAll();
        return $results;
    }

    public function storePostedData()
    {
        $this->storeData($_POST);
    }


    public function storeData($data2handle)
    {
        $errors = [];
        $data = $index = [];
        foreach ($this->dataStruct as $colDef) {
            $fieldname = $colDef['COLUMN_NAME'];
            $isPosted = isset($data2handle[$fieldname]);
            if (strtoupper($colDef['COLUMN_KEY']) === 'PRI') {
                if ($isPosted) {
                    if (!util::IsNullOrEmptyString($data2handle[$fieldname]))
                        $index = [$fieldname => $data2handle[$fieldname]];
                }
            }
            if (!$this->updatable($colDef)) continue;
            if ($isPosted) {
                $posted = $data2handle[$fieldname];
                if (!$this->validateField($colDef, $posted)) {
                    $errors[] = "failed to validate field " . $fieldname . " with: " . $posted;
                } else {
                    if (
                        util::IsNullOrEmptyString($posted) and strtoupper($colDef['IS_NULLABLE']) ==
                        'YES'
                    ) {
                        $data[$fieldname] = NULL;
                    } else {
                        $data[$fieldname] = $posted;
                    }
                }
            } else {
                if ($colDef['DATA_TYPE'] == 'enum') {
                    $data[$fieldname] = NULL;
                }
            }
        }
        if (util::is_array_empty($errors)) {
            $sqlStr = $this->buildSqlClause($index, $data);
            $data = array_merge($data, $index);
            $stmt = $this->pdo->prepare($sqlStr);
            $this->bind($stmt, $data);
            try {
                $stmt->execute($data);
            } catch (Exception $exc) {
                $errors[] = "failed to store data";
                $errors[] = $exc->getMessage();
            }

            $temp_last_id = (util::is_array_empty($index)) ? $this->pdo->lastInsertId()
                : $index;
            if (is_array($temp_last_id)) {
                reset($temp_last_id);
                $temp_key = key($temp_last_id);
                $this->last_id = $temp_last_id[$temp_key];
            } else {
                $this->last_id = $temp_last_id;
            }
        }
        return $errors;
    }

    private function bind($stmt, $data)
    {
        $coldefs = [];
        foreach ($this->dataStruct as $key => $value) {
            $coldefs[$value['COLUMN_NAME']] = $value;
        }
        foreach ($data as $key => $value) {
            switch (strtoupper($coldefs[$key]['DATA_TYPE'])) {
                case 'INT':
                    $stmt->bindValue(':' . $key, strval($value), PDO::PARAM_INT);
                    break;
                case 'TINYINT':
                    $stmt->bindValue(':' . $key, strval($value), PDO::PARAM_BOOL);
                    break;
                default:
                    $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
            }
        }
    }

    public function getDataStructure()
    {
        return $this->dataStruct;
    }

    public function getKeyStructure()
    {
        return $this->keys;
    }

    private function buildSqlClause($index, $data)
    {
        $new = (sizeof($index) == 0);
        $sqlArray = [];
        $sqlArray[] = $new ? "INSERT INTO" : "UPDATE";
        $sqlArray[] = $this->table;
        $sqlArray[] = "SET";
        $list = [];
        foreach ($data as $key => $val) {
            $list[] = sprintf("%s=:%s", $key, $key);
        }
        $sqlArray[] = implode(',', $list);
        $sqlArray[] = $new ? NULL : sprintf(
            "WHERE %s=:%s",
            array_keys($index)[0],
            array_keys($index)[0]
        );
        $sqlStr = implode(' ', $sqlArray);
        return $sqlStr;
    }

    private function validateField($colDef, $posted)
    {
        if ($colDef['IS_NULLABLE'] == 'NO') {
            if (util::IsNullOrEmptyString($posted)) {
                return FALSE;
            }
        }
        return TRUE;
    }

    private function updatable($col)
    {
        if (!isset($col['EXTRA'])) {
            debug_print_backtrace();
        }
        if (in_array(
            strtoupper($col['EXTRA']),
            ['AUTO_INCREMENT', 'ON UPDATE CURRENT_TIMESTAMP']
        )) {
            return FALSE;
        }
        return TRUE;
    }

    public function genEmptyRecord()
    {
        $record = [];
        foreach ($this->dataStruct as $key => $item) {
            //            Debug::dump($item,'struct item in ' . __METHOD__ . ' line ' . __LINE__);
            if (util::IsNullOrEmptyString($item['EXTRA'])) {
                $record[$item['COLUMN_NAME']] = $item['COLUMN_DEFAULT'];
            }
        }
        return $record;
    }

    public function get_enum_values($field)
    {
        $sql = "SELECT SUBSTRING(COLUMN_TYPE, 5) as val
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '{$this->dbName}'
        AND TABLE_NAME = '{$this->table}'
        AND COLUMN_NAME = '{$field}'";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $ex) {
            Debug::dump(
                $stmt->errorInfo(),
                'error in ' . __METHOD__ . ' line ' . __LINE__
            );
        }
        $result = $stmt->fetch();
        preg_match('/\((.*)\)/', $result['val'], $matches);
        $vals = explode(',', $matches[1]);
        foreach ($vals as $key => $value) {
            $value = trim($value, "'");
            $trimmedvals[] = $value;
        }
        return $trimmedvals;
    }
}
