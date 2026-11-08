@extends('layouts.app')
@section('title', 'Cetak Barcode - Atelier')
@section('back', route('dashboard'))
@section('page_title', 'Cetak Barcode')

@section('content')
@livewire('user-cetak-barcode')
@endsection
