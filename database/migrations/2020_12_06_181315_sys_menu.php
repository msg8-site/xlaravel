<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SysMenu extends Migration
{
    //数据表名称
    private $tablename = 'sys_menu';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('菜单表-自增主键ID');
                $table->unsignedBigInteger('fid')->default('0')->comment('父级id，没有父级是为0');
                $table->string('menuname', 64)->default('')->comment('菜单名称');
                $table->string('menupath', 256)->default('')->comment('菜单url路径');
                $table->bigInteger('orderbyid')->default('100')->comment('排序规则，desc,默认100');
                $table->dateTime('create_datetime')->default('2000-01-01 00:00:00')->comment('创建时间');
                $table->dateTime('update_datetime')->default('2000-01-01 00:00:00')->comment('更新时间');
                $table->string('backup1', 64)->default('')->comment('备用字段1');
                $table->unsignedBigInteger('uplockid')->default('1')->comment('更新冲突锁id');
            });
            //初始化插入root数据
            DB::table($this->tablename)->insert([
                ['id' => '1', 'fid' => '0', 'menuname' => '系统管理', 'menupath' => '', 'orderbyid' => '999', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['id' => '2', 'fid' => '1', 'menuname' => '用户管理', 'menupath' => 'user_index', 'orderbyid' => '800', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['id' => '3', 'fid' => '1', 'menuname' => '节点管理', 'menupath' => 'node_index', 'orderbyid' => '790', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['id' => '4', 'fid' => '1', 'menuname' => '菜单管理', 'menupath' => 'menu_index', 'orderbyid' => '790', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['id' => '5', 'fid' => '1', 'menuname' => '角色管理', 'menupath' => 'role_index', 'orderbyid' => '780', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['id' => '6', 'fid' => '1', 'menuname' => '登陆日志', 'menupath' => 'loguserlogin_index', 'orderbyid' => '700', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['id' => '7', 'fid' => '1', 'menuname' => '动态码日志', 'menupath' => 'logcodecheck_index', 'orderbyid' => '700', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['id' => '8', 'fid' => '1', 'menuname' => '数据操作日志', 'menupath' => 'logdatabasecudn_index', 'orderbyid' => '700', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['id' => '9', 'fid' => '1', 'menuname' => '开发参考样例', 'menupath' => 'example_index', 'orderbyid' => '600', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
            ]);
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
