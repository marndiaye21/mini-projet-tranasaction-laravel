<?php

namespace App\Http\Controllers;

use App\Enum\TransactionType;
use App\Models\Account;
use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function store(Request $request) {
        $transaction = $request->all();

        if ($transaction['type'] === TransactionType::Deposit->value) {
            $this->depositMoney($transaction);
        }
    }

    public function depositMoney(array $transaction) : Transaction | null
    {
        if (!$this->validClient($transaction['owner'])) {
            throw new \Exception("Impossible d'éffectuer le dépôt le numéro indiqué n'est pas valide");
        }

        if (!$this->hasAccount($transaction['owner'])) {

            $receiverPhone = null;

            if ($this->hasAccount($transaction['receiver'])) {
    
                $receiverPhone = $transaction['receiver'];
    
                $receiverAccount = Account::where('account_number', 'like', "___%$receiverPhone%")->first();
                $client = Client::where('phone', $transaction['owner'])->first();
                
                $receiverAccount->balance += $transaction['amount'];
                $receiverAccount->update();
                
                return Transaction::create([
                    "amount" => $transaction['amount'],
                    "type" => $transaction['type'] . '',
                    "client_id" => $client->id,
                    "phone_receiver" => $receiverPhone,
                    "account_type" => trim(explode("=", $receiverAccount->account_number)[1], ' ')
                ]);
            }
        }

        return null;
    }

    public function hasAccount(string $phone) : bool
    {
        $account = Account::where('account_number', 'like', "___%$phone%")->first();

        if ($account) {
            return true;
        }

        return false;
    }

    public function validClient(string $phone): bool
    {
        $client = Client::where("phone", $phone)->first();
        
        if ($client) {
            return true;
        }

        return false;
    }

    public function getAccountType(string $accountNumber) : string
    {
        if (strpos($accountNumber, "WV")) {
            return "Wave = WV";
        } elseif(strpos($accountNumber, "OM")) {
            return "Orange Money = OM";
        } elseif(strpos($accountNumber, "WR")) {
            return "Wari = WR";
        } elseif(strpos($accountNumber, "CB")) {
            return "Compte Bancaire = CB";
        }
    }
}
