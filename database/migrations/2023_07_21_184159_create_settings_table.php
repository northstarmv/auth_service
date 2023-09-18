<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 200);
            $table->longText('value');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });


        DB::table('settings')->insert([
            'key' => 'breadcrumb_section_01',
            'value' => '',
        ]);

        DB::table('settings')->insert([
            'key' => 'breadcrumb_section_02',
            'value' => '',
        ]);

        DB::table('settings')->insert([
            'key' => 'breadcrumb_section_03',
            'value' => '',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
