<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class XFundtemp extends Migration
{
    //数据表名称
    private $tablename = 'x_fundtemp';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('基金缓存-自增主键ID');
                $table->string('fundcode', 12)->default('')->comment('基金标识号')->unique('fundcode');
                $table->string('fundname', 64)->default('')->comment('基金名称');
                $table->string('new_shijian', 64)->default('')->comment('最近更新时间');
                $table->string('new_jingzhi', 64)->default('')->comment('最新更新净值');
                $table->mediumText('jsonstring')->comment('基金详细json字符串')->nullable();;
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
