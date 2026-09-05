<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The agreements admin list orders by created_at and filters by category_id and
 * the two party ids, but the table had only its PRIMARY key. On 5k+ rows that
 * meant a full scan + filesort on every DataTables request. These indexes back
 * the sort and the filters.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->index('created_at', 'agreements_created_at_index');
            $table->index('category_id', 'agreements_category_id_index');
            $table->index('party_1_id', 'agreements_party_1_id_index');
            $table->index('party_2_id', 'agreements_party_2_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropIndex('agreements_created_at_index');
            $table->dropIndex('agreements_category_id_index');
            $table->dropIndex('agreements_party_1_id_index');
            $table->dropIndex('agreements_party_2_id_index');
        });
    }
};
