<?php
	
	/**
	  *处理用户注册信息修改Action
	  *@file ChangeAction.php
	  *@author wangshuai
	  *@time 2016-08-26
	  */
	class ChangeAction extends Yaf_Action_Abstract{
		public function execute(){
			$params = $_POST;
			$tablename = "users";
			$username = $params['username'];
			unset($params['username']);
			if(!$this->validate($tablename,$params,$errormsg)){
			    $this->getView()->display("error.phtml",array("error"=>$errormsg));
			    return;
			}
			if(strlen($params['password1'])>0){
			    if($params['password1'] != $params['password2']){
			        $errormsg = "请确定一个唯一的密码!";
			        $this->getView()->display("error.phtml",array("error"=>$errormsg));
			        return false;
			    }
			    $t_user = Utils_Database::getInstance()->first($tablename,array("username" => $username));
			    if(!is_array($t_user)||empty($t_user)){
			        $errormsg = "不存在此用户信息!";
			        $this->getView()->display("error.phtml",array("error"=>$errormsg));
			        return false;
			    }
			    if($t_user['password'] != md5($params['password'])){
			        $errormsg = "密码信息错误，无法匹配此用户!";
			        $this->getView()->display("error.phtml",array("error"=>$errormsg));
			        return false;
			    }
			    $params['password'] = md5($params['password1']);
			}
			if(strlen($params['password'])<=0){
			    unset($params['password']);
			}
			unset($params["password1"]);
			unset($params["password2"]);
			$flag = $this->modify($tablename,$params,$username);
			if($flag){
			    $this->getView()->display("homepage.phtml",array("method"=>"reload"));
			}else{
				$this->getView()->display("error.phtml",array("error"=>"修改注册用户信息失败!"));
			}
		}
		/**
		  *注册用户信息修改
		  */
		private function modify($tablename,$params,$username){
			return Utils_Database::getInstance()->modify($tablename,$params,array("username"=>$username));
		}
		/**
		  *验证修改的注册信息
		  */
		private function validate($tablename,$params,&$errormsg){
			$errorinfos = array(
					"password" => "旧密码",
					"password1" => "新密码",
					"password2" => "确认新密码",
					"basic_income" => "基本月收入大于0且",
					"plan_save" => "计划存储",
					"to_family" => "孝敬父母金额",
					"rent" => "每月房租",
					);
			foreach($params as $k=>$v){
			    if($k=="password1"||$k=="password2"){
			    	if(strlen($v)>0){
			    	    if(strlen($params['password'])<=0){
					       $errormsg = "要修改密码必须填入旧密码!";
					       return false;
			    	    }
			    	    if(strlen($params['password1'])<=0){
			    	        $errormsg = "要修改密码必须填入新密码!";
			    	        return false;
			    	    }
			    	    if(strlen($params['password2'])<=0){
			    	        $errormsg = "要修改密码必须确认新密码!";
			    	        return false;
			    	    }
					}
			    }
				if($k=="basic_income" || $k=="plan_save" || $k=="to_family" || $k=="rent"){
					if(!is_numeric($v)){
						$errormsg = "$errorinfos[$k]内容必须为数字!";
						return false;
					}
					if(strlen($v)<=0){
					    $errormsg = "$errorinfos[$k]不能为空!";
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

