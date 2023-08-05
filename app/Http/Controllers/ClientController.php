<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    private $authorizedRelations = ["transactions", "accounts"];
    private $phonePattern = "/^(77|76|78|70|75)\d{7}$/";
    private $accountPattern = "/^(OM|WV|CB)_(77|76|78|70|75)\d{7}$/";

    public function index(Request $request)
    {
        if ($request->join && in_array($request->join, $this->authorizedRelations)) {
            return Client::with($request->join)->get();
        }

        return Client::all();
    }

    public function show(Request $request, string $id)
    {
        if ($request->join && in_array($request->join, $this->authorizedRelations)) {
            return Client::where("id", $id)->with($request->join)->first();
        }

        return Client::where("id", $id)->first();
    }

    public function search(string $searchKey)
    {
        if (preg_match("/^[A-Z]/", $searchKey)) {
            $accounts = Account::where("account_number", "like", "$searchKey%")->with("client")->get();
            if ($accounts->isEmpty()) {
                if (!preg_match($this->accountPattern, $searchKey)) {
                    return [
                        "message" => "Numéro de compte invalide !!!",
                        "data" => []
                    ];
                }
                return [
                    "message" => "Ce numéro ne correspond à aucun compte !!!",
                    "data" => []
                ];
            }

            return [
                "message" => "Comptes reçus avec succès",
                "data" => $accounts,
            ];
        }

        if (preg_match("/^\d/", $searchKey)) {
            $clients = Client::where("phone", "like", "$searchKey%")->with("transactions")->get();
            if ($clients->isEmpty()) {
                if (!preg_match($this->phonePattern, $searchKey)) {
                    return [
                        "message" => "Numéro de téléphone invalide !!!",
                        "data" => []
                    ];
                }
                return [
                    "message" => "Ce numéro de téléphone ne correspond à aucun client",
                    "data" => []
                ];
            }
            
            return [
                "message" => "Clients reçus avec succès",
                "data" => $clients,
            ];
        }
        
        return [
            "message" => "Numéro de téléphone ou compte invalide !!!",
            "data" => []
        ];
    }

    public function store(Request $request)
    {
        return $request->all();
    }
}
