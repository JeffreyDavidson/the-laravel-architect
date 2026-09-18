@props(['seoSource' => null])

@php
    $headContent = isset($head) ? $head->toHtml() : '';
    $content = $slot->toHtml();
@endphp

@include('layouts.app', [
    'seoSource' => $seoSource,
    'head' => $headContent,
    'content' => $content,
])
