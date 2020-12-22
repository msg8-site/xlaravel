<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DocMarkdown extends Migration
{
    //数据表名称
    private $tablename = 'doc_markdown';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tablename)) {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->bigIncrements('id')->comment('文档管理-自增主键ID');
                $table->string('docname', 64)->default('')->comment('文档名称')->unique('docname');
                $table->string('typename', 64)->default('')->comment('类别名称');
                $table->bigInteger('orderbyid')->default('100')->comment('排序规则，desc,默认100');
                $table->mediumText('content')->comment('markdown文档内容存放');
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
