@extends('layouts.app')
@section('title', 'Scan Barcode - Atelier')
@section('back', route('dashboard'))
@section('page_title', 'Scan Barcode')

@section('content')
@livewire('user-scan-barcode')
@endsection
