<?php

namespace App\Enums;

/**
 * EP03-07: visibilidad de un recurso personal dentro de un hogar (ADR-0002).
 */
enum Visibility: string
{
    /** Solo el titular lo ve. */
    case Private = 'private';

    /** Visible para los roles activos del hogar. */
    case Shared = 'shared';
}
