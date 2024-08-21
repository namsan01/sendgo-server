<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id(); // 자동 증가하는 기본 키
            $table->string('title'); // 제목
            $table->text('content'); // 내용
            $table->string('status'); // 상태
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 사용자 ID (외래 키)
            $table->timestamps(); // created_at 및 updated_at 타임스탬프

            // 인덱스와 외래 키를 추가할 수 있습니다.
            $table->index('status'); // 상태에 인덱스 추가
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contents');
    }
}
