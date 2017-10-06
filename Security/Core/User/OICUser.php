<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Security\Core\User;

use Serializable;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * OICUser.
 *
 * @author
 */
class OICUser implements AdvancedUserInterface, Serializable, EquatableInterface
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @var array
     */
    protected $roles = array();

    /**
     * @param string $username
     * @param null   $roles
     * @param null   $attributes
     */
    public function __construct($username, $roles = null, $attributes = null)
    {
        $this->username = $username;
        $this->roles = $roles;
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        if (0 == count($this->roles)) {
            return array('ROLE_USER', 'ROLE_OIC_USER');
        }

        return $this->roles;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(UserInterface $user)
    {
        return $user->getUsername() === $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(UserInterface $user)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->username,
            $this->attributes,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        list($this->username, $this->attributes) = $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUsername();
    }
}
