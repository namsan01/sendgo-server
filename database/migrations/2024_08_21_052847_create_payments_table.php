<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // 자동 증가하는 기본 키
            $table->string('method'); // 결제 방법
            $table->decimal('amount', 10, 2); // 결제 금액
            $table->string('orderId'); // 주문 ID
            $table->string('status'); // 결제 상태
            $table->text('errorMessage')->nullable(); // 오류 메시지 (선택적)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 사용자 ID (외래 키)
            $table->timestamps(); // created_at 및 updated_at 타임스탬프

            // 인덱스와 외래 키를 추가할 수 있습니다.
            $table->index('orderId'); // 주문 ID에 인덱스 추가
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
