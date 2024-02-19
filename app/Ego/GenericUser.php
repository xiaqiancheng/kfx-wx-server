<?php

declare(strict_types=1);

namespace App\Ego;

use HyperfExt\Auth\Contracts\AuthenticatableInterface;
use HyperfExt\Jwt\Contracts\JwtSubjectInterface;

class GenericUser implements AuthenticatableInterface, JwtSubjectInterface
{
    /**
     * All of the user's attributes.
     *
     * @var array
     */
    protected $attributes;

    /**
     * Create a new generic User object.
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Dynamically access the user's attributes.
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->attributes[$key];
    }

    /**
     * Dynamically set an attribute on the user.
     *
     * @param mixed $value
     */
    public function __set(string $key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Dynamically check if a value is set on the user.
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Dynamically unset a value on the user.
     */
    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->attributes[$this->getAuthIdentifierName()];
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword(): ?string
    {
        return $this->attributes['password'];
    }

    /**
     * Get the "remember me" token value.
     */
    public function getRememberToken(): ?string
    {
        return $this->attributes[$this->getRememberTokenName()];
    }

    /**
     * Set the "remember me" token value.
     */
    public function setRememberToken(string $value)
    {
        $this->attributes[$this->getRememberTokenName()] = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName(): ?string
    {
        return 'remember_token';
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJwtIdentifier()
    {
        return $this->attributes;
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJwtCustomClaims(): array
    {
        return $this->attributes;
    }
}
