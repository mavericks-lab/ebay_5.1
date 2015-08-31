<?php

    /**
 * Created by PhpStorm.
 * User: Optimistic
 * Date: 17/03/2015
 * Time: 15:00
 */

class TestAPIRequester extends \PHPUnit_Framework_TestCase {

    public function setUp(){
        $client = Mockery::mock('GuzzleHttp\Client');
        $this->requester = new \Maverickslab\Ebay\APIRequester($client);
    }

    public function testCreateAnApiRequesterObject(){
        $client = Mockery::mock('GuzzleHttp\Client');
        $this->assertInstanceOf('\Maverickslab\Ebay\APIRequester',new \Maverickslab\Ebay\APIRequester($client));
    }

    public function testRequest(){
        $this->assertInstanceOf('GuzzleHttp\Message\Response',$this->requester->request([], 'String', $site_id = 1));
    }
}
