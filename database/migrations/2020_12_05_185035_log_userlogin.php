<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LogUserlogin extends Migration
{
    //数据表名称
    private $tablename = 'log_userlogin';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('用户登录日志表-自增主键ID');
                $table->tinyInteger('flag')->default('0')->comment('用户状态，1登陆成功，2登陆失败');
                $table->string('loginname', 64)->default('')->comment('登陆用户名')->index('loginname');
                $table->string('loginip', 64)->default('')->comment('登陆IP地址');
                $table->string('errdescribe', 128)->default('')->comment('错误判断描述');
                $table->string('useragent', 1024)->default('')->comment('用户浏览器标识');
                $table->string('moreclientkey', 64)->default('')->comment('用户登录标识key');
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
