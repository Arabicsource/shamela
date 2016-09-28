<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Book;
use App\Page;
use App\Title;
use Carbon\Carbon;

class ImportBooksController extends Controller {

	private $book_inserted_id;
	private $book_id;
	
	public function __invoke(){

		// TimeStart
		$time_start = microtime(true);

		// Import Books
		$books = \DB::table('0bok')->get();
		foreach ($books as $book){
			$this->import_book($book);
		}

		// Import Categories
		$categories = \DB::table('0cat')->get();
		foreach ($categories as $category){
			$this->import_category($category);
		}

		$time = number_format(microtime(true) - $time_start, 4, '.', ',');
    	echo "Process Time: {$time} s";

	}

	public function import_book($imported_book){
		
		$this->book_id = $imported_book->bkid;

		// If Book Not Found in "books" Table, Insert, If Found Return bookID
		if (\App\Book::whereSeal($imported_book->seal)->count() == 0){

			$now = Carbon::now('utc')->toDateTimeString();
			
			$book_array = (array) $imported_book;

			$book = [];
			$book['number']      = $book_array['oNum'];
			$book['seal']        = $book_array['seal'];

			$book['title']       = $book_array['bk'];
			$book['abstract']    = $book_array['betaka'];
			$book['order']       = $book_array['bkord'];
			
			$book['author_id']   = $book_array['authno'];
			$book['category_id'] = $book_array['cat'];
			
			$book['created_at']  = $now;
			$book['updated_at']  = $now;
			
			$this->book_inserted_id = \App\Book::insertGetId($book);

		} else {
			$book = \App\Book::where('id', $imported_book->bkid)->first();
			$this->book_inserted_id = $book->id;
		}

		// Import Book's Pages
		$this->import_pages();

		// Import Book's Titles
		$this->import_titles();

		// Delete Book Row After Insertion
		\DB::table('0bok')->where('seal', $imported_book->seal)->delete();
	}

	public function import_pages(){
		
		// If Book Pages are not found, Import it
		if (\App\Page::whereBookId($this->book_inserted_id)->count() == 0){

			// Retreive All Pages.
			$pages = \DB::table('b'. $this->book_id)->get();
			$pages_array = [];
			$pages_array = $pages->map(function ($item)  {

				// $now = Carbon\Carbon::now('utc')->toDateTimeString();
				$page_array = [];
				$page_array['seal']    = $item->seal;
				$page_array['part']    = $item->part;
				$page_array['number']  = $item->page;
				$page_array['text']    = $item->nass;
				$page_array['book_id'] = $this->book_inserted_id;

				return $page_array;
			    
			});

			foreach ($pages_array->chunk(100) as $chunk) {
				\DB::table('pages')->insert($chunk->toArray());
			}
		}

		// Drop Book Table
		\Illuminate\Support\Facades\Schema::dropIfExists('b'. $this->book_id);

	}

	public function import_titles(){

		if (\App\Title::whereBookId($this->book_inserted_id)->count() == 0){

				// Retreive All Titles.
				$rows = \DB::table('t'. $this->book_id)->get();

				$rows_array = [];
				$rows_array = $rows->map(function ($item) {

				// $now = Carbon\Carbon::now('utc')->toDateTimeString();

				$row_array            = [];
				$row_array['level']   = $item->lvl;
				$row_array['page']    = $item->id;
				$row_array['sub']     = $item->sub;
				$row_array['title']   = $item->tit;
				$row_array['book_id'] = $this->book_inserted_id;

				return $row_array;
			});

			// Mass insert in Title Table.
			foreach ($rows_array->chunk(100) as $chunk) {
				\DB::table('titles')->insert($chunk->toArray());
			}
		}
		
		// Drop Title Table
		\Illuminate\Support\Facades\Schema::dropIfExists('t'. $this->book_id);
	}

	public function import_category($imported_category){

		// If Category Not Found in "categories" Table, Insert, If Found Return CategoryID
		if (\App\Category::whereTitle(trim($imported_category->name))->count() == 0){

			$category_array    = (array) $imported_category;
			
			$category          = [];
			$category['title'] = $category_array['name'];
			$category['level'] = $category_array['Lvl'];
			$category['order'] = $category_array['catord'];
			
			\App\Category::insertGetId($category);
		}
		/*
		else {

			$category = \App\Category::whereTitle(trim($imported_category->name))->first();
			$this->category_inserted_id = $category->id;
		}
		*/

		// Delete Category Row After Insertion
		// \DB::table('0cat')->whereId($imported_category->id)->delete();
	}
}
