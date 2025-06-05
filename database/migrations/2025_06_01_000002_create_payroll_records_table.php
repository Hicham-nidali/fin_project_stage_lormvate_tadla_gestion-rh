<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('evaluation_report_id')->nullable();
            $table->string('period');
            $table->date('period_start');
            $table->date('period_end');
            
            $table->decimal('base_salary', 10, 2);
            $table->decimal('adjustment_percentage', 5, 2)->default(0);
            $table->decimal('adjustment_amount', 10, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_rate', 8, 2)->default(0);
            $table->decimal('overtime_amount', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            
            $table->json('performance_data')->nullable();
            $table->json('adjustment_details')->nullable();
            $table->text('notes')->nullable();
            
            $table->string('status')->default('draft');
            $table->timestamp('calculated_at')->nullable();
            $table->unsignedBigInteger('calculated_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('evaluation_report_id')->references('id')->on('evaluation_reports')->onDelete('set null');
            $table->foreign('calculated_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            
            $table->unique(['user_id', 'period']);
            $table->index(['period', 'status']);
            $table->index('period_start');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_records');
    }
};