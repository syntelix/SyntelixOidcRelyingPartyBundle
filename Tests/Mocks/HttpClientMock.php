<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Tests\Mocks;

use Buzz\Client\AbstractCurl;
use Buzz\Message\Response;
use Buzz\Message\RequestInterface;
use Buzz\Message\MessageInterface;

/**
 * HttpClientMock.
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class HttpClientMock extends AbstractCurl
{
    /**
     * @var null
     */
    public $response = null;
    /**
     * @var
     */
    public $request;

    /**
     * @param RequestInterface $request
     * @param MessageInterface $response
     */
    public function send(RequestInterface $request, MessageInterface $response)
    {
        $this->request = $request;

        $response->setHeaders($this->response->getHeaders());
        $response->setContent($this->response->getContent());
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $isOk
     * @param $headers
     * @param $content
     */
    public function setResponseContent($isOk, $headers, $content)
    {
        $this->response = new Response();
        $this->response->setContent($content);
        $this->response->setHeaders($headers);
    }
}
