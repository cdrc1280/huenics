@extends('errors.layout')

@section('title', '403 Access Restricted')

@section('badge', 'Error 403 • Restricted Area')

@section('code', '403')

@section('heading', 'Restricted Administrative Portal')

@section('message')
    Access to this operational area or document requires verified administrative credentials. If you are an authorized Huenics staff member or logistics executive, please log in with your assigned account.
@endsection
