<?php
	
	/**
	  *账目添加Action
	  *@file AddAction.php
	  *@author wangshuai
	  *@time 2016-08-08
	  */
	class AddAction extends Yaf_Action_Abstract{
		public function execute(){
			$request_method = $_SERVER['REQUEST_METHOD'];
			$username = $_SESSION['username'];
			$request_method = strtolower($request_method);
			$params = array();
			if($request_method == "get"){
				$params = $this->getRequest()->getParams();
			}
			else{
				$params = $_POST;
			}
			if($params['type']==-1){
				$this->responseError("请选择收支类型!");
				return;
			}
			if($params['source']==-1){
				$this->responseError("请选择收支来源!");
				return;
			}
			if(strlen($params['cost'])==0 || !is_numeric($params['cost']) || $params['cost']<=0){
				$this->responseError("请输入正确的消费金额!");
				return;
			}
			$reflection =  array("income1"=>"工资",
								 "income2"=>"奖金",
								 "income3"=>"理财",
								 "income4"=>"其他",
								 "consume1"=>"房租",
								 "consume2"=>"定存",
								 "consume3"=>"餐费",
								 "consume4"=>"水电",
								 "consume5"=>"购物",
								 "consume6"=>"充电",
								 "consume7"=>"孝敬父母",
								 "consume8"=>"其他",
								 );
			$sourcereflection = array(
								"card" => "银行卡",
								"cash" => "现金",
								"online" => "在线支付",
					);
			$params['briefreason'] = $reflection[$params['briefreason']];
			$params['cost'] = number_format($params['cost'],2,'.','');
			$params['user'] = $username;
			$params['recordtime'] = date('Y-m-d',time());
			if($params['type']==0){
				$_SESSION['balance'] += $params['cost'];
				$_SESSION[$params['source']] += $params['cost'];
			}else{
				$_SESSION['balance'] -= $params['cost'];
				$_SESSION[$params['source']] -= $params['cost'];
			}
			$params['balance'] = $_SESSION['balance'];
			$params['singlebalance'] = $_SESSION[$params['source']];
			$params['source'] = $sourcereflection[$params['source']];
			if(AccountModel::add($username,$params)){
			//	$this->getView()->display("homepage.phtml",array("method" => "add"));
				header("Location:list");
			}else{
				$this->responseError("账目添加失败!");
			}
		}
		private function responseError($errorMsg){
				$this->getView()->display("error.phtml",array("error"=>$errorMsg));
		}
	}
?>

