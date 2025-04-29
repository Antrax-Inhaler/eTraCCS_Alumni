<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEntityTypeToUserViewHistoriesTable extends Migration
{
    public function up()
    {
        Schema::table('user_view_histories', function (Blueprint $table) {
            $table->string('entity_type')->after('entity_id'); // Add entity_type column
        });
    }

    public function down()
    {
        Schema::table('user_view_histories', function (Blueprint $table) {
            $table->dropColumn('entity_type'); // Drop entity_type column if rolling back
        });
    }
}