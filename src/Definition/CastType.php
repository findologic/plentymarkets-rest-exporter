<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Definition;

final class CastType
{
    public const EMPTY = 'empty';
    public const TEXT = 'text';
    public const INT = 'int';
    public const FLOAT = 'float';
    public const SHORT_TEXT = 'shortText';
    public const LONG_TEXT = 'longText';
    public const SELECTION = 'selection';
    public const MULTI_SELECTION = 'multiSelection';
}
