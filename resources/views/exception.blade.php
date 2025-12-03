@php use Monolog\Utils; @endphp

@extends('contextify::layout')

@section('content')
    <pre>{{ $exception }}</pre>

    <p>
        <b>Extra context:</b><br>
        <pre>{{ Utils::jsonEncode($extraContext, Utils::DEFAULT_JSON_FLAGS | JSON_PRETTY_PRINT) }}</pre>
    </p>
@endsection
