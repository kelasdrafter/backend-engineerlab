<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCertificatesMakeBatchNullableAndAddCourseId extends Migration
{
    public function up()
    {
        // butuh doctrine/dbal untuk change()
        Schema::table('certificates', function (Blueprint $table) {
            // ubah batch_id jadi nullable
            $table->unsignedBigInteger('batch_id')->nullable()->change();

            // tambahkan course_id nullable
            $table->unsignedBigInteger('course_id')->nullable()->after('batch_id');
            $table->foreign('course_id')->references('id')->on('courses');
        });
    }

    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            // rollback FK & kolom
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');

            // kembalikan batch_id NOT NULL jika perlu
            $table->unsignedBigInteger('batch_id')->nullable(false)->change();
        });
    }
}
