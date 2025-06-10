<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->dateTime('meeting_date')->nullable();
            $table->string('meeting_location')->nullable();
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['status', 'created_at']);
            $table->index('priority');
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
    }
};