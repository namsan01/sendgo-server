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
            $table->id(); // 자동 증가하는 기본 키
            $table->string('name'); // 사용자 이름
            $table->string('email')->unique(); // 이메일 (유일한 값)
            $table->string('password'); // 비밀번호
            $table->string('phone')->nullable(); // 전화번호 (선택적)
            $table->boolean('is_admin')->default(false); // 관리자 여부 (기본값 false)
            $table->timestamp('email_verified_at')->nullable(); // 이메일 확인 시간 (선택적)
            $table->rememberToken(); // 비밀번호 재설정을 위한 토큰
            $table->timestamps(); // created_at 및 updated_at 타임스탬프

            // 인덱스와 외래 키를 추가할 수 있는 곳입니다.
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
