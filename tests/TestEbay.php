<?php
/**
 * Created by PhpStorm.
 * User: Optimistic
 * Date: 18/03/2015
 * Time: 00:51
 */

use Maverickslab\Ebay\Facade\Ebay;

class TestEbay extends \PHPUnit_Framework_TestCase {
    public function testConvertsMethodNameToClassName(){
        Ebay::shouldReceive('convertMethodNameToClass')
            ->once()
            ->with('product')
            ->andReturn('\Maverickslab\Ebay\Product');

        $this->assertEquals('\Maverickslab\Ebay\Product', Ebay::convertMethodNameToClaass('product'));
    }
}
