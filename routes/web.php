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

// Import Set Of Books which found in 0cat, 0bok tables
Route::get('import_books', 'ImportBooksController');

Route::get('/', function () {
    return view('welcome');
});

Route::get('import_book', function(){
	
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

	$book = DB::table('main')->where('BkId', $book_id)->first();

	if (\App\Book::where(['seal' => $book->seal, 'aSeal' => $book->aSeal])->count() == 0){

		$now = Carbon\Carbon::now('utc')->toDateTimeString();
		
		$book_array = (array) $book;

		unset($book_array['BkId']);
		$book_array['created_at'] = $now;
		$book_array['updated_at'] = $now;
		
		$book_inserted_id = \App\Book::insertGetId($book_array);
	}

	// Drop Page Table (b$num) if existed in Books Table
	if (\App\Page::whereBookId($book_inserted_id)->count() == 0){

		// Retreive All Pages.
		$pages = DB::table($value)->get();
		$pages_array = [];
		$pages_array = $pages->map(function ($item) use ($book_inserted_id) {

			// $now = Carbon\Carbon::now('utc')->toDateTimeString();

			$page_array = [];
			$page_array['seal'] = $item->seal;
			$page_array['part'] = $item->part;
			$page_array['number'] = $item->page;
			$page_array['text'] = $item->nass;
			$page_array['book_id'] = $book_inserted_id;

			return $page_array;
		    
		});

		foreach ($pages_array->chunk(100) as $chunk) {
			\DB::table('pages')->insert($chunk->toArray());
		}
	}

	// Drop Book Table
	Illuminate\Support\Facades\Schema::dropIfExists($value);

	// Title Table Name
	$title_table = 't' . $book_id;

	if (\App\Title::whereBookId($book_inserted_id)->count() == 0){

		// Retreive All Titles.
		$rows = DB::table($title_table)->get();

		$rows_array = [];
		$rows_array = $rows->map(function ($item) use ($book_inserted_id) {

			// $now = Carbon\Carbon::now('utc')->toDateTimeString();

			$row_array = [];
			$row_array['level'] = $item->lvl;
			$row_array['page'] = $item->id;
			$row_array['sub'] = $item->sub;
			$row_array['title'] = $item->tit;
			$row_array['book_id'] = $book_inserted_id;

			return $row_array;
		});

		// Mass insert in Title Table.
		foreach ($rows_array->chunk(100) as $chunk) {
			\DB::table('titles')->insert($chunk->toArray());
		}
	}
	
	// Drop Title Table
	// Illuminate\Support\Facades\Schema::dropIfExists($title_table);
}

