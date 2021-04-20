<?php

namespace UserFrosting\Sprinkle\Site\Database\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Database\Migration;

class CompaniesTable extends Migration
{
    public function up()
    {
        if (!$this->schema->hasTable('companies')) {
            $this->schema->create('companies', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('company_name')->unique();
                $table->string('email');
                $table->string('logo');
                $table->string('website');
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });
        }
    }

    public function down()
    {
        $this->schema->drop('companies');
    }
}