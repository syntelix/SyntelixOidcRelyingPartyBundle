<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\Response;

use JOSE_JWT;
use Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\JWK\JWKSetHandler;
use Buzz\Message\Response as HttpClientResponse;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\HttpFoundation\Response;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\InvalidAuthorizationCodeException;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\InvalidClientOrSecretException;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\InvalidIdSignatureException;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\InvalidRequestException;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\InvalidResponseTypeException;
use Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\Exception\UnsupportedGrantTypeException;

/**
 * OICResponseHandler.
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
class OICResponseHandler
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var JWKSetHandler
     */
    protected $jwkHandler;

    /**
     * __construct.
     *
     * @param JWKSetHandler $jwkHandler
     * @param array         $options
     */
    public function __construct(JWKSetHandler $jwkHandler, $options)
    {
        $this->jwkHandler = $jwkHandler;
        $this->options = $options;
    }

    /**
     * Search error in header and in content of the response.
     * If an error is found an exception is thrown.
     * If all is clear, the content is JSON decoded (if needed) and returned as an array.
     *
     * @param HttpClientResponse $response
     *
     * @return array $content
     */
    public function handleHttpClientResponse(HttpClientResponse $response)
    {
        $content = $this->getContent($response);

        if ($response->getStatusCode() >= Response::HTTP_UNAUTHORIZED) {
            if (null !== ($authError = $response->getHeader('WWW-Authenticate'))) {
                preg_match('/^Basic realm="(.*)"$/', $authError, $matches);

                if (empty($matches)) {
                    preg_match('/^Bearer realm="(.*)"$/', $authError, $matches);
                }

                $content = array('error' => 'Authentication fail', 'error_description' => $matches[1]);
            }
        } elseif ($response->getStatusCode() >= Response::HTTP_BAD_REQUEST) {
            if (null !== ($bearerError = $response->getHeader('WWW-Authenticate'))) {
                preg_match('/^Bearer error="(.*)", error_description="(.*)"$/', $bearerError, $matches);
                $content = array('error' => $matches[1], 'error_description' => $matches[1]);
            }
        }

        if (!$this->hasError($content)) {
            return $content;
        }

        return null;
    }

    /**
     * handleTokenAndAccessTokenResponse.
     *
     * @param HttpClientResponse $response
     *
     * @return JOSE_JWT
     */
    public function handleTokenAndAccessTokenResponse(HttpClientResponse $response)
    {
        $content = $this->handleHttpClientResponse($response);

        if ('' == $content) {
            return $content;
        }

        if ($this->isJson($content['id_token'])) {
            $jsonDecoded = $this->getJsonEncodedContent($content['id_token']);

            $content['id_token'] = new JOSE_JWT($jsonDecoded);
        } else {
            $content['id_token'] = $this->getJwtEncodedContent($content['id_token']);
        }

        return $content;
    }

    /**
     * handleEndUserinfoResponse.
     *
     * @param HttpClientResponse $response
     *
     * @return JOSE_JWT
     *
     * @throws InvalidIdSignatureException
     */
    public function handleEndUserinfoResponse(HttpClientResponse $response)
    {
        $content = $this->handleHttpClientResponse($response);

        if (!$content instanceof JOSE_JWT) {
            return $content;
        }

        $this->verifySignedJwt($content);

        return $content->claims;
    }

    /**
     * getContent.
     *
     * @param HttpClientResponse $response
     *
     * @return array
     */
    protected function getContent(HttpClientResponse $response)
    {
        $contentType = explode(';', $response->getHeader('Content-Type'));
        if (in_array('application/json', $contentType)) {
            return $this->getJsonEncodedContent($response->getContent());
        } elseif (in_array('application/jwt', $contentType)) {
            return $this->getJwtEncodedContent($response->getContent());
        }
    }

    /**
     * @param string $content
     *
     * @return array
     */
    protected function getJsonEncodedContent($content)
    {
        if (empty($content)) {
            return null;
        }

        $jsonDecode = new JsonDecode(true);

        return $jsonDecode->decode($content, JsonEncoder::FORMAT);
    }

    /**
     * @param $content
     *
     * @return \JOSE_JWE|JOSE_JWT
     */
    protected function getJwtEncodedContent($content)
    {
        $jwt = JOSE_JWT::decode($content);

        $this->verifySignedJwt($jwt);

        return $jwt;
    }

    /**
     * Check the signature of an JSON Web Token if there is a signature.
     *
     * @param JOSE_JWT $jwt
     *
     * @return JOSE_JWT
     *
     * @throws InvalidIdSignatureException
     */
    protected function verifySignedJwt(JOSE_JWT $jwt)
    {
        if (array_key_exists('alg', $jwt->header)) {
            $key = null;

            // get the right key base on the algorithm
            if ('HS' == substr($jwt->header['alg'], 0, 2)) {
                $key = $this->options['client_secret'];
            } elseif ('RS' == substr($jwt->header['alg'], 0, 2)) {
                $jwkSetJsonObject = $this->jwkHandler->getJwk();
                $jwkSet = new \JOSE_JWKSet();
                $jwkSet->setJwksFromJsonObject($jwkSetJsonObject);
                $key = $jwkSet->filterJwk('use', \JOSE_JWK::JWK_USE_SIG);

                if (null === $key && array_key_exists(0, $jwkSet->keys)) {
                    $key = $jwkSet->keys[0];
                }
            }

            if (null !== $key) {
                $jws = new \JOSE_JWS($jwt);

                try {
                    $jws->verify($key);
                } catch (\Exception $e) {
                    throw new InvalidIdSignatureException($e->getMessage());
                }
            }
        }

        return $jwt;
    }

    /**
     * @param array|object $content
     *
     * @return bool
     *
     * @throws InvalidRequestException
     * @throws InvalidResponseTypeException
     * @throws InvalidAuthorizationCodeException
     * @throws InvalidClientOrSecretException
     * @throws UnsupportedGrantTypeException
     */
    public function hasError($content)
    {
        if (!is_array($content)) {
            return false;
        }

        if (array_key_exists('error', $content)) {
            if (!array_key_exists('error_description', $content)) {
                $content['error_description'] = $content['error'];
            }

            switch ($content['error']) {
                case 'invalid_request':
                    throw new InvalidRequestException($content['error_description']);
                    break;
                case 'invalid_response_type':
                    throw new InvalidResponseTypeException($content['error_description']);
                    break;
                case 'invalid_authorization_code':
                    throw new InvalidAuthorizationCodeException($content['error_description']);
                    break;
                case 'invalid_client':
                    throw new InvalidClientOrSecretException($content['error_description']);
                    break;
                case 'unsupported_grant_type':
                    throw new UnsupportedGrantTypeException($content['error_description']);
                    break;
                case 'unauthorized_client':
                    throw new InvalidClientOrSecretException($content['error_description']);
                    break;
                default:
                    throw new InvalidRequestException($content['error_description']);
                    break;
            }
        }

        return false;
    }

    /**
     * @param $string
     *
     * @return bool
     */
    private function isJson(string $string)
    {
        json_decode($string);

        return JSON_ERROR_NONE == json_last_error();
    }
}
