<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        $defaultProfileId = DB::table('profiles')->insertGetId([
            'name' => 'Default',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('time_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('profile_id')->nullable()->after('id')->index();
        });

        Schema::table('journals', function (Blueprint $table) {
            $table->unsignedBigInteger('profile_id')->nullable()->after('id')->index();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedBigInteger('profile_id')->nullable()->after('id')->index();
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('profile_id')->nullable()->after('id')->index();
        });

        DB::table('time_entries')->whereNull('profile_id')->update(['profile_id' => $defaultProfileId]);
        DB::table('journals')->whereNull('profile_id')->update(['profile_id' => $defaultProfileId]);
        DB::table('settings')->whereNull('profile_id')->update(['profile_id' => $defaultProfileId]);
        DB::table('attendance_logs')->whereNull('profile_id')->update(['profile_id' => $defaultProfileId]);

        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropUnique('time_entries_date_unique');
            $table->unique(['profile_id', 'date']);
        });

        Schema::table('journals', function (Blueprint $table) {
            $table->dropUnique('journals_date_unique');
            $table->unique(['profile_id', 'date']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_key_unique');
            $table->unique(['profile_id', 'key']);
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropUnique('attendance_logs_date_unique');
            $table->unique(['profile_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropUnique('attendance_logs_profile_id_date_unique');
            $table->unique('date');
            $table->dropColumn('profile_id');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_profile_id_key_unique');
            $table->unique('key');
            $table->dropColumn('profile_id');
        });

        Schema::table('journals', function (Blueprint $table) {
            $table->dropUnique('journals_profile_id_date_unique');
            $table->unique('date');
            $table->dropColumn('profile_id');
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropUnique('time_entries_profile_id_date_unique');
            $table->unique('date');
            $table->dropColumn('profile_id');
        });

        Schema::dropIfExists('profiles');
    }
};
