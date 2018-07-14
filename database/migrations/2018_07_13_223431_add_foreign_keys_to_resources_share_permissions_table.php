<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToResourcesSharePermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('resources_share_permissions', function(Blueprint $table)
		{
			$table->foreign('resourceid', 'resources_share_permissions_ibfk_1')->references('resourceid')->on('resources')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('resources_share_permissions', function(Blueprint $table)
		{
			$table->dropForeign('resources_share_permissions_ibfk_1');
		});
	}

}