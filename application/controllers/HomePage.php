<?php
	class HomePageController extends Yaf_Controller_Abstract {
		public function addAction(){
			$this->getView()->display("account/AddAccount.phtml");
		}
		public function transformAction(){
			$this->getView()->display("account/TransformAccount.phtml");
		}
		public function changeAction(){
		    header("Location:/fund/user/show");
		}
		public function listAction(){
			header("Location:/fund/account/md");
		}
	}
?>
