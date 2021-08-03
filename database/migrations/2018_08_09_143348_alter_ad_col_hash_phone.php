<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;
class AlterAdColHashPhone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         DB::statement('alter TABLE users add phone_hash varchar(255) after country_code');

         $all = User::all();
         foreach ($all as $key => $value) {
             $user = User::where('user_id',$value->user_id)->update(['phone_hash'=> hash('sha256',$value->country_code.$value->phone)]);
         }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
