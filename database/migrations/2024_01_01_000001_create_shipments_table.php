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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('receiver_name');
            $table->string('receiver_email');
            $table->string('receiver_phone');
            $table->string('origin_address');
            $table->string('origin_city');
            $table->string('origin_state');
            $table->string('origin_postal_code');
            $table->string('destination_address');
            $table->string('destination_city');
            $table->string('destination_state');
            $table->string('destination_postal_code');
            $table->decimal('weight', 10, 2);
            $table->string('dimensions')->nullable();
            $table->text('contents_description')->nullable();
            $table->enum('status', ['pending', 'picked', 'in_transit', 'out_for_delivery', 'delivered', 'cancelled', 'returned'])->default('pending');
            $table->decimal('shipping_cost', 10, 2);
            $table->timestamp('pickup_date')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('tracking_number');
            $table->index('sender_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
