@extends('errors.server')

@section('code', '429')
@section('title', 'Give it a minute.')
@section('message', 'That’s a lot of requests in a short time. Wait a minute, then try again.')
@section('note', 'The limit resets automatically; you don’t need to change anything.')
