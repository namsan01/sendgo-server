<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveResponseFromPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('response');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->text('response')->nullable();
        });
    }
}
