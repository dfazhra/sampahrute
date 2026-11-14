@extends('sampahrute.layouts.public')
@section('title', $code.' · '.$title)
@section('content')
<section class="error-page">
    <div class="error-card">
        <span class="error-code">{{ $code }}</span>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        <div class="button-row center">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="btn btn-soft">‹ Kembali</a>
            <a href="{{ route('home') }}" class="btn btn-primary">Ke beranda</a>
        </div>
    </div>
</section>
@endsection
