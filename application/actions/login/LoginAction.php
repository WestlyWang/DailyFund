<?php
	
	/**
	  *登录处理Action
	  *@file LoginAction.php
	  *@author wangshuai
	  *@time 2016-08-08
	  */
	class LoginAction extends Yaf_Action_Abstract{
		public function execute(){
			$params = $_POST;
			if(!$this->validate($params,$errorMsg)){
				$this->responseError($errorMsg);
				return;
			}
			$params['password'] = md5($params['password']);
			$total = Utils_Database::getInstance()->total("users",$params);
			if($total != 1){
				$this->responseError("无该用户或密码错误!");
				return;
			}
			$username = $params['username'];
			$_SESSION['username']= $username;
			$tablename = md5($username)."_detail";
			$r = Utils_Database::getInstance()->first($tablename,array("user"=>$username));
			if(empty($r)){
				$r['balance'] = 0;
			}
			$_SESSION['balance'] = $r['balance'];
			$r = Utils_Database::getInstance()->first($tablename,array("user"=>$username,"source"=>"现金"));
			$_SESSION['cash'] = empty($r['singlebalance'])?0:$r['singlebalance'];
			$r = Utils_Database::getInstance()->first($tablename,array("user"=>$username,"source"=>"银行卡"));
			$_SESSION['card'] = empty($r['singlebalance'])?0:$r['singlebalance'];
			$r = Utils_Database::getInstance()->first($tablename,array("user"=>$username,"source"=>"在线支付"));
			$_SESSION['online'] = empty($r['singlebalance'])?0:$r['singlebalance'];
			$this->getView()->display("homepage.phtml");
		}
		private function validate($params,&$errorMsg){
			if(!is_array($params) || empty($params)){
				$errorMsg = "不允许非法登录";
				return false;
			}
			if($params['username'] == '' || strlen($params['username'])==0){
				$errorMsg = "用户名不允许为空!";
				return false;
			}
			if($params['password'] == '' || strlen($params['password'])==0){
				$errorMsg = "密码不允许为空!";
				return false;
			}
			return true;
		}
		private function responseError($errorMsg){
			$this->getView()->display("error.phtml",array("error" => $errorMsg));
		}
	}
?>

