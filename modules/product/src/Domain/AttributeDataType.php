<?php

namespace Modules\Product\Domain;

/** Data types supported by reusable catalog specifications. */
enum AttributeDataType: string
{
    case Text = 'text';
    case Number = 'number';
    case Boolean = 'boolean';
    case Enum = 'enum';
}
