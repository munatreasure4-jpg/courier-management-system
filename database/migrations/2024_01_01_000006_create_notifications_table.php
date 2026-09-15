<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->onDelete('cascade');
            $table->enum('type', [
                'shipment_created',
                'shipment_picked',
                'shipment_in_transit',
                'out_for_delivery',
                'delivered',
                'delivery_failed',
                'payment_received',
                'invoice_generated'
            ]);
            $table->enum('channel', ['email', 'sms', 'in_app', 'push'])->default('in_app');
            $table->string('subject');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('shipment_id');
            $table->index('is_read');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
