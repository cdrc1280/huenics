@extends('errors.layout')

@section('title', '419 Session Expired')

@section('badge', 'Error 419 • Security Token Timeout')

@section('code', '419')

@section('heading', 'Quotation Session Timed Out')

@section('message')
    Your secure form token has expired due to inactivity. This safeguards your procurement data. Please refresh the page or return to the Quotation Builder to regenerate your estimate.
@endsection
