<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Enum\TransactionType;

class TransactionController extends Controller
{
    private const DEPOSIT_MIN_AMOUNT = 500;
    
    private $receiverAccount;

    public function store(Request $request)
    {
        $transaction = $request->all();

        if ($transaction['type'] === TransactionType::Deposit->value) {
            return $this->depositMoney($transaction);
        } elseif ($transaction['type'] === TransactionType::Withdraw->value) {
            return $this->withdrawMoney($transaction);
        }
    }

    public function depositMoney(array $transaction): Transaction
    {
        if (!$this->validClient($transaction['owner'])) {
            throw new \Exception("Impossible d'éffectuer le dépôt ! le numéro client indiqué n'est pas valide");
        }

        if (!$this->validClient($transaction['receiver'])) {
            throw new \Exception("Impossible d'éffectuer le dépôt ! le numéro du destinataire indiqué n'est pas valide");
        }

        if ($transaction['amount'] < self::DEPOSIT_MIN_AMOUNT) {
            throw new \Exception("Impossible d'éffectuer le dépôt ! le montant doit être au minimum " . self::DEPOSIT_MIN_AMOUNT);
        }

        $client = Client::where('phone', $transaction['owner'])->first();

        $receiverPhone = $transaction['receiver'];

        if ($this->hasAccount($receiverPhone)) {
            $this->updateReceiverAccount($receiverPhone, $transaction['amount']);
            $accountType = trim(explode("=", $this->receiverAccount->account_type)[1], ' ');
        } else {
            $accountType = 'WR';
            $code = Str::random(15);
        }

        return Transaction::create([
            "amount" => $transaction['amount'],
            "type" => $transaction['type'] . '',
            "client_id" => $client->id,
            "phone_receiver" => $receiverPhone,
            "account_type" => $accountType,
            'code' => $code ?? null,
        ]);
    }

    public function withdrawMoney(array $transaction): Transaction
    {
        $senderPhone = $transaction['owner'];
        $amount = $transaction['amount'];

        if (!$this->validClient($transaction['owner'])) {
            throw new \Exception("Impossible d'effectuer le retrait ! Le numéro client indiqué n'est pas valide");
        }

        $client = Client::where('phone', $senderPhone)->first();

        if ($this->hasAccount($senderPhone)) {
            if ($this->hasEnoughBalance($senderPhone, $amount)) {
                $this->updateSenderAccount($senderPhone, $amount);
                return Transaction::create([
                    "amount" => $amount,
                    "type" => $transaction['type'] . '',
                    "client_id" => $client->id,
                    "phone_receiver" => null,
                    "account_type" => 'WR',
                ]);
            } else {
                throw new \Exception("Impossible d'effectuer le retrait ! Le solde du compte est insuffisant");
            }
        } else {
            if ($transaction['code']) {
                $matchingTransaction = Transaction::where([
                    'code' => $transaction['code'],
                    'phone_receiver' => $senderPhone
                ])->first();

                if ($matchingTransaction) {
                    $matchingTransaction->delete();
                    return Transaction::create([
                        "amount" => $amount,
                        "type" => $transaction['type'] . '',
                        "client_id" => $client->id,
                        "account_type" => 'WR',
                    ]);
                } else {
                    throw new \Exception("Le code de retrait n'est pas valide ou a déjà été utilisé.");
                }
            } else {
                throw new \Exception("Impossible d'effectuer le retrait ! Le client n'a pas de compte et le code de retrait est manquant.");
            }
        }
    }

    private function updateReceiverAccount(string $receiverPhone, float $amount): void
    {
        $this->receiverAccount = Account::where('account_number', 'like', "___%$receiverPhone%")->first();
        $this->receiverAccount->balance += $amount;
        $this->receiverAccount->update();
    }

    private function updateSenderAccount(string $senderPhone, float $amount): void
    {
        $senderAccount = Account::where('account_number', 'like', "___%$senderPhone%")->first();
        $senderAccount->balance -= $amount;
        $senderAccount->update();
    }

    private function hasEnoughBalance(string $senderPhone, float $amount): bool
    {
        $senderAccount = Account::where('account_number', 'like', "___%$senderPhone%")->first();
        return $senderAccount->balance >= $amount;
    }

    public function hasAccount(string $phone): bool
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

    public function getAccountType(string $accountNumber): string
    {
        if (strpos($accountNumber, "WV")) {
            return "Wave = WV";
        } elseif (strpos($accountNumber, "OM")) {
            return "Orange Money = OM";
        } elseif (strpos($accountNumber, "WR")) {
            return "Wari = WR";
        } elseif (strpos($accountNumber, "CB")) {
            return "Compte Bancaire = CB";
        }
    }
}
