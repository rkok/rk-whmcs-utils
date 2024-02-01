<?php

namespace RKWhmcsUtils\Models;

class WhmcsCurrency
{
    protected int $id;
    protected string $code;

    public function __construct(int $id, string $code) {
        $this->id = $id;
        $this->code = $code;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): WhmcsCurrency
    {
        $this->id = $id;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): WhmcsCurrency
    {
        $this->code = $code;
        return $this;
    }
}
