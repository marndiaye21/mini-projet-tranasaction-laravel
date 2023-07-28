<?php

namespace App\Enum;

enum AccountType : int{
    case OrangeMoney = 1;
    case Wave = 2;
    case Wari = 3;
    case CompteBancaire = 4;
}
