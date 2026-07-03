@extends('adminlte::page')

@section('content')
    <form action="{{route('events::categories.store')}}" method="post" class="pt-3">
        @csrf
        <input type="text" class="form-control mb-3" name="title" placeholder="Title">
        <button class="btn btn-primary w-100">Save</button>
    </form>
@endsection
