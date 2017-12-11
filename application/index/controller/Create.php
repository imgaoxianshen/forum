<?php
namespace app\index\controller;

// use first\second\SoapDiscovery;
use app\index\controller\Test;

class Create extends \think\Controller
{
	public function create(){
		import('SoapDiscovery',EXTEND_PATH);
		// include('../application/index/controller/Test.php');
		$disc = new  \SoapDiscovery('Test', 'soap');
		$disc->getWSDL();  
	}
	public function elss(){
		echo 123123;
	}
}