<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TplExample extends Migration
{
    //数据表名称
    private $tablename = 'tpl_example';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('参考样例表-自增主键ID');
                $table->tinyInteger('status')->default('0')->comment('用户状态，1开启，2关闭，4禁用，0默认其他');
                $table->string('name', 64)->default('')->comment('名称')->unique('name');
                $table->string('testdata1', 64)->default('')->comment('测试数据1');
                $table->string('testdata2', 64)->default('')->comment('测试数据2');
                $table->string('testdata3', 64)->default('')->comment('测试数据3');
                $table->string('testdata4', 64)->default('')->comment('测试数据4');
                $table->string('testdata5', 64)->default('')->comment('测试数据5');
                $table->dateTime('create_datetime')->default('2000-01-01 00:00:00')->comment('创建时间');
                $table->dateTime('update_datetime')->default('2000-01-01 00:00:00')->comment('更新时间');
                $table->string('backup1', 64)->default('')->comment('备用字段1');
                $table->string('backup2', 64)->default('')->comment('备用字段2');
                $table->string('backup3', 64)->default('')->comment('备用字段3');
                $table->unsignedBigInteger('uplockid')->default('1')->comment('更新冲突锁id');
            });
            //初始化插入root数据
            DB::table($this->tablename)->insert([
                ['status' => '1', 'name' => 'github项目地址', 'testdata1' => 'https://github.com/msg8-site/xlaravel', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['status' => '1', 'name' => 'gitee项目地址', 'testdata1' => 'https://gitee.com/msg8-site/xlaravel', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['status' => '1', 'name' => 'xlaravel演示后台', 'testdata1' => 'https://xlaravel.msg8.site/index', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['status' => '1', 'name' => '辅助代码库', 'testdata1' => 'https://code.msg8.site/', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['status' => '1', 'name' => '好网址导航', 'testdata1' => 'https://www.msg8.site/', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
                ['status' => '1', 'name' => 'xlaravel文档', 'testdata1' => 'https://sys.msg8.site/', 'create_datetime' => date('Y-m-d H:i:s'), 'backup1' => '初始化数据'],
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
