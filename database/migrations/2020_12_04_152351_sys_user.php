<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SysUser extends Migration
{
    //数据表名称
    private $tablename = 'sys_user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('用户表-自增主键ID');
                $table->tinyInteger('status')->default('0')->comment('用户状态，1开启，2关闭，4禁用，0锁定');
                $table->unsignedBigInteger('role')->default('0')->comment('角色表对应id');
                $table->string('username', 64)->default('')->comment('用户名')->unique('username');
                $table->string('nickname', 64)->default('')->comment('用户昵称姓名');
                $table->string('passwd', 64)->default('')->comment('用户密码');
                $table->string('useremail', 128)->default('')->comment('用户邮箱');
                $table->string('userphone', 32)->default('')->comment('用户手机');
                $table->string('usergooglekey', 128)->default('')->comment('用户谷歌动态码密钥');
                $table->dateTime('create_datetime')->default('2000-01-01 00:00:00')->comment('创建时间');
                $table->dateTime('update_datetime')->default('2000-01-01 00:00:00')->comment('更新时间');
                $table->tinyInteger('moreclientflag')->default('2')->comment('多客户端登陆限制，1开启，2关闭');
                $table->string('moreclientkey', 64)->default('')->comment('多客户端最新登陆标识，配合判断开关使用');
                $table->string('backup1', 64)->default('')->comment('备用字段1');
                $table->string('backup2', 64)->default('')->comment('备用字段2');
                $table->string('backup3', 64)->default('')->comment('备用字段3');
                $table->unsignedBigInteger('uplockid')->default('1')->comment('更新冲突锁id');
            });
            //初始化插入root数据
            DB::table($this->tablename)->insert([
                'status'          => '1',
                'role'            => '0',
                'username'        => 'root',
                'nickname'        => '超级管理员',
                'passwd'          => hash('sha256',hash('sha256',hash('sha256','msg8.site'))),   //初始密码为msg8.site
                'create_datetime' => date('Y-m-d H:i:s'),
                'moreclientflag'  => '1',
                'backup1'         => '系统最高权限用户root',
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
        Schema::dropIfExists($this->tablename);
    }
}
