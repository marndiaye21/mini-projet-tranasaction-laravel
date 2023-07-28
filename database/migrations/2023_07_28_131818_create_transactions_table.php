<?php

use App\Models\Client;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('amount');
            /**
             * @var Enum
             * 0 => Retrait
             * 1 => Dépôt
             * 2 => Transfert
             */
            $table->enum('type', [0, 1, 2]);
            $table->string('phone_receiver')->nullable();
            $table->foreignIdFor(Client::class)->constrained()->cascadeOnDelete();
            $table->dateTime("datetime")->default(now());
            $table->string("code", 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
