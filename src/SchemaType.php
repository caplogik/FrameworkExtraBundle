<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle;

final class SchemaType
{
    const BOOLEAN = 'boolean';
    const NUMBER = 'number';
    const STRING = 'string';
    const DATE = 'date';
    const ARRAY = 'array';
    const OBJECT = 'object';

    private function __construct() {}
}
