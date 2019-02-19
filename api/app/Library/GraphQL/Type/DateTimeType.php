<?php

declare(strict_types = 1);

namespace App\Library\GraphQL\Type;

use Folklore\GraphQL\Support\Contracts\TypeConvertible;
use GraphQL\Type\Definition\ScalarType;
use DateTime;

final class DateTimeType extends ScalarType implements TypeConvertible
{
    /**
     * @var string
     */
    public $name = 'DateTimeType';

    /**
     * @var string
     */
    public $description = 'DateTime scalar type';

    /**
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value)//: DateTime <- Retorna null quando fixado
    {
        return $value;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function parseValue($value) : string
    {
        return $value;
    }

    /**
     * @param $ast
     * @return null|string
     */
    public function parseLiteral($ast) : ?string
    {
        return null;
    }

    /**
     * @return DateTimeType
     */
    public function toType() : DateTimeType
    {
        return new static();
    }
}
