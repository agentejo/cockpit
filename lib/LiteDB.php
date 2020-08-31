<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class LiteDB extends PDO {

    public $log = [];

    public static function connect($dns="sqlite::memory:", $user = null, $password = null, $options=[]) {

        $litedb = new static($dns, $user, $password, $options);

        return $litedb;
    }

    public function fetch($sql) {

        $this->log[] = $sql;

        $data = [];

        if ($stmt = $this->query($sql)){

            $res = $stmt->fetch(self::FETCH_ASSOC);

            $data = $res ? $res : [];
        }

        return count($data) ? $data : null;
    }

    public function fetchAll($sql) {

        $this->log[] = $sql;

        $data = [];

        if ($stmt = $this->query($sql)){

            $data = $stmt->fetchAll(self::FETCH_ASSOC);
        }

        $arObj = $data;

        return $arObj;
    }

    public function __get($table) {
        $query = new LiteDBQuery($this, $table);

        return $query;
    }
}

class LiteDBQuery {

    protected $cmd;
    protected $connection;

    protected $fields;
    protected $table;
    protected $joins;
    protected $conditions = [];
    protected $group;
    protected $having;
    protected $order;
    protected $limit;
    protected $offset;

    public function __construct($connection, $table=null) {

        $this->connection = $connection;
        $this->table      = $table;
    }


    public function select($fields = '*') {
        $this->cmd    = "select";
        $this->fields = $fields;
        $this->joins  = "";

        return $this;
    }

    public function fields($fields = '*') {

        $this->fields = $fields;

        return $this;
    }

    public function from($table) {
        $this->table = $table;

        return $this;
    }

    public function where($conditions) {

        if (is_string($conditions)) {
            $this->conditions[] = $conditions;
        } else {

            foreach ($conditions as $arg) {
                $this->conditions[] = $arg;
            }
        }

        return $this;
    }

    public function join($type, $join) {
        $this->joins .= strtoupper($type)." JOIN {$join} ";
        return $this;
    }

    public function group($group) {
        $this->group = $group;
        return $this;
    }

    public function having($having) {
        $this->having = $having;
        return $this;
    }

    public function order($order) {
        $this->order = $order;
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function all($condition = null) {

        if (func_num_args() && $condition) {
            foreach (func_get_args() as $arg) {
                $this->where($arg);
            }
        }

        return $this->connection->fetchAll($this->buildSelect());
    }

    public function one($conditions = null) {

        $this->limit = 1;

        if ($conditions) {
            $this->where($conditions);
        }

        return $this->connection->fetch($this->buildSelect());
    }

    public function count($conditions = null) {

        $table = $this->table;
        $obj   = $this->connection->{$table}->select('COUNT(*) AS C');

        if ($conditions) {
            $obj->where($conditions);
        }

        $count = $obj->one();

        return isset($count['C']) ? intval($count['C']):0;
    }

    public function sum($field, $conditions = null) {

        $args  = func_get_args();
        $table = $this->table;
        $obj   = $this->connection->{$table}->select('SUM('.$field.') AS S');

        array_shift($args);

        if ($conditions) {
            $obj->where($conditions);
        }

        $count = $obj->one();

        return isset($count['S']) ? floatval($count['S']):0;
    }

    public function avg($field, $conditions = null) {

        $args  = func_get_args();
        $table = $this->table;
        $obj   = $this->connection->{$table}->select('AVG('.$field.') AS A');

        array_shift($args);

        if ($conditions) {
            $obj->where($conditions);
        }

        $avg = $obj->one();

        return isset($avg['A']) ? floatval($avg['A']):0;
    }

    public function insert($data){

        $table = $this->table;

        $fields = [];
        $values = [];

        foreach ($data as $col=>$value){

            if (!is_null($value) && (is_array($value) || is_object($value))){
              $value = json_encode($value, JSON_NUMERIC_CHECK);
            }

            $fields[] = "`{$col}`";
            $values[] = (is_null($value) ? 'NULL':$this->connection->quote($value));
        }

        $fields = implode(',', $fields);
        $values = implode(',', $values);

        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$values})";

        $this->connection->log[] = $sql;

        $res = $this->connection->exec($sql);

        if ($res){
            return $this->connection->lastInsertId();
        } else {
            trigger_error('SQL Error: '.implode(', ',$this->connection->errorInfo()).":\n".$sql);
            return false;
        }
    }

    public function update($data, $conditions=[]){

        $table = $this->table;
        $conditions = $this->buildConditions($conditions);

        if (strlen(trim($conditions))>0) $conditions = "WHERE ".$conditions;

        $fields = [];

        foreach ($data as $col=>$value){

            if (!is_null($value) && (is_array($value) || is_object($value))){
                $value = json_encode($value, JSON_NUMERIC_CHECK);
            }

            $fields[] = "`{$col}`=".(is_null($value) ? 'NULL':$this->connection->quote($value));
        }

        $fields = implode(',', $fields);

        $sql = "UPDATE `{$table}` SET {$fields} {$conditions}";

        $this->connection->log[] = $sql;

        if ($this->connection->exec($sql)) {

        } else {
            $errorInfo = $this->connection->errorInfo();
            if ($errorInfo[0]!='00000') {
                trigger_error('SQL Error: '.implode(', ',$errorInfo).":\n".$sql);
                return false;
            }
        }
    }

    public function delete($conditions = []){

        $table = $this->table;
        $conditions = $this->buildConditions($conditions);

        if (strlen(trim($conditions))>0) $conditions = "WHERE ".$conditions;

        $sql = "DELETE FROM `{$table}` {$conditions}";

        $this->connection->log[] = $sql;

        $res = $this->connection->exec($sql);

        if ($res || $res===0) {
            return true;
        } else {
            trigger_error('SQL Error: '.implode(', ',$this->connection->errorInfo()).":\n".$sql);
            return false;
        }
    }

    public function truncate() {
        $this->connection->exec("DELETE FROM `{$this->table}`");
    }

    public function drop() {
        $this->connection->exec("DROP TABLE `{$this->table}`");
    }


    public function buildSelect(){

        $fields     = $this->fields;
        $table      = $this->table;
        $joins      = $this->joins;
        $conditions = $this->conditions;
        $group      = $this->group;
        $having     = $this->having;
        $order      = $this->order;
        $limit      = $this->limit;
        $offset     = $this->offset;

        if (is_array($fields)) $fields = implode(', ', $fields);
        if (is_array($table))  $table  = implode(', ', $table);
        if (is_array($group))  $group  = implode(', ', $group);
        if (is_array($order))  $order  = implode(', ', $order);

        $conditions = $this->buildConditions($conditions);
        $having     = $this->buildConditions($having);

        if ($limit) {

            $rt = '';

            if (!strpos(strtolower($limit), 'limit') || strpos(strtolower($limit), 'limit') === 0) {
                $rt = ' LIMIT';
            }

            $rt .= ' ' . $limit;

            if ($offset) {
                $rt .= ' OFFSET ' . $offset;
            }

            $limit = $rt;
        }

        if (strlen(trim($conditions))>0) $conditions = "WHERE ".$conditions;
        if (strlen(trim($group))>0) $group = "GROUP BY ".$group;
        if (strlen(trim($having))>0) $having = "HAVING ".$conditions;
        if (strlen(trim($fields))==0) $fields = "*";
        if (strlen(trim($order))>0) {
            
            $driver = strtolower($this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME));

            if ($driver == 'mysql') {
                $order = str_replace(['RANDOM()'], ['RAND()'], $order);
            } elseif ($driver == 'sqlite') {
                $order = str_replace(['RAND()'], ['RANDOM()'], $order);
            }

            $order = "ORDER BY ".$order;
        }

        $sql = trim("SELECT {$fields} FROM `{$table}` {$joins} {$conditions} {$group} {$having} {$order} {$limit}");

        return $sql;

    }

    protected function buildConditions($conditions){

        if (is_string($conditions)) $conditions = array($conditions);

        if (!is_array($conditions) || !count($conditions)) {
            return '';
        }

        $_conditions = [];

        foreach ($conditions as $c) {

            $sql = '';

            if (is_string($c)) {
                $sql = $c;
            } elseif (is_array($c) && isset($c[0], $c[1])) {
                $sql = $c[0];

                foreach ($c[1] as $key=>$value){
                    $sql = str_replace(':'.$key, $this->connection->quote($value), $sql);
                }

            }

            if (count($_conditions) > 0  && strtoupper(substr($sql,0,4))!='AND ' && strtoupper(substr($sql,0,3))!='OR '){
                $sql = 'AND '.$sql;
            }

            $_conditions[] = $sql;
        }
        

       $conditions = implode(' ', $_conditions);

       return $conditions;
    }
}
