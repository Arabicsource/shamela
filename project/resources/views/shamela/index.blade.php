@extends('shamela.layout')
@section('content')
    <div class="row">
        <div>
            <div class="welcome">
                {{ trans('common.welcome') }}<br>
                <div class="welcome_book_count">
                    {{ trans('common.categories_count') }}: {{ App\Category::count('id') }} |
                    {{ trans('common.books_count') }}: {{ App\Book::count('id') }} |
                    {{ trans('common.authors_count') }}: {{ App\Author::count('id') }} |
                    {{ trans('common.pages_count') }}: {{ App\Page::count('id') }}
                </div>
                <div class="welcome_developed_by">
                    <br>
                    <a class="btn purple" title="{{ trans('button.book') }}" data-toggle="modal"
                       href="#basic">
                        <i class="fa fa-book"></i> {{ trans('button.book') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
@stop
@section('style')
    <style type="text/css">
        .welcome_book_count {
            font-family: ns;
            font-size: 20px;
            color: #2f353b;
            padding-bottom: 20px;
            opacity: 1;
        }

        .welcome_developed_by {
            font-family: ns;
            font-size: 15px;
            color: #E87E04;
            opacity: 1;
        }

        .modal .modal-body div {
            font-family: Arial;
            font-size: 13pt;
            font-weight: bold;
            line-height: 180%;
        }

        .welcome {
            font-size: 80px;
            text-align: center;
            padding: 40px;
            font-family: 'amiri';
            color: #D91E18;
            background-color: #FFF;
            opacity: 1;
            position: fixed;
            top: 50%;
            left: 50%;
            /* bring your own prefixes */
            transform: translate(-50%, -50%);
        }
    </style>
@stop
@section('script')
    <script type="text/javascript">
    </script>
@stop