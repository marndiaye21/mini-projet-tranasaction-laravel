<?php

namespace App\Enum;

enum TransactionType : int {
    case Withdraw = 0;
    case Deposit = 1;
    case Transfer = 2;
}
