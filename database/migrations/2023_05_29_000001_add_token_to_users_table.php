<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function getConnection()
    {
        return config('database.portal');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'oauth_provider')
                && !Schema::hasColumn('users', 'oauth_provider_id')
            ) {
                $table->string('oauth_provider', 100);
                $table->string('oauth_provider_id', 100);
                $table->string('token',2000)->nullable();
                $table->string('refresh_token',2000)->nullable();
                $table->dateTime('expired_at')->nullable();
                $table->string('scopes')->nullable();
                $table->index(['oauth_provider', 'oauth_provider_id'], 'i_oauth_providers');
            }

            if (!Schema::hasColumn('users', 'orggid'))
                $table->uuid('orggid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'oauth_provider')
            || Schema::hasColumn('users', 'oauth_provider_id')
        )
            Schema::dropColumns('users', ['oauth_provider', 'oauth_provider_id']);
        if (Schema::hasColumn('users', 'orggid'))
            Schema::dropColumns('users', ['orggid']);
    }
};
