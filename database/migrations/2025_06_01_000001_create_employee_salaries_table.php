<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('department_id');
            $table->decimal('base_salary', 10, 2);
            $table->string('currency', 3)->default('MAD'); // Changé de EUR à MAD
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('created_by')->references('id')->on('users');
            
            $table->index(['user_id', 'effective_from']);
            $table->index('effective_from');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_salaries');
    }
};