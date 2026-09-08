<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class InvoiceNotEditableException extends ConflictHttpException
{
    public function __construct()
    {
        parent::__construct('This invoice can no longer be edited because it is no longer pending.');
    }
}
