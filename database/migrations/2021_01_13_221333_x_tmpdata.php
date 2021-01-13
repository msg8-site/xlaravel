<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class XTmpdata extends Migration
{
    //数据表名称
    private $tablename = 'x_tmpdata';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('临时存储表-自增主键ID');
                $table->string('tmpidname', 64)->default('')->comment('唯一标识id名称')->unique('tmpidname');
                $table->text('tmpdata')->comment('临时存储数据')->nullable();
                $table->dateTime('create_datetime')->default('2000-01-01 00:00:00')->comment('创建时间');
                $table->dateTime('update_datetime')->default('2000-01-01 00:00:00')->comment('更新时间');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (true === COMM_DBLOCK) {
            Schema::dropIfExists($this->tablename);
        }
    }
}
