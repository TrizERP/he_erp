<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('placement_notification_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_institute_id')->index();
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->string('channel', 10); // email|whatsapp|inapp
            $table->string('event_key', 50)->index();
            $table->string('recipient', 150)->nullable(); // masked, never the raw address / number
            $table->string('subject', 255)->nullable();
            $table->text('body');
            $table->string('status', 10)->default('queued'); // queued|sent|failed|skipped
            $table->text('error')->nullable();
            $table->string('related_type', 30)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->index(['sub_institute_id', 'channel', 'status'], 'placement_notif_state');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('placement_notification_log');
    }
};
