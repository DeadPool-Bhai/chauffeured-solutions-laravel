@extends('layouts.app')

@section('content')
    <main>
        @if ($page->h1)
            <h1>{{ $page->h1 }}</h1>
        @endif
        {!! $page->body_html !!}
    </main>
@endsection
