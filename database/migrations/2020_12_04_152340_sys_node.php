<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SysNode extends Migration
{
    //数据表名称
    private $tablename = 'sys_node';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('节点表-自增主键ID');
                $table->tinyInteger('type')->default('0')->comment('类型，1显示，2隐藏');
                $table->string('classname', 128)->default('')->comment('类名');
                $table->string('functionname', 256)->default('')->comment('函数名');
                $table->string('routepath', 256)->default('')->comment('访问路径uri');
                $table->string('nodename', 128)->default('')->comment('说明名称');
                $table->dateTime('create_datetime')->default('2000-01-01 00:00:00')->comment('创建时间');
                $table->dateTime('update_datetime')->default('2000-01-01 00:00:00')->comment('更新时间');
                $table->string('backup1', 64)->default('')->comment('备用字段1');
                $table->string('backup2', 64)->default('')->comment('备用字段2');
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
