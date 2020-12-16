<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SysRole extends Migration
{
    //数据表名称
    private $tablename = 'sys_role';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('角色表-自增主键ID');
                $table->string('nodeidstr', 1024)->default('')->comment('节点ID存储，英文逗号分隔');
                $table->string('menuidstr', 1024)->default('')->comment('菜单ID存储，英文逗号分隔');
                $table->string('rolename', 64)->default('')->comment('角色名称');
                $table->dateTime('create_datetime')->default('2000-01-01 00:00:00')->comment('创建时间');
                $table->dateTime('update_datetime')->default('2000-01-01 00:00:00')->comment('更新时间');
                $table->string('backup1', 64)->default('')->comment('备用字段1');
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
        Schema::dropIfExists($this->tablename);
    }
}
