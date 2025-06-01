<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evaluation_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('summary'); // Résumé du chef
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('created_by'); // Chef de département
            $table->date('evaluation_period_start');
            $table->date('evaluation_period_end');
            $table->json('attendance_data'); // Données de pointage
            $table->json('tasks_data'); // Données des tâches
            $table->json('requests_data'); // Données des demandes
            $table->json('employees_performance'); // Performance par employé
            $table->text('recommendations')->nullable(); // Recommandations
            $table->string('status')->default('draft'); // draft, sent, reviewed
            $table->timestamp('sent_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable(); // Admin RH
            $table->timestamp('reviewed_at')->nullable();
            $table->text('hr_comments')->nullable(); // Commentaires de l'admin RH
            $table->timestamps();
            
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('reviewed_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_reports');
    }
};