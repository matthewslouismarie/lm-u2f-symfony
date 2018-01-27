<?php

namespace App\DataStructure;

use App\Model\TransitingData;
use InvalidArgumentException;
use UnexpectedValueException;

class TransitingDataManager
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

        return new TransitingDataManager($newList);
    }

    public function filterBy(string $attribute, string $attributeValue): self
    {
        return new TransitingDataManager(
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
        return new TransitingDataManager(
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

    public function isEmpty(): bool
    {
        return 0 === count($this->list);
    }
}
