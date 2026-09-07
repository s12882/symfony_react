<?php

namespace App\Enum;

enum InvoiceStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
