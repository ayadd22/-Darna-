<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            $table->foreignId('office_id')
                ->constrained('offices')
                ->restrictOnDelete();

            $table->foreignId('owner_id')
                ->constrained('owners')
                ->restrictOnDelete();

            $table->foreignId('governorate_id')
                ->constrained('governorates')
                ->restrictOnDelete();

            $table->string('title');
            $table->text('description');

            $table->string('property_type');
            $table->string('listing_type');
            $table->string('status')->default('pending_approval');

            $table->decimal('price', 15, 2);
            $table->string('currency', 3);

            $table->decimal('area', 10, 2);
            $table->string('address');

            $table->string('contact_phone')->nullable();

            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->text('owner_notes')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index('property_type');
            $table->index('listing_type');
            $table->index('status');
            $table->index('price');

            $table->index([
                'governorate_id',
                'status',
                'listing_type',
                'property_type',
            ], 'properties_public_filters_index');

            $table->index([
                'office_id',
                'created_at',
                'id',
            ], 'properties_office_listing_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
