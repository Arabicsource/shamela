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
	
	// TimeStart
	$time_start = microtime(true);

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

    $time = number_format(microtime(true) - $time_start, 4, '.', ',');
    echo "Process Time: {$time} s";
});
	
function import_book_title_tables($value){


	// Get BookID
	if (strstr($value, 'b' ))
		$table = explode('b', $value);
	elseif (strstr($value, 't'))
		$table = explode('t', $value);

	$book_id = $table[1];
	
	// Drop Book Table (b$num) if existed in Books Table
	if (\App\Book::whereBookId($book_id)->count() == 0){

		// Retreive All Pages.
		$pages = DB::table($value)->get();
		$pages_array = [];
		$pages_array = $pages->map(function ($item) use ($book_id) {

			$now = Carbon\Carbon::now('utc')->toDateTimeString();

			$page_array = [];
			$page_array['part'] = $item->part;
			$page_array['page'] = $item->page;
			$page_array['seal'] = $item->seal;
			$page_array['text'] = $item->nass;
			$page_array['book_id'] = $book_id;
			$page_array['created_at'] = $now;
			$page_array['updated_at'] = $now;

			return $page_array;
		    
		});

		foreach ($pages_array->chunk(100) as $chunk) {
			\DB::table('books')->insert($chunk->toArray());
		}
	}

	// Drop Book Table
	Illuminate\Support\Facades\Schema::dropIfExists($value);

	// Title Table Name
	$title_table = 't' . $book_id;


	if (\App\Title::whereBookId($book_id)->count() == 0){

		// Retreive All Titles.
		$rows = DB::table($title_table)->get();

		$rows_array = [];
		$rows_array = $rows->map(function ($item) use ($book_id) {

			$now = Carbon\Carbon::now('utc')->toDateTimeString();

			$row_array = [];
			$row_array['page'] = $item->page;
			$row_array['level'] = $item->level;
			$row_array['sub'] = $item->sub;
			$row_array['title'] = $item->nass;
			$row_array['book_id'] = $book_id;
			$row_array['created_at'] = $now;
			$row_array['updated_at'] = $now;

			return $row_array;
		});

		// Mass insert in Title Table.
		foreach ($rows_array->chunk(100) as $chunk) {
			\DB::table('titles')->insert($chunk->toArray());
		}
	}
	
	// Drop Title Table
	Illuminate\Support\Facades\Schema::dropIfExists($title_table);
}