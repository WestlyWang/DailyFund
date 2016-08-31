<?php
	
	/**
	  *处理新用户注册Action
	  *@file RegisterAction.php
	  *@author wangshuai
	  *@time 2016-08-16
	  */
	class RegisterAction extends Yaf_Action_Abstract{
		public function execute(){
			$params = $_POST;
			$tablename = "users";
			if(!$this->validate($tablename,$params,$errormsg)){
				$this->getView()->display("error.phtml",array("error"=>$errormsg));
				return;
			}
			$params['password'] = md5($params['password1']);
			unset($params["password1"]);
			unset($params["password2"]);
			$username = $params['username'];
			/**用户插入*/
			$this->register($tablename,$params);
			$flag = $this->create($username);
			if($flag){
				$this->getView()->display("login.phtml");
			}else{
				$this->getView()->display("error.phtml",array("error"=>"注册用户失败!"));
			}
		}
		/**
		  *注册创建表
		  *使用用户名创建md5(username)_XXX表
		  *创建datastore下的username目录
		  */
		private function create($username){
			if(empty($username)){
				$this->getView()->display("error.phtml",array("error"=>"非法用户名!"));
				return;
			}
			$params = array(
					"id" => "int(11)",
					"user" => "varchar(20)",
					"recordtime" => "date",
					"briefreason" => "varchar(60)",
					"source" => "varchar(16)",
					"cost" => "double",
					"type" => "int(1)",
					"singlebalance" => "double",
					"balance" => "double",
					"remarks" => "text"
					);
			$tablename = md5($username)."_detail";
			Utils_Database::getInstance()->createTable($tablename,$params);
			$params = array(
					"id" => "int(11)",
					"st_time" => "varchar(16)",
					"income" => "varchar(8)",
					"income_total" => "double",
					"income_detail" => "varchar(256)",
					"consume" => "varchar(8)",
					"consume_total" => "double",
					"consume_detail" => "varchar(256)",
					"balance" => "double"
					);
			$tablename = md5($username)."_statistics";
			Utils_Database::getInstance()->createTable($tablename,$params);
			$path = APP_PATH."/datastore/".$username;
			if(!$this->mkDirs($path)){
				return false;
			}
			exec("php ".APP_PATH."/application/script/autoInit.php $username");
			return true;
		}
		/**
		  *递归创建目录username
		  */
		private function mkDirs($path){
			if(!is_dir($path)){
				if(!$this->mkDirs(dirname($path))){
					return false;
				}
				if(!mkdir($path,0777)){
					return false;
				}
			}
			return true;
		}
		/**
		  *注册用户信息插入
		  */
		private function register($tablename,$params){
			Utils_Database::getInstance()->add($tablename,$params);
		}
		/**
		  *验证注册信息
		  */
		private function validate($tablename,$params,&$errormsg){
			$errorinfos = array(
					"username" => "用户名",
					"password1" => "密码",
					"password2" => "确认密码",
					"basic_income" => "基本月收入大于0且",
					"plan_save" => "计划存储",
					"to_family" => "孝敬父母金额",
					"rent" => "每月房租",
					);
			$username = $params['username'];
			if(empty($username)){
				$errormsg = "用户名不能为空!";
				return false;
			}
			$total = Utils_Database::getInstance()->total($tablename,array("username" => $username));
			if($total > 0){
				$errormsg = "用户名已存在!";
				return false;
			}
			foreach($params as $k=>$v){
				if(strlen($v)<=0){
					$errormsg = "$errorinfos[$k]不能为空!";
					return false;
				}
				if($k=="basic_income" || $k=="plan_save" || $k=="to_family" || $k=="rent"){
					if(!is_numeric($v)){
						$errormsg = "$errorinfos[$k]内容必须为数字!";
						return false;
					}
				}
			}
			if($params['password1']!=$params['password2']){
				$errormsg = "请确认密码一致!";
				return false;
			}
			if($params['basic_income']<=0){
				$errormsg = "月收入必须大于0";
				return false;
			}
			return true;
		}
	}
?>

