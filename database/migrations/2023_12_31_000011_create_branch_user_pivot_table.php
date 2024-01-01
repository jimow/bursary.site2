<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchUserPivotTable extends Migration
{
    public function up()
    {
        Schema::create('branch_user', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id', 'branch_id_fk_9352862')->references('id')->on('branches')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'user_id_fk_9352862')->references('id')->on('users')->onDelete('cascade');
        });
    }
}
