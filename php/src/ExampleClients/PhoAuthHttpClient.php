<?php namespace ExampleClients;
use PhoAuth\Signer;
use Zend\Http\Client;

class PhoAuthHttpClient {

    private $oauthParams = array();

    /**
     * The base oauth params
     */
    private $oauthParamsDefaults = array(
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_version'          => '1.0',
        'oauth_consumer_key'     => '',
        'oauth_token'            => '',
    );

    private $consumerSecret = '';

    /**
       * Helper for the OAuth consumer signer.
       * @return string
       */
    public function getOAuthConsumerSecret()
    {
        return $this->consumerSecret;
    }

    private $tokenSecret = '';
    /**
     * Helper for the OAuth consumer signer.
     * @return string
     */
    public function getOAuthAccessTokenSecret()
    {
        return $this->tokenSecret;
    }

    public function getOauthParams()
    {
        return $this->oauthParams;
    }

    public function __construct($options)
    {
        if (isset($options['oauthConfig'])) {
            $this->consumerSecret = $options['oauthConfig']['oauth_consumer_secret'];
            $this->tokenSecret = $options['oauthConfig']['oauth_token_secret'];
            // Loop through the defaults and set them before merging in options
            foreach ($this->oauthParamsDefaults as $oauthParam => $oauthParamValue) {
                if (isset($options['oauthConfig'][$oauthParam])) {
                    $this->oauthParams[$oauthParam] = $options['oauthConfig'][$oauthParam];
                } else {
                    $this->oauthParams[$oauthParam] = $oauthParamValue;
                }
            }
        }

        if (isset($options['requestConfig'])) {
            $this->client = new Client(
                $this->buildUri($options['requestConfig'])
            );
            $this->client->setAdapter(new \Zend\Http\Client\Adapter\Curl());
            $this->client->setParameterGet($options['requestConfig']['params']);
            $this->client->setMethod($options['requestConfig']['method']);
            $this->setOAuthHeader($options['requestConfig']);
        }
    }

    public function setOAuthHeader($requestConfig)
    {
        $this->oauthParams['oauth_nonce'] = uniqid();
        $this->oauthParams['oauth_timestamp'] = time();

        $params = array_merge($requestConfig['params'], $this->oauthParams);

        $signer = new Signer(
            $requestConfig['scheme'],
            $requestConfig['method'],
            $requestConfig['host'],
            $requestConfig['port'],
            $requestConfig['uri'] . '?' . http_build_query($params)
        );

        $signer->setConsumerSecretFinder(
            array($this, 'getOAuthConsumerSecret')
        );
        $signer->setTokenSecretFinder(
            array($this, 'getOAuthAccessTokenSecret')
        );
        $this->client->setHeaders(
            array('Authorization' => $signer->makeOAuthHeader())
        );
    }

    public function getResponseBody()
    {
        $this->response = $this->client->send();
        return json_decode($this->response->getBody());
    }

    public function buildUri($config)
    {
        return $config['scheme'] .  '://' .
            $config['host'] .  ':' . $config['port'] .
            $config['uri'];
    }
}