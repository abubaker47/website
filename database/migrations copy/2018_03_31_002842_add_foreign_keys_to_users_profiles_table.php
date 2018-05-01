<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUsersProfilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users_profiles', function(Blueprint $table)
		{
			$table->foreign('uprofileid', 'users_profiles_ibfk_1')->references('userid')->on('users')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users_profiles', function(Blueprint $table)
		{
			$table->dropForeign('users_profiles_ibfk_1');
		});
	}

}