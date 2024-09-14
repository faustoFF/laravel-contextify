<?php
/**
 * @var string $hostname
 * @var string $env
 * @var \Carbon\Carbon $datetime
 * @var ?int $pid
 * @var ?string $command
 * @var array $server
 * @var string $exception
 */
?>

@extends('emails.layout')

@section('content')
    <pre>{{ $exception }}</pre>

    <p>
        <b>Hostname:</b> {{ $hostname }}<br>
        <b>ENV:</b> {{ $env }}<br>
        <b>Datetime:</b> {{ $datetime->toDateTimeString() }}<br>
        <b>PID:</b> {{ $pid }}<br>
        <b>Command:</b> {{ $command }}<br>
        <b>Server:</b> {!! nl2br(var_export($server, true)) !!}<br>
    </p>
@endsection
