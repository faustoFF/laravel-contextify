<?php
/**
 * @var string $hostname
 * @var string $env
 * @var \Carbon\Carbon $datetime
 * @var string $callContext
 * @var ?int $callContextPid
 * @var ?string $callContextCommand
 * @var string $callContextUid
 * @var string $msg
 * @var string $level
 * @var mixed $context
 */

use Monolog\Utils;
?>

@extends('layout')

@section('content')
    <pre>{{ $msg }}</pre>

    <p>
        <b>Hostname:</b> {{ $hostname }}<br>
        <b>ENV:</b> {{ $env }}<br>
        <b>Level:</b> {{ ucfirst($level) }}<br>
        <b>Datetime:</b> {{ $datetime->toDateTimeString() }}<br>
        <b>Log context:</b> {{ $callContext }}<br>
        <b>PID:</b> {{ $callContextPid }}<br>
        <b>Command:</b> {{ $callContextCommand }}<br>
        <b>UID:</b> {{ $callContextUid }}
        @if($context)
            <br>
            <b>Context:</b><br>
            <pre>{{ is_string($context) ? $context : Utils::jsonEncode($context, Utils::DEFAULT_JSON_FLAGS | JSON_PRETTY_PRINT) }}</pre>
        @endif
    </p>
@endsection
