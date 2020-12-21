<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LogDatabascudn extends Migration
{
    //数据表名称
    private $tablename = 'log_databasecudn';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('数据操作记录表-自增主键ID');
                $table->string('type', 16)->default('')->comment('日志类型，c添加，u修改，d删除，n其他');
                $table->string('username', 64)->default('')->comment('操作用户名');
                $table->string('userip', 64)->default('')->comment('操作对应IP');
                $table->string('moreclientkey', 64)->default('')->comment('用户客户端标识');
                $table->string('cudnclassname', 64)->default('')->comment('操作对应类名');
                $table->string('cudnfunctionname', 128)->default('')->comment('操作对应函数名');
                $table->string('tablename', 128)->default('')->comment('操作数据表名称');
                $table->unsignedBigInteger('tableid')->default('0')->comment('操作数据表对应主键ID')->index('tableid');  //不为0代表单挑精确记录
                $table->mediumText('cudndata')->comment('操作数据记录');
                $table->dateTime('create_datetime')->default('2000-01-01 00:00:00')->comment('数据创建时间')->index('create_datetime');
                // $table->dateTime('update_datetime')->default('2000-01-01 00:00:00')->comment('更新时间');


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
