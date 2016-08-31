<?php
	class IndexController extends Yaf_Controller_Abstract {
		public function indexAction(){
			$this->getView()->display("login.phtml");
		}
		public function registerAction(){
			$this->getView()->display("register.phtml");
		}
		public function logoutAction(){
			unset($_SESSION['username']);
			unset($_SESSION['balance']);
			unset($_SESSION['card']);
			unset($_SESSION['cash']);
			unset($_SESSION['online']);
			session_destroy();
			$this->getView()->display("login.phtml");
		}
	}
?>
