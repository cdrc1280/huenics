@extends('errors.layout')

@section('title', '500 Server Processing Notice')

@section('badge', 'Error 500 • Internal System Notice')

@section('icon')
    <i class="fa-solid fa-server"></i>
@endsection

@section('code', '500')

@section('heading', 'System Processing Request')

@section('message')
    Our enterprise servers encountered an unexpected situation while processing your transaction. The technical operations team has been notified. Please try reloading or contact our CS desk.
@endsection
