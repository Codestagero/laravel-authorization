<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->foreignUuid('role_id')
                ->references('id')
                ->on('roles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('permission', 64);
            $table->timestamps();

            $table->primary(['permission', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
