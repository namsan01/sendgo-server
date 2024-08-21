<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id(); // 자동 증가하는 기본 키
            $table->foreignId('content_id')->constrained()->onDelete('cascade'); // 콘텐츠 ID (외래 키)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 사용자 ID (외래 키)
            $table->text('content'); // 댓글 내용
            $table->boolean('is_admin')->default(false); // 관리자 여부 (기본값 false)
            $table->timestamps(); // created_at 및 updated_at 타임스탬프

            // 인덱스와 외래 키를 추가할 수 있습니다.
            $table->index('content_id'); // 콘텐츠 ID에 인덱스 추가
            $table->index('user_id'); // 사용자 ID에 인덱스 추가
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
