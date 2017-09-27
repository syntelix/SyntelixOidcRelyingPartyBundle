<?php

/*
 * This file is part of the SyntelixOidcRelayingPartyBundle package.
 */

namespace Syntelix\Bundle\OidcRelyingPartyBundle\OpenIdConnect\Constraint;

/**
 * IDTokenValidator
 *
 * Valid an id token like describe here
 * http://openid.net/specs/openid-connect-basic-1_0.html#IDTokenValidation
 *
 * @author valérian Girard <valerian.girard@educagri.fr>
 */
use DateTime;

/**
 * Class IDTokenValidator.
 */
class IDTokenValidator implements ValidatorInterface
{
    /**
     * @var array|null
     */
    private $errors;

    /**
     * @var array|null
     */
    private $options;

    /**
     * @var array|null
     */
    private $idToken;

    /**
     * @var array|null
     */
    private $claims;

    /**
     * IDTokenValidator constructor.
     *
     * @param $options
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * @param mixed $idToken
     */
    public function setIdToken($idToken)
    {
        $this->idToken = $idToken;
        $this->setClaims(is_object($idToken) ? $this->idToken->claims : $this->idToken['claims']);
    }

	/**
	 * @param array|null $claims
	 *
	 * @return $this
	 */
	public function setClaims( array $claims = null) {
		$this->claims = $claims;

		return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $this->errors = array();

        /* 1. The Issuer Identifier for the OpenID Provider
         * (which is typically obtained during Discovery) MUST exactly match
         * the value of the iss (issuer) Claim.
         */
        if ($this->options['issuer'] !== $this->claims['iss']) {
            $this->errors[] = 'Issuer are not the same';
        }

        /* 2. The Client MUST validate that the aud (audience) Claim contains
         * its client_id value registered at the Issuer identified by the iss (issuer)
         * Claim as an audience. The ID Token MUST be rejected if the ID Token
         * does not list the Client as a valid audience, or if it contains
         * additional audiences not trusted by the Client.
         */
        if (!$this->isClientIdInAudience($this->claims['aud'])) {
            $this->errors[] = 'The client does not validate the aud value';
        }

        /* 3. If the ID Token contains multiple audiences,
         * the Client SHOULD verify that an azp Claim is present.
         */
        if (!$this->isMultipleAudienceValide($this->claims['aud'])) {
            $this->errors[] = "The client's claim required an azp value";
        }

        /* 4. If an azp (authorized party) Claim is present,
         * the Client SHOULD verify that its client_id is the Claim Value.
         */
        if ($this->claims && array_key_exists('azp', $this->claims)) {
            if (!$this->isClientIdInAudience($this->claims['azp'])) {
                $this->errors[] = "The client's azp claim is not valid";
            }
        }

        /* 5. The current time MUST be before the time represented by
         * the exp Claim (possibly allowing for some small
         * leeway to account for clock skew).
         */
        if (!$this->isExpirationTimeValid()) {
            $this->errors[] = "The client's expiration time is out of bound";
        }

        /* 6. The iat Claim can be used to reject tokens that were issued
         * too far away from the current time, limiting the amount of time that
         * nonces need to be stored to prevent attacks.
         * The acceptable range is Client specific.
         */
        if (!$this->isIanValid()) {
            $this->errors[] = "The client's iat value is not valid";
        }

        /* 7. If the acr Claim was requested, the Client SHOULD check that
         * the asserted Claim Value is appropriate. The meaning and processing
         * of acr Claim Values is out of scope for this document.
         */
        // Not implemented yet

        /* 8. When a max_age request is made, the Client SHOULD check
         * the auth_time Claim value and request re-authentication if it
         * determines too much time has elapsed since the last End-User
         * authentication.
         */
        if ($this->isValidAuthTime() === false) {
            $this->errors[] = "The client's auth_time time is out of bound";
        }

        return (bool) count($this->errors) == 0;
    }

    /**
     * @param $aud
     *
     * @return bool
     */
    public function isClientIdInAudience($aud)
    {
        if (is_string($aud)) {
            return $this->options['client_id'] === $aud;
        } elseif (is_array($aud)) {
            return in_array($this->options['client_id'], $aud);
        }

        return false;
    }

    /**
     * @param $aud
     *
     * @return bool
     */
    public function isMultipleAudienceValide($aud)
    {
        if (is_string($aud)) {
            return true;
        } elseif (is_array($aud)) {
            if (count($aud) == 1) {
                return true;
            } elseif (count($aud) > 1) {
                if (array_key_exists('azp', $this->claims)) {
                    return $this->isClientIdInAudience($this->claims['azp']);
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isExpirationTimeValid()
    {
        $expirationTime = new DateTime();
        $expirationTime->setTimestamp($this->claims['exp']);

        return new DateTime('Now') < $expirationTime;
    }

    /**
     * @return bool
     */
    public function isIanValid()
    {
        $expirationTime = new DateTime();
        $expirationTime->setTimestamp($this->claims['iat']);
        $expirationTime->add(new \DateInterval(sprintf('PT%dS', $this->options['token_ttl'])));

        return new DateTime('Now') < $expirationTime;
    }

    /**
     * @return bool|null
     */
    public function isValidAuthTime()
    {
        if ($this->options['authentication_ttl'] !== null && $this->options['authentication_ttl'] > 0) {
            if ($this->claims && array_key_exists('auth_time', $this->claims)) {
                $expirationAuthTime = new DateTime();
                $expirationAuthTime->setTimestamp($this->claims['auth_time']);
                $expirationAuthTime->add(new \DateInterval(sprintf('PT%dS', $this->options['authentication_ttl'])));

                return new DateTime('Now') < $expirationAuthTime;
            } else {
                return null;
            }
        }

        return true;
    }
}
