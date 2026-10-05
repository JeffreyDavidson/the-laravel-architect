@extends('errors.server')

@section('code', '403')
@section('title', 'This link can’t be opened.')
@section('message', 'It may have expired, been copied only in part, or need access you don’t have.')
@section('note', 'If someone sent you this link, ask them for a fresh one.')

{{-- Reloading a refused link gives the same answer, so offer only the way home. --}}
@section('actions')
    <a class="button primary" href="/">Go home</a>
@endsection
