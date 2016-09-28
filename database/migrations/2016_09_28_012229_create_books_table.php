<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        
        Schema::create('books', function (Blueprint $table) {
            $table->increments('id');
              
            $table->integer('number');
            $table->string('seal');
              
            // Book Title
            $table->string('title');
            $table->text('abstract')->nullable();
            $table->integer('order')->default(0);

            $table->integer('author_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('books');
    }
}
