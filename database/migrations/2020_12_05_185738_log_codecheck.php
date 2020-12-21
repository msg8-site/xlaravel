<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LogCodecheck extends Migration
{
    //数据表名称
    private $tablename = 'log_codecheck';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('谷歌验证日志表-自增主键ID');
                $table->tinyInteger('checkflag')->default('0')->comment('验证结果，1成功，2失败');
                $table->string('describename', 128)->default('')->comment('区分标识名称');
                $table->string('checkusername', 64)->default('')->comment('验证用户名');
                $table->string('checkuserip', 64)->default('')->comment('验证对应IP地址');
                $table->string('checkcode', 64)->default('')->comment('输入验证的的谷歌动态码');
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
