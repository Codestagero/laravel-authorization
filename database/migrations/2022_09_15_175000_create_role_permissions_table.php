<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
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

    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
};
