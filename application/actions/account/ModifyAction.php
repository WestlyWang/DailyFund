<?php
	
	/**
	  *错误更新Action
	  *账户信息变更Action
	  *@file UpdateAction.php
	  *@author wangshuai
	  *@time 2016-08-09
	  */
	class ModifyAction extends Yaf_Action_Abstract{
		public function execute(){
			$username = $_SESSION['username'];
			$request_method = $_SERVER['REQUEST_METHOD'];
			$request_method = strtolower($request_method);
			$params = array();
			if($request_method == "get"){
				$params = $this->getRequest()->getParams();
			}
			else{
				$params = $_POST;
			}
			$conditions = array();
			$conditions['id'] = $params['id'];
			unset($params['id']);
			if($params['type']==-1){
				$this->responseError("请选择收支类型!");
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
								 "income5"=>"转入",
								 "consume1"=>"房租",
								 "consume2"=>"定存",
								 "consume3"=>"餐费",
								 "consume4"=>"水电",
								 "consume5"=>"购物",
								 "consume6"=>"充电",
								 "consume7"=>"孝敬父母",
								 "consume8"=>"其他",
								 "consume9"=>"转出",
								 );
			$params['briefreason'] = $reflection[$params['briefreason']];
			$sourcereflection = array(
								"card" => "银行卡",
								"cash" => "现金",
								"online" => "在线支付",
					);
			$params['source'] = $sourcereflection[$params['source']];
			if(AccountModel::modify($username,$params,$conditions)){
				$this->getView()->display("homepage.phtml",array("method" => "modify"));
			//	header("Location:list");
			}else{
				$this->responseError("更新消费信息失败!");
			}
		}
		private function responseError($errorMsg){
				$this->getView()->display("error.phtml",array("error"=>$errorMsg));
		}
	}
?>

