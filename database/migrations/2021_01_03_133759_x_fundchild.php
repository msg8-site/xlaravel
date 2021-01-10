<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class XFundchild extends Migration
{
    //数据表名称
    private $tablename = 'x_fundchild';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('基金子表-自增主键ID');
                $table->tinyInteger('fid')->default('0')->comment('主表关联id');
                $table->date('thedate')->default('2000-01-01')->comment('基金对应日期');
                $table->double('jingzhi', 8, 4)->default('0.0000')->comment('净值，单位元');
                $table->double('inmoney', 9, 2)->default('0.00')->comment('买入金额，单位元');
                $table->double('outcount', 9, 2)->default('0.00')->comment('卖出份额，单位份');
                $table->dateTime('create_datetime')->default('2000-01-01 00:00:00')->comment('创建时间');
                $table->dateTime('update_datetime')->default('2000-01-01 00:00:00')->comment('更新时间');
                $table->unsignedBigInteger('uplockid')->default('1')->comment('更新冲突锁id');
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
