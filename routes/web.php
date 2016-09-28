<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('import_books', function(){

	// Get All Books Tables in db, Where not books Table.
	$tables = DB::select('SHOW TABLES 
		WHERE 
		(
		`Tables_in_'.env('DB_DATABASE').'` LIKE "b%" OR 
		`Tables_in_'.env('DB_DATABASE').'` LIKE "t%" 
		)
		AND 
		(
		`Tables_in_'.env('DB_DATABASE').'` NOT LIKE "books" AND 
		`Tables_in_'.env('DB_DATABASE').'` NOT LIKE "titles"
		);');

	foreach ($tables as $table) {
    	foreach ($table as $value){

        	import_book_title_tables($value);
    	}
	}	
});

function import_book_title_tables($value){

	// Get BookID
	if (strstr($value, 'b' ))
		$table = explode('b', $value);
	elseif (strstr($value, 't'))
		$table = explode('t', $value);

	$book_id = $table[1];

	if (\App\Book::whereBookId($book_id)->count()){
		
		// Drop Table if existed in Books Table
		Illuminate\Support\Facades\Schema::dropIfExists($value);

	} else {

		// Retreive All Pages.
		$pages = DB::table($value)->get();

		foreach ($pages as $page) {

			$shamela_book = new App\Book();
			
			$shamela_book->part = $page->part;
			$shamela_book->page = $page->page;
			$shamela_book->seal = $page->seal;
			$shamela_book->text = $page->nass;
			$shamela_book->book_id = $book_id;
			
			$shamela_book->save();
		}

		// Drop Table After Insertion
		Illuminate\Support\Facades\Schema::dropIfExists($value);
	}

	$title_table = 't' . $book_id;

	// Drop Table If Existed in Database
	if (\App\Title::whereBookId($book_id)->count()){
		Illuminate\Support\Facades\Schema::dropIfExists($title_table);
	} else {
		// Retreive All Pages.
		$rows = DB::table($title_table)->get();

		foreach ($rows as $row) {

			$shamela_title = new App\Title();
			
			$shamela_title->page = $row->id;
			$shamela_title->level= $row->lvl;
			$shamela_title->sub = $row->sub;
			$shamela_title->title = $row->tit;
			$shamela_title->book_id = $book_id;
			
			$shamela_title->save();
		}

		Illuminate\Support\Facades\Schema::dropIfExists($title_table);
	}



	
}