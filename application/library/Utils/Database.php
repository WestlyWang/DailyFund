<?php
	define("CONFIG_PATH",dirname(__FILE__)."/../../");
	class Utils_Database{
		private $configs;
		private static $instance = null;
		private $dbh = null;
		/**
		  *引入配置文件
		  */
		private function __construct(){
			$this->configs = require(CONFIG_PATH.'config/db.php');
			$this->dbh = $this->connect();
		}
		/**
		  *建立数据库实例对象
		  */
		public static function getInstance(){
			if($instance==null){
				$instance = new Utils_Database();
			}
			return $instance;
		}
		/**
		  *建立数据库链接
		  *@return 数据库实例
		  */
		private function connect(){
			try{
				$dbh = new PDO($this->configs['pdo']['db.dsn'],$this->configs['pdo']['db.username'],$this->configs['pdo']['db.password']);
				$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
				$dbh->setAttribute(PDO::ATTR_PERSISTENT,true);
			}catch(PDOException $e){
				print "Error:".$e->getMessage()."<br/>";
				$dbh = null;
			}
			return $dbh;
		}
		/**
		  *向表tablename中插入数据
		  *数据组织形式:array(列名=>列值)
		  *@return bool
		  */
		public function add($tablename,$params){
			$sql = "insert into $tablename (";
			foreach($params as $key=>$value){
				$sql = $sql.$key.",";
			}
			$sql = rtrim($sql,',').") values (";
			foreach($params as $key=>$value){
				$sql = $sql.":".$key.",";
			}
			$sql = rtrim($sql,',').")";
			try{
				$this->dbh->beginTransaction();
				$stmt = $this->dbh->prepare($sql);
				foreach($params as $key=>$value){
					$stmt->bindValue(":$key",$value,is_int($value)||is_bool($value)?PDO::PARAM_INT:PDO::PARAM_STR);
				}
				$flag = $stmt->execute();
				if(!$flag){
					throw new PDOException("添加数据失败!");
				}
				$this->dbh->commit();
			}catch(PDOException $e){
				print "Execute $sql Occur Error:".$e->getMessage()."<br/>";
				$this->dbh->rollback();
				$this->dbh = null;
				return false;
			}
			return true;
		}
		/**
		  *删除表$tablename中的数据
		  *where array($key=>$value)
		  */
		public function del($tablename,$params){
			$sql = "delete from $tablename";
			if(!empty($params)){
				$sql = $sql." where";
				foreach($params as $key=>$value){
					$sql = $sql." $key=:$key and";
				}
				$sql = substr($sql,0,-4);
			}
			try{
				$this->dbh->beginTransaction();
				$stmt = $this->dbh->prepare($sql);
				foreach($params as $key=>$value){
					$stmt->bindValue(":$key",$value,is_int($value)||is_bool($value)?PDO::PARAM_INT:PDO::PARAM_STR);
				}
				$flag = $stmt->execute();
				if(!$flag){
					throw new PDOException("删除数据失败!");
				}
				$this->dbh->commit();
			}catch(PDOException $e){
				print "Execute $sql Occur Error:".$e->getMessage()."<br/>";
				$this->dbh->rollback();
				$this->dbh = null;
				return false;
			}
			return true;
		}
		/**
		  *更新数据
		  *@params 表名 更新内容array 条件array
		  *@return bool
		  */
		public function modify($tablename,$params,$conditions){
			if(empty($params)){
				return true;
			}
			$sql = "update $tablename set";
			foreach($params as $key=>$value){
				$sql = $sql." $key=:$key,";
			}
			$sql = rtrim($sql,',');
			if(!empty($conditions)){
				$sql = $sql." where";
				foreach($conditions as $key=>$value){
					$sql = $sql." $key=:con_$key and";
				}
				$sql = substr($sql,0,-4);
			}
			try{
				$this->dbh->beginTransaction();
				$stmt = $this->dbh->prepare($sql);
				foreach($params as $key=>$value){
					$stmt->bindValue(":$key",$value,is_int($value)||is_bool($value)?PDO::PARAM_INT:PDO::PARAM_STR);
				}
				foreach($conditions as $key=>$value){
					$stmt->bindValue(":con_$key",$value,is_int($value)||is_bool($value)?PDO::PARAM_INT:PDO::PARAM_STR);
				}
				$flag = $stmt->execute();
				if(!$flag){
					throw new PDOException("更新数据失败!");
				}
				$this->dbh->commit();
			}catch(PDOException $e){
				print "Execute $sql Occur Error:".$e->getMessage()."<br/>";
				$this->dbh->rollback();
				$this->dbh = null;
				return false;
			}
			return true;
		}
		/**
		  *查询表结果集
		  */
		public function query($tablename,$params){
			$sql = "select * from $tablename";
			if(!empty($params)){
				$sql = $sql." where";
				foreach($params as $key=>$value){
					$sql = $sql." $key=:$key and";
				}
				$sql = substr($sql,0,-4);
			}
			try{
				$this->dbh->beginTransaction();
				$stmt = $this->dbh->prepare($sql);
				foreach($params as $key=>$value){
					$stmt->bindValue(":$key",$value,is_int($value)||is_bool($value)?PDO::PARAM_INT:PDO::PARAM_STR);
				}
				$flag = $stmt->execute();
				if(!$flag){
					throw new PDOException("查找数据失败!");
				}
				$stmt->setFetchMode(PDO::FETCH_ASSOC);
				$this->dbh->commit();
				$results = array();
				while($res = $stmt->fetch(PDO::FETCH_ASSOC)){
					$results[] = $res;
				}
				return $results;
			}catch(PDOException $e){
				print "Execute $sql Occur Error:".$e->getMessage()."<br/>";
				$this->dbh->rollback();
				$this->dbh = null;
				return null;
			}
			return null;
		}
		/**
		  *分页结果集
		  */
		public function page($tablename,$params,$fp=0,$limit=20){
			$sql = "select * from $tablename";
			$fp = $fp * $limit;
			if(!empty($params)){
				$sql = $sql." where";
				foreach($params as $key=>$value){
					$sql = $sql." $key=:$key and";
				}
				$sql = substr($sql,0,-4);
			}
			$sql = $sql." limit $fp,$limit";
			try{
				$this->dbh->beginTransaction();
				$stmt = $this->dbh->prepare($sql);
				foreach($params as $key=>$value){
					$stmt->bindValue(":$key",$value,is_int($value)||is_bool($value)?PDO::PARAM_INT:PDO::PARAM_STR);
				}
				$flag = $stmt->execute();
				if(!$flag){
					throw new PDOException("查找数据失败!");
				}
				$stmt->setFetchMode(PDO::FETCH_ASSOC);
				$this->dbh->commit();
				$results = array();
				while($res = $stmt->fetch(PDO::FETCH_ASSOC)){
					$results[] = $res;
				}
				return $results;
			}catch(PDOException $e){
				print "Execute $sql Occur Error:".$e->getMessage()."<br/>";
				$this->dbh->rollback();
				$this->dbh = null;
				return null;
			}
			return null;
		}
		/**
		  *统计查询结果数
		  */
		public function total($tablename,$params){
			$sql = "select count(*) as total from $tablename";
			if(!empty($params)){
				$sql = $sql." where";
				foreach($params as $key=>$value){
					$sql = $sql." $key=:$key and";
				}
				$sql = substr($sql,0,-4);
			}
			try{
				$this->dbh->beginTransaction();
				$stmt = $this->dbh->prepare($sql);
				foreach($params as $key=>$value){
					$stmt->bindValue(":$key",$value,is_int($value)||is_bool($value)?PDO::PARAM_INT:PDO::PARAM_STR);
				}
				$flag = $stmt->execute();
				$stmt->setFetchMode(PDO::FETCH_ASSOC);
				$this->dbh->commit();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$result = $result[total];
				return $result;
			}catch(PDOException $e){
				print "Execute $sql Occur Error:".$e->getMessage()."<br/>";
				$this->dbh->rollback();
				$this->dbh = null;
				return 0;
			}
			return 0;
		}
		/**
		  *取结果集第一条数据
		  */
		public function first($tablename,$params){
			$sql = "select * from $tablename";
			if(!empty($params)){
				$sql = $sql." where";
				foreach($params as $key=>$value){
					$sql = $sql." $key=:$key and";
				}
				$sql = substr($sql,0,-4);
			}
			$sql = $sql." order by id desc limit 0,1";
			try{
				$this->dbh->beginTransaction();
				$stmt = $this->dbh->prepare($sql);
				foreach($params as $key=>$value){
					$stmt->bindValue(":$key",$value,is_int($value)||is_bool($value)?PDO::PARAM_INT:PDO::PARAM_STR);
				}
				$flag = $stmt->execute();
				$stmt->setFetchMode(PDO::FETCH_ASSOC);
				$this->dbh->commit();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				return $result;
			}catch(PDOException $e){
				print "Execute $sql Occur Error:".$e->getMessage()."<br/>";
				$this->dbh->rollback();
				$this->dbh = null;
				return null;
			}
			return null;
		}
		/**
		  *根据sql创建数据表
		  */
		public function createTable($tablename,$params){
			$sql = "create table if not exists $tablename(";
			foreach($params as $colname => $type){
				if($colname == "id"){
					$sql = $sql."$colname $type not null primary key auto_increment,";
				}else{
					$sql = $sql."$colname $type not null,";
				}
			}
			$sql = rtrim($sql,",");
			$sql = $sql.") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			try{
				$this->dbh->beginTransaction();
				$this->dbh->exec($sql);
				$this->dbh->commit();
			}catch(PDOException $e){
				print "Execute $sql Occur Error:".$e->getMessage()."<br/>";
				$this->dbh->rollback();
				$this->dbh = null;
				return 0;
			}
		}
		/**
		  *执行sql语句
		  */
		public function executeQuery($sql){
			try{
				$result = array();
				$this->dbh->beginTransaction();
				$stmt = $this->dbh->prepare($sql);
				$stmt->execute();
				$stmt->setFetchMode(PDO::FETCH_ASSOC);
				$this->dbh->commit();
				while($res = $stmt->fetch(PDO::FETCH_ASSOC)){
					$result[] = $res;
				}
				return $result;
			}catch(PDOException $e){
				print "Execute $sql Occur Error:".$e->getMessage()."<br/>";
				$this->dbh->rollback();
				$this->dbh = null;
			}
			return null;
		}
		/**
		  *执行sql语句
		  */
		public function executeUpdate($sql){
			try{
				$this->dbh->beginTransaction();
				$this->dbh->exec($sql);
				$this->dbh->commit();
				return true;
			}catch(PDOException $e){
				print "Execute $sql Occur Error:".$e->getMessage()."<br/>";
				$this->dbh->rollback();
				$this->dbh = null;
			}
			return false;
		}

		/**
		  *释放资源
		  */
		private function __destruct(){
			$this->dbh = null;
			$this->configs = array();
			self::$instance = null;
		}
	}
?>
