@php use Monolog\Utils; @endphp

@extends('contextify::layout')

@section('content')
    <pre>{{ $msg }}</pre>

    <p>
        <b>Level:</b> {{ ucfirst($level) }}<br>
        <br>
        <b>Context:</b><br>
        <pre>{{ is_string($context) ? $context : Utils::jsonEncode($context, Utils::DEFAULT_JSON_FLAGS | JSON_PRETTY_PRINT) }}</pre>
        <br>
        <b>Extra context:</b><br>
        <pre>{{ Utils::jsonEncode($extraContext, Utils::DEFAULT_JSON_FLAGS | JSON_PRETTY_PRINT) }}</pre>
    </p>
@endsection
