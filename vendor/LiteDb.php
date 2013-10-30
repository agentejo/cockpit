<?php


class LiteDb extends PDO {

    public $log = array();
    
    public static function connect($dns="sqlite::memory:", $options=array()) {

        $litedb = new static($dns, null, null, $options);

        return $litedb;
    }

    public function vacuum() {
        $this->query('VACUUM');
    }

    public function createTable($table, $columns=array()) {
        $sql = "CREATE TABLE {$table} ( id INTEGER PRIMARY KEY AUTOINCREMENT )";
        $this->exec($sql);

        foreach ($columns as $column => $type) {
            $this->addColumn($table, $column, $type);
        }
    }

    public function renameTable($table, $name) {
        $sql = "ALTER TABLE {$table} RENAME TO {$name}";
        $this->exec($sql);
    }

    public function dropTable($table) {
        $sql = "DROP TABLE `{$table}`";
        $this->exec($sql);
    }

    public function addColumn($table, $column, $type = '' ) {
        $sql = "ALTER TABLE {$table} ADD {$column} {$type}";
        $this->exec($sql);
    }

    public function dropColumn($table, $column) {
        $sql = "ALTER TABLE {$table} DROP COLUMN {$column}";
        $this->exec($sql);
    }

    public function getTables() {
        
        $tables = $this->fetchAll("SELECT name FROM sqlite_master WHERE type='table' AND name!='sqlite_sequence';");
        $names  = array();

        foreach($tables as $table) {
            $names[] = $table["name"];
        }

        return $names;
    }

    public function tableExists($table) {
        return in_array($table, $this->getTables());
    }

    public function getColumns($table) {

        $columnsRaw = $this->fetchAll("PRAGMA table_info('{$table}')");
        $columns = array();
        foreach($columnsRaw as $r) {
            $columns[$r['name']]=$r['type'];
        }
        return $columns;
    }

    public function fetch($sql) {

        $this->log[] = $sql;

        $data = array();

        if($stmt = $this->query($sql)){
            
            $res = $stmt->fetch(self::FETCH_ASSOC);

            $data = $res ? $res : array();
        }

        return new ArrayObject($data);
    }

    public function fetchAll($sql) {

        $this->log[] = $sql;

        $data = array();

        if($stmt = $this->query($sql)){
            
            $data = $stmt->fetchAll(self::FETCH_ASSOC);
        }

        $arObj = new ArrayObject($data);

        return $arObj;
    }

    public function __get($table) {
        $query = new LiteDbQuery($this, $table);

        return $query;
    }
}

class LiteDbQuery {
    
    protected $cmd;
    protected $connection;

    protected $fields;
    protected $table;
    protected $joins;
    protected $conditions = array();
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

    public function where() {
        
        $args = func_get_args();

        switch(func_num_args()) {
            
            case 0:
                break;
            case 1:
                $this->conditions[] = $args[0];
                break;                
            case 2:

                if(is_array($args[1])) {
                    $this->conditions[] = array($args[0], $args[1]);

                    break;
                }
            default:

                $params = array($args[0]);

                for($i=1;$i<count($args);$i++) {
                    $params[] = $this->connection->quote($args[$i]);
                }

                $this->conditions[] = call_user_func_array("sprintf", $params);
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

    public function all() {

        if(func_num_args()) {
            call_user_func_array(array($this, 'where'), func_get_args());
        }

        return $this->connection->fetchAll($this->buildSelect());
    }

    public function one() {

        $this->limit = 1;

        if(func_num_args()) {
            call_user_func_array(array($this, 'where'), func_get_args());
        }

        return $this->connection->fetch($this->buildSelect());
    }

    public function count($conditions = null) {
        
        $table = $this->table;
        $obj   = $this->connection->{$table}->select('COUNT(*) AS C');

        if($conditions) {
            $obj->where($conditions);
        }

        $count = $obj->one();

        return isset($count['C']) ? $count['C']:0;
    }

    public function insert($data){    
      
        $table = $this->table;

        $fields = array();
        $values = array();

        foreach($data as $col=>$value){
            
            if(!is_null($value) && (is_array($value) || is_object($value))){
              $value = json_encode($value, JSON_NUMERIC_CHECK);
            }

            $fields[] = "`{$col}`";
            $values[] = (is_null($value) ? 'NULL':$this->connection->quote($value));
        }
        
        $fields = implode(',', $fields);
        $values = implode(',', $values);

        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$values})";

        $this->connection->log[] = $sql;

        $res = $this->connection->exec($sql);

        if($res){
            return $this->connection->lastInsertId();
        }else{
            trigger_error('SQL Error: '.implode(', ',$this->connection->errorInfo()).":\n".$sql);
            return false;
        }
    }

    public function update($data, $conditions=array()){    

        $table = $this->table;

        $conditions = $this->buildConditions($conditions);

        if(strlen(trim($conditions))>0) $conditions = "WHERE ".$conditions;

        $fields = array();

        foreach($data as $col=>$value){

            if(!is_null($value) && (is_array($value) || is_object($value))){
                $value = json_encode($value, JSON_NUMERIC_CHECK);
            }

            $fields[] = "`{$col}`=".(is_null($value) ? 'NULL':$this->connection->quote($value));
        }

        $fields = implode(',', $fields);

        $sql = "UPDATE ".$table." SET {$fields} {$conditions}";

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

    public function delete(){

        $table = $this->table;

        $args       = func_get_args();
        $conditions = array();

        switch(func_num_args()) {
            
            case 0:
                return false;
                break;
            case 1:
                $conditions[] = $args[0];
                break;                
            case 2:

                if(is_array($args[1])) {
                    $conditions[] = array($args[0], $args[1]);
                    break;
                }
            default:

                $params = array($args[0]);

                for($i=1;$i<count($args);$i++) {
                    $params[] = $this->connection->quote($args[$i]);
                }

                $conditions[] = call_user_func_array("sprintf", $params);
        }

        $conditions = $this->buildConditions($conditions);

        if(strlen(trim($conditions))>0) $conditions = "WHERE ".$conditions;

        $sql = "DELETE FROM {$table} {$conditions}";

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
        $this->connection->exec("DELETE FROM ".$this->table);
    }

    public function drop() {
        $this->connection->exec("DROP TABLE ".$this->table);
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

        if(is_array($fields)) $fields = implode(', ', $fields);
        if(is_array($table))  $table  = implode(', ', $table);
        if(is_array($group))  $group  = implode(', ', $group);
        if(is_array($order))  $order  = implode(', ', $order);

        $conditions = $this->buildConditions($conditions);
        $having     = $this->buildConditions($having);

       
        if ($limit) {
            
            $rt = '';
            
            if (!strpos(strtolower($limit), 'limit') || strpos(strtolower($limit), 'limit') === 0) {
                $rt = ' LIMIT';
            }
            
            $rt .= ' ' . $limit;
            
            if ($offset) { $rt .= ' OFFSET ' . $offset; }

            $limit = $rt;
        }

        if(strlen(trim($conditions))>0) $conditions = "WHERE ".$conditions;
        if(strlen(trim($group))>0) $group = "GROUP BY ".$group;
        if(strlen(trim($having))>0) $having = "HAVING ".$conditions;
        if(strlen(trim($fields))==0) $fields = "*";
        if(strlen(trim($order))>0) $order = "ORDER BY ".$order;

        $sql = trim("SELECT {$fields} FROM {$table} {$joins} {$conditions} {$group} {$having} {$order} {$limit}");

        return $sql;

    }

    protected function buildConditions($conditions){
        
        if(is_string($conditions)) $conditions = array($conditions);
        
        $_conditions = array();

        if(count($conditions)){
          
          $_bindParams = array();

          foreach($conditions as $c){
            
            $sql = '';
            
            if(is_array($c)){
              
              $sql = $c[0];
              
              foreach($c[1] as $key=>$value){
                $sql = str_replace(':'.$key,$this->connection->quote($value), $sql);
              }
            }else{
              $sql= $c;
            }

            if(count($_conditions) > 0  && strtoupper(substr($sql,0,4))!='AND ' && strtoupper(substr($sql,0,3))!='OR '){
              $sql = 'AND '.$sql;
            }
            
            $_conditions[] = $sql;
            
          }
          
        }
        
       $conditions = implode(' ', $_conditions);
       
       return $conditions;
    }
}