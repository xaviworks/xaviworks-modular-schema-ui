<?php

namespace XaviWorks\ModularSchemaUi\Tables;

use XaviWorks\ModularSchemaUi\Contracts\Action as ActionContract;

final class Action implements ActionContract
{
    private string $label;

    private string $httpMethod = 'GET';

    private ?string $url = null;

    private ?string $confirmation = null;

    public function __construct(private readonly string $name)
    {
        $this->label = str($name)->headline()->toString();
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function httpMethod(string $method): self
    {
        $this->httpMethod = strtoupper($method);

        return $this;
    }

    public function url(string $template): self
    {
        $this->url = $template;

        return $this;
    }

    public function confirm(string $message): self
    {
        $this->confirmation = $message;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function labelText(): string
    {
        return $this->label;
    }

    public function method(): string
    {
        return $this->httpMethod;
    }

    public function urlTemplate(): ?string
    {
        return $this->url;
    }

    public function confirmationMessage(): ?string
    {
        return $this->confirmation;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'label' => $this->labelText(),
            'method' => $this->method(),
            'url' => $this->urlTemplate(),
            'confirm' => $this->confirmationMessage(),
        ];
    }
}
