<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class XFundmain extends Migration
{
    //数据表名称
    private $tablename = 'x_fundmain';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('基金主表-自增主键ID');
                $table->string('fundcode', 12)->default('')->comment('基金标识号')->unique('fundcode');
                $table->string('fundname', 64)->default('')->comment('基金名称');
                $table->string('new_shijian', 20)->default('')->comment('最近更新时间');
                $table->string('new_jingzhi', 16)->default('')->comment('最新更新净值');
                $table->string('sumhas_count', 16)->default('')->comment('总持有份额');
                $table->string('sumhas_money', 16)->default('')->comment('总持有金额');
                $table->string('sumhas_average', 16)->default('')->comment('总持有平均单价');
                $table->string('sumhas_profit', 16)->default('')->comment('总持有累计盈利');
                $table->string('sumhas_incount', 16)->default('')->comment('总持有累计买入份额');
                $table->string('sumhas_inmoney', 16)->default('')->comment('总持有累计买入金额');
                $table->string('sumhas_outcount', 16)->default('')->comment('总持有累计卖出份额');
                $table->string('sumhas_outmoney', 16)->default('')->comment('总持有累计卖出金额');
                $table->text('jingzhi_zhangdie_jsonstring')->comment('净值涨跌等json字符串')->nullable();
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
