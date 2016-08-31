<?php
require_once 'boot.inc.php';
class Auto{
	private static $instance = null;
	private function __construct(){
	}
	public static function getInstance(){
		if($instance==null){
			$instance = new Auto();
		}
		return $instance;
	}
	private function backup($username){
		$tablename = md5($username)."_detail";
		$file = APP_PATH."/datastore/$username/account_detail_".date("Ym",time()).".log.bf";
		$records = Utils_Database::getInstance()->query($tablename);
		if(empty($records)){
			return;
		}
		$length = count($records);
		$title = array("ID","用户","时间","原因","来源","金额","收支","单项余额","余额","备注");
		$title[0] = str_pad($title[0],6,'*',STR_PAD_BOTH);
		$title[1] = str_pad($title[1],18,'*',STR_PAD_BOTH);//16+mb_strlen($title[1])
		$title[2] = str_pad($title[2],12,'*',STR_PAD_BOTH);
		$title[3] = str_pad($title[3],10,'*',STR_PAD_BOTH);
		$title[4] = str_pad($title[4],10,'*',STR_PAD_BOTH);
		$title[5] = str_pad($title[5],10,'*',STR_PAD_BOTH);
		$title[7] = str_pad($title[7],10,'*',STR_PAD_BOTH);
		$title[8] = str_pad($title[8],10,'*',STR_PAD_BOTH);
		file_put_contents($file,implode("\t",$title),FILE_APPEND);
		file_put_contents($file,"\n",FILE_APPEND);
		for($index=0;$index<$length;$index++){
			$record = $records[$index];
			$record['id'] = str_pad($index+1,6,'0',STR_PAD_LEFT);
			$record['user'] = str_pad($record['user'],16,' ',STR_PAD_BOTH);
			$record['briefreason'] = str_pad($record['briefreason'],12-mb_strlen($record['briefreason']),' ',STR_PAD_RIGHT);
			$record['cost'] = str_pad($record['cost'],8,' ',STR_PAD_LEFT);
			$record['type'] = $record['type']==1?"支出":"收入";
			$record['source'] = str_pad($record['source'],8+mb_strlen($record['source']),' ',STR_PAD_RIGHT);
			$record['singlebalance'] = str_pad($record['singlebalance'],8,' ',STR_PAD_LEFT);
			$record['balance'] = str_pad($record['balance'],8,' ',STR_PAD_LEFT);
			file_put_contents($file,implode("\t",$record),FILE_APPEND);
			file_put_contents($file,"\n",FILE_APPEND);
		}
	}
	private function clear($username){
		$tablename = md5($username)."_detail";
		Utils_Database::getInstance()->del($tablename);
	}
	private function statistics($username){
		$tablename = md5($username)."_detail";
		$records = Utils_Database::getInstance()->query($tablename);
		if(empty($records)){
			return array();
		}
		$income = array("工资"=>0,"奖金"=>0,"理财"=>0,"转入"=>0,"其他"=>0);
		$consume = array("房租"=>0,"定存"=>0,"孝敬父母"=>0,"水电"=>0,"餐费"=>0,"购物"=>0,"充电"=>0,"转出"=>0,"其他"=>0);
		$income_total = 0.0;
		$consume_total = 0.0;
		$singlebalance =  array("现金"=>0,"银行卡"=>0,"在线支付"=>0,"lastbalance"=>0);
		foreach($records as $record){
			if($record['type'] == 0){
				$income_total = $income_total + $record['cost'];
				$income[$record['briefreason']] += $record['cost'];
				$singlebalance[$record['source']] += $record['cost'];
				$singlebalance['lastbalance'] +=$record['cost'];
			}else{
				$consume_total += $record['cost'];
				$consume[$record['briefreason']] += $record['cost'];
				$singlebalance[$record['source']] -= $record['cost'];
				$singlebalance['lastbalance'] -=$record['cost'];
			}
		}
		$balance = $singlebalance['银行卡'];//$income_total - $consume_total;
		if($balance>=1000){
			$c = floor($balance/1000);
			$balance = $balance - $c*1000;
			$saveinfo = array(
				"user" => $username,
				"recordtime" => date("Y-m-d",time()+3600),
				"briefreason" => "定存",
				"cost" => $c*1000,
				"source"=>"银行卡",
				"singlebalance" => $balance,
				"type" => "1",
				"balance" => $singlebalance['lastbalance']-$c*1000,
				"remarks" => "当月银行卡结余大于1000，定存"
				);
			Utils_Database::getInstance()->add($tablename,$saveinfo);
			$consume['定存'] += $c*1000;
			$consume_total += $c*1000;
			$singlebalance['银行卡'] -=$c*1000;
			$singlebalance['lastbalance'] -=$c*1000;
		}
		$balance = $singlebalance['lastbalance'];
		foreach($singlebalance as $k=>$v){
			if($k!="lastbalance" && $v>0){
				$balance -= $v;
				$otherinfo = array(
					"user" => $username,
					"recordtime" => date("Y-m-d",time()+3600),
					"briefreason" => "其他",
					"cost" => $v,
					"source" => $k,
					"singlebalance" => 0,
					"type" => "1",
					"balance" => $balance,
					"remarks" => "$k余额结转下月"
				);
				Utils_Database::getInstance()->add($tablename,$otherinfo);
				$consume_total += $v;
				$consume['其他'] += $v;
			}
		}
		$income_detail = "";
		$consume_detail = "";
		foreach($income as $k=>$v){
			$income_detail .= "$k:$v;";
		}
		foreach($consume as $k=>$v){
			$consume_detail .= "$k:$v;";
		}
		$params = array(
				'st_time' => date("Y-m",time()),
				'income' => '收入',
				'income_total' => $income_total,
				'income_detail' => $income_detail,
				'consume' => '支出',
				'consume_total' => $consume_total, //收支均衡，剩余结转下月
				'consume_detail' => $consume_detail,
				'balance' => 0
				);
		Utils_Database::getInstance()->add(md5($username)."_statistics",$params);
		return $singlebalance;
	}
	private function init($username,$basic_income,$plan_save,$to_family,$rent,$lastbalance){
		$tablename = md5($username)."_detail";
		$balance = 0;
		foreach($lastbalance as $k=>$v){
			if($k!="lastbalance" && $v != 0){
				$balance += $v;
				$otherinfo = array(
					"user" => $username,
					"recordtime" => date("Y-m-d",time()+3600),
					"briefreason" => "其他",
					"cost" => $v,
					"source" => $k,
					"singlebalance" => $v,
					"type" => "0",
					"balance" => $balance,
					"remarks" => $k."余额结转至本月"
				);
				Utils_Database::getInstance()->add($tablename,$otherinfo);
			}
		}
		$balance = $basic_income + $balance;
		$cardbalance = empty($lastbalance['银行卡']) ? $basic_income : $basic_income + $lastbalance['银行卡'];
		$incomeinfo = array(
				"user" => $username,
				"recordtime" => date("Y-m-d",time()+3600),
				"briefreason" => "工资",
				"cost" => $basic_income,
				"type" => "0",
				"source" => "银行卡",
				"singlebalance" => $cardbalance,
				"balance" => $balance,
				"remarks" => "上月工资"
				);	
		$balance-=$plan_save;
		$cardbalance -= $plan_save;
		$saveinfo = array(
				"user" => $username,
				"recordtime" => date("Y-m-d",time()+3600),
				"briefreason" => "定存",
				"cost" => $plan_save,
				"type" => "1",
				"source" => "银行卡",
				"singlebalance" => $cardbalance,
				"balance" => $balance,
				"remarks" => "每月攒钱计划"
				);
		$balance-=$to_family;
		$cardbalance -= $to_family;
		$familyinfo = array(
				"user" => $username,
				"recordtime" => date("Y-m-d",time()+3600),
				"briefreason" => "孝敬父母",
				"cost" => $to_family,
				"type" => "1",
				"source" => "银行卡",
				"singlebalance" => $cardbalance,
				"balance" => $balance,
				"remarks" => "每月孝敬父母，补贴家用"
				);
		$balance-=$rent;
		$cardbalance -= $rent;
		$rentinfo = array(
				"user" => $username,
				"recordtime" => date("Y-m-d",time()+3600),
				"briefreason" => "房租",
				"cost" => $rent,
				"type" => "1",
				"source" => "银行卡",
				"singlebalance" => $cardbalance,
				"balance" => $balance,
				"remarks" => ""
				);
		Utils_Database::getInstance()->add($tablename,$incomeinfo);
		Utils_Database::getInstance()->add($tablename,$saveinfo);
		Utils_Database::getInstance()->add($tablename,$familyinfo);
		Utils_Database::getInstance()->add($tablename,$rentinfo);
	}
	public function run(){
		$users = Utils_Database::getInstance()->query("users");
		foreach($users as $user){
			$username = $user['username'];
			$basic_income = $user['basic_income'];
			$plan_save = $user['plan_save'];
			$to_family = $user['to_family'];
			$rent = $user['rent'];
			$lastbalance = $this->statistics($username);
			$this->backup($username);
			$this->clear($username);
			$this->init($username,$basic_income,$plan_save,$to_family,$rent,$lastbalance);
		}
	}
	public function runSingle($uname){
		$user = Utils_Database::getInstance()->first("users",array("username" => $uname));
		$basic_income = $user['basic_income'];
		$plan_save = $user['plan_save'];
		$to_family = $user['to_family'];
		$rent = $user['rent'];
		$lastbalance = $this->statistics($uname);
		$this->backup($uname);
		$this->clear($uname);
		$this->init($uname,$basic_income,$plan_save,$to_family,$rent,$lastbalance);
	}
}
if($argc==1){
	Auto::getInstance()->run();
}else{
	$uname = $argv[1];
	Auto::getInstance()->runSingle($uname);
}
?>
