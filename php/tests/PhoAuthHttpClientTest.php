<?php
require_once __DIR__ . '/../src/bootstrap.php';
use ExampleClients\PhoAuthHttpClient;

class PhoAuthHttpClientTest extends PHPUnit_Framework_TestCase {

    private $requestConfig = array(
        'scheme' => 'https',
        'method' => 'GET',
        'host'   => 'oauth-api.beatport.com',
        'port'   => 443,
        'uri'    => '/catalog/3/mixes/detail',
        'params' => array('id' => '1'),
    );

    private $oauthConfig = array();

    public function setUp()
    {
        $this->oauthConfig = require( __DIR__ . '/oauth.cfg.dist');
        $this->client = new PhoAuthHttpClient(
            array(
                'requestConfig' => $this->requestConfig,
                'oauthConfig' => $this->oauthConfig
            )
        );
    }

    public function testConstructor()
    {
        $this->assertCount(6, $this->client->getOauthParams());
    }
    
    public function testConstructorNoOauthParams()
    {
        $requestConfig = array('foo' => 'bar');
        $client = new PhoAuthHttpClient(
            array(
                'requestConfig' => $this->requestConfig
            )
        );
        $this->assertCount(2, $client->getOauthParams());
    }

    public function testGetConsumerSecret()
    {
        $this->assertEquals(
            $this->client->getOAuthConsumerSecret(),
            $this->oauthConfig['oauth_consumer_secret']
        );
    }
    
    public function testGetAccessTokenSecret()
    {
        $this->assertEquals(
            $this->client->getOAuthAccessTokenSecret(),
            $this->oauthConfig['oauth_token_secret']
        );
    }
    
    public function testRequest()
    {
        $response = $this->client->getResponseBody();
        $this->assertObjectHasAttribute('metadata', $response);
        $this->assertObjectNotHasAttribute('error', $response->metadata,
            'Request Failed: ' . print_r($response->metadata, true));
    }

    public function testBuildUri()
    {
        $this->assertEquals(
            'https://oauth-api.beatport.com:443/catalog/3/mixes/detail',
            $this->client->buildUri($this->requestConfig)
        );
    }
}
