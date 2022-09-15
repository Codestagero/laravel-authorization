<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user_roles', function (Blueprint $table): void {
            $table->morphs('user');
            $table->foreignUuid('role_id')
                ->references('id')
                ->on('roles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['user_id', 'role_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_roles');
    }
};
