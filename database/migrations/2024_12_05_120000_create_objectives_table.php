<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['monthly', 'quarterly', 'annual', 'custom'])->default('monthly');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled', 'overdue'])->default('assigned');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('created_by'); // Direction user ID
            $table->date('start_date');
            $table->date('due_date');
            $table->date('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->json('metrics')->nullable(); // KPIs, target numbers, etc.
            $table->text('notes')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['department_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index(['created_by', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('objectives');
    }
};