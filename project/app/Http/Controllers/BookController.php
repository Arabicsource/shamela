<?php

namespace App\Http\Controllers;

use App\Author;
use App\Book;
use App\Http\Middleware\MainMiddleware;
use App\Page;
use App\Title;
use Illuminate\Http\Request;

use App\Http\Requests;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware(MainMiddleware::class);
    }

    public function show(Request $request, $book_id, $shamela_id = null)
    {
        $data = [];

        $data['book_id'] = $book_id;

        // Get Book
        $book = Book::findOrFail($book_id);
        $data['book'] = $book;

        // Get First Page
        if ($shamela_id != null) {

            // Get Page By ID
            $data['shamela_id'] = $shamela_id;
            $data['page'] = Page::select(['page', 'text'])->whereBookId($book_id)->whereShamelaId($shamela_id)->first();
        } else {

            // Get First Page
            $shamela_id = Page::whereBookId($book_id)->min('shamela_id');

            $data['shamela_id'] = $shamela_id;
            $data['page'] = Page::select(['shamela_id', 'page', 'text'])
                ->whereBookId($book_id)
                ->whereShamelaId($shamela_id)
                ->first();
        }


        if ($shamela_id == null)
            return trans('common.book_not_added_yet');

        // Book Buttons
        $first = \DB::table('pages')->whereBookId($book_id)->min('shamela_id');
        $data['first_url'] = route('book', $book_id . '/' . $first);

        $prev = \DB::table('pages')->whereBookId($book_id)->where('shamela_id', '<', $shamela_id)->max('shamela_id');
        $data['prev_url'] = route('book', $book_id . '/' . $prev);

        $next = \DB::table('pages')->whereBookId($book_id)->where('shamela_id', '>', $shamela_id)->min('shamela_id');
        $data['next_url'] = route('book', $book_id . '/' . $next);

        $last = \DB::table('pages')->whereBookId($book_id)->max('shamela_id');
        $data['last'] = $last;
        $data['last_url'] = route('book', $book_id . '/' . $last);

        $data['page']->text = $this->hs($data['page']->text);

        if ($request->ajax()) {
            if ($data['page'] == null) {
                return response()->json([
                    'error' => trans('common.page_not_found')
                ]);
            }

            return response()->json([
                'text' => nl2br($data['page']->text),
                'shamela_id' => $shamela_id,
                'page' => $shamela_id . '|' . $last,
                'first_url' => $data['first_url'],
                'prev_url' => $data['prev_url'],
                'next_url' => $data['next_url'],
                'last_url' => $data['last_url']
            ]);
        }

        // Get Book's Author
        // $data['author'] = Author::find(Book::find($book_id)->author_id);
        // Get Book's Titles
        $data['titles'] = Title::select(['id', 'shamela_id', 'level', 'title'])
            ->whereBookId($book_id)
            ->orderBy('id')
            ->get();
        // ->whereLevel(0)

        // Shamela URL- oNum
        $data['shamela_url'] = 'http://shamela.ws/index.php/book/' . $book->id;

        return View('shamela.book', $data);
    }

    public function search(Request $request)
    {

        $books = Book::where('title', 'like', '%' . $request->keyword . '%')->take(10)->get();

        $books_html = '';
        $books_html .= '<ul>';

        foreach ($books as $book) {
            $books_html .= '<li>';
            $books_html .= '<a href="' . route('book', $book->id) . '">';
            $books_html .= $book->title;
            $books_html .= '</a>';
            $books_html .= '<br/>';
            $books_html .= '</li>';
        }

        $books_html .= '</ul>';

        return response()->json([
            'books' => $books_html
        ]);
    }

    public function highlight($text, $words)
    {
        preg_match_all('~\w+~', $words, $m);

        if (!$m)
            return $text;

        $re = '~\\b(' . implode('|', $m[0]) . ')\\b~';

        return preg_replace($re, '<b>$0</b>', $text);
    }

    public function hs($text)
    {
        return preg_replace('/[{]([\p{Arabic}\d-\[\]_+ ]+)[}]/u', '<b>$0</b>', $text);
    }
}
