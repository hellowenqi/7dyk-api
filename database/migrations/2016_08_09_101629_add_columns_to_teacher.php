<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToTeacher extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('teacher', function(Blueprint $table)
		{
            $table->integer('listen_virtual');
            $table->integer('like_virtual');
            $table->integer('answernum_virtual');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('teacher', function(Blueprint $table)
		{
			//
		});
	}

}
