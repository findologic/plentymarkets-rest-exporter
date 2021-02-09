<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

class PluginConfiguration extends Entity
{
    /** @var string|null */
    private $id;

    /** @var string|null */
    private $key;

    /** @var string|null */
    private $value;

    /** @var int|null */
    private $plugin_id;

    /** @var string|null */
    private $label;

    /** @var string|null */
    private $type;

    /** @var array|mixed */
    private $possibleValues;

    /** @var string|null */
    private $default;

    /** @var string|null */
    private $tab;

    /** @var bool|null */
    private $scss;

    public function __construct(array $data)
    {
        // Undocumented - the properties may not match the received data exactly
        $this->id = $this->getStringProperty('id', $data);
        $this->key = $this->getStringProperty('key', $data);
        $this->value = $this->getStringProperty('value', $data);
        $this->plugin_id = $this->getIntProperty('plugin_id', $data);
        $this->label = $this->getStringProperty('label', $data);
        $this->type = $this->getStringProperty('type', $data);
        $this->possibleValues = $data['possibleValues'] ?? [];
        $this->default = $this->getStringProperty('default', $data);
        $this->tab = $this->getStringProperty('tab', $data);
        $this->scss = $this->getBoolProperty('scss', $data);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $this->value,
            'plugin_id' => $this->plugin_id,
            'label' => $this->label,
            'type' => $this->type,
            'possibleValues' => $this->possibleValues,
            'default' => $this->default,
            'tab' => $this->tab,
            'scss' => $this->scss
        ];
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getPluginId(): ?int
    {
        return $this->plugin_id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getPossibleValues()
    {
        return $this->possibleValues;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function getTab(): ?string
    {
        return $this->tab;
    }

    public function getScss(): ?bool
    {
        return $this->scss;
    }
}
