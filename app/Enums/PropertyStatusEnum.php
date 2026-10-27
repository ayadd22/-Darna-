<?php

namespace App\Enums;

enum PropertyStatusEnum: string
{
    case PENDING_APPROVAL = 'pending_approval';
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case SOLD = 'sold';
}
