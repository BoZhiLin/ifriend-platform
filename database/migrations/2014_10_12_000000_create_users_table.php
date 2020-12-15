<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('姓名');
            $table->string('gender')->comment('性別');
            $table->string('nickname')->comment('暱稱');
            $table->string('email')->unique()->comment('信箱');
            $table->string('password')->comment('密碼');
            $table->string('avatar')->nullable()->comment('頭像');
            $table->string('phone')->nullable()->comment('電話');
            $table->text('introduction')->nullable()->comment('自我介紹');
            $table->string('blood')->nullable()->comment('血型');
            $table->string('constellation')->nullable()->comment('星座');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_verified')->default(false)->comment('是否驗證');
            $table->timestamp('expired_at')->nullable()->comment('會員有效日');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
