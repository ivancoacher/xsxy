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
            $table->increments('id');
            $table->string('name');
            $table->string('nickname');
            $table->string('email')->unique();
            $table->string('gender')->default(0)->comment("0,未知 1：男 2：女");
            $table->string('openid')->unique();
            $table->string('sign', '1000')->default('')->comment('个人签名');
            $table->decimal('level', '10', 1)->default('0')->comment('用户等级');

            $table->string('child_name', '20')->default('')->comment('孩子姓名');
            $table->unsignedInteger('child_gender')->default(0)->comment("0,未知 1：男 2：女");
            $table->string('child_grade', '20')->default('')->comment('孩子年级');
            $table->string('child_class', '20')->default('')->comment('孩子班级');
            $table->string('child_school', '20')->default('')->comment('孩子学校');

            $table->string('user_type')->comment('用户类型：normal/vip');
            $table->string('password');
            $table->string('avatar_url', '200')->comment('用户头像');
            $table->rememberToken();
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
