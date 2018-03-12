<?php

namespace App\DataStructure;

use App\Model\TransitingData;
use InvalidArgumentException;
use Serializable;
use UnexpectedValueException;

class TransitingDataManager implements Serializable
{
    private $list;

    public function __construct(array $list = [])
    {
        foreach ($list as $value) {
            if (!is_a($value, TransitingData::class)) {
                throw new InvalidArgumentException();
            }
        }
        $this->list = $list;
    }

    public function add(TransitingData $newValue): self
    {
        $newList = $this->list;
        $newList[] = $newValue;

        return new self($newList);
    }

    public function filterBy(string $attribute, string $attributeValue): self
    {
        return new self(
            array_filter(
                $this->list,
                function ($listValue) use ($attribute, $attributeValue) {
                    return !$listValue->isAlike($attribute, $attributeValue);
                }
            )
        );
    }

    public function getBy(string $attribute, string $attributeValue): self
    {
        return new self(
            array_filter(
                $this->list,
                function ($listValue) use ($attribute, $attributeValue) {
                    return $listValue->isAlike($attribute, $attributeValue);
                }
            )
        );
    }

    public function getOnlyValue(): TransitingData
    {
        $list = $this->list;
        if (1 !== count($list)) {
            throw new UnexpectedValueException();
        } else {
            return reset($list);
        }
    }

    public function getSize(): int
    {
        return count($this->list);
    }

    public function isEmpty(): bool
    {
        return 0 === count($this->list);
    }

    public function replaceByKey(TransitingData $newValue): self
    {
        return $this
            ->filterBy('key', $newValue->getKey())
            ->add($newValue)
        ;
    }

    public function toArray(): array
    {
        $valueList = [];
        foreach ($this->list as $value) {
            $valueList[] = $value->getValue();
        }

        return $valueList;
    }

    public function serialize(): string
    {
        return serialize($this->list);
    }

    public function unserialize($serialized): void
    {
        $this->list = unserialize($serialized);
    }
}
