@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Forbidden'))
@section('buttons')
    <form method="post" action="{{ route('platform.logout') }}">
        @csrf
        <button type="submit"> Выход </button>
    </form>
@endsection
