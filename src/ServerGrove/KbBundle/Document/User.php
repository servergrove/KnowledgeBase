<?php

namespace ServerGrove\KbBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User
 *
 * @PHPCRODM\Document(referenceable=true, repositoryClass="ServerGrove\KbBundle\Repository\UserRepository")
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class User implements AdvancedUserInterface
{

    /**
     * @var string
     * @PHPCRODM\Id(strategy="repository")
     */
    private $id;

    /**
     * @var string
     * @PHPCRODM\String
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     * @PHPCRODM\String
     * @Assert\NotBlank
     */
    private $username;

    /**
     * @var string
     * @PHPCRODM\String
     * @Assert\NotBlank
     */
    private $password;

    /**
     * @var string
     * @PHPCRODM\String
     */
    private $salt;

    /**
     * @var string
     * @PHPCRODM\String
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * @var \Doctrine\ODM\PHPCR\MultivaluePropertyCollection
     * @PHPCRODM\String(multivalue=true)
     */
    private $roles;

    /**
     * @var bool
     * @PHPCRODM\Boolean
     */
    private $enabled;

    /**
     * @var bool
     * @PHPCRODM\Boolean
     */
    private $locked;

    /**
     * @var \DateTime
     * @PHPCRODM\Date
     * @Assert\Date
     */
    private $expirationDate;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @PHPCRODM\ReferenceMany(targetDocument="Article")
     */
    private $subscriptions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->salt          = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->roles         = array();
        $this->subscriptions = new ArrayCollection();
    }

    public function __sleep()
    {
        return array(
            'id',
            'name',
            'username',
            'password',
            'salt',
            'email',
            'roles',
            'enabled',
            'locked',
            'expirationDate'
        );
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $role
     */
    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    /**
     * @param string $role
     */
    public function removeRole($role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * @param array $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * Returns the roles granted to the user.
     *
     * @return array
     */
    public function getRoles()
    {
        return is_object($this->roles) ? $this->roles->toArray() : $this->roles;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt.
     *
     * @return string The salt
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @param \DateTime $expirationDate
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * @return \DateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param boolean $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param \ServerGrove\KbBundle\Document\Article $article
     */
    public function subscribe(Article $article)
    {
        $this->getSubscriptions()->add($article);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSubscriptions()
    {
        if (is_null($this->subscriptions)) {
            $this->subscriptions = new ArrayCollection();
        }

        return $this->subscriptions;
    }

    /**
     * Removes sensitive data from the user.
     *
     * @return void
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * Checks whether the user's account has expired.
     *
     * @return Boolean true if the user's account is non expired, false otherwise
     */
    public function isAccountNonExpired()
    {
        return is_null($this->getExpirationDate()) || time() < $this->getExpirationDate()->getTimestamp();
    }

    /**
     * Checks whether the user is locked.
     *
     * @return Boolean true if the user is not locked, false otherwise
     */
    public function isAccountNonLocked()
    {
        return !$this->isLocked();
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * @return Boolean true if the user's credentials are non expired, false otherwise
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * @return Boolean true if the user is enabled, false otherwise
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return string
     */
    protected function getSluggableValue()
    {
        return $this->username;
    }
}
