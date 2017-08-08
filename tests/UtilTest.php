<?php 

/**
*  @author Maxim P. (extead@gmail.com)
*/
class UtilTest extends PHPUnit_Framework_TestCase{

  public function testIsThereAnySyntaxError(){
	$var = new \Extead\UAParser\Util();
	$this->assertTrue(is_object($var));
	unset($var);
  }

  public function testMethod1(){
	$var = new \Extead\UAParser\Util();
	$this->assertTrue($var->lowerize("TeSt") === 'test');
	unset($var);
  }
  
}