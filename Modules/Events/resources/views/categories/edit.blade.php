@extends('adminlte::page')

@section('content')
    <form action="{{route('events::categories.update',['category'=>$category->id])}}" method="post" class="pt-3">
        @csrf
        @method('put')
        <input type="text" class="form-control mb-3" name="title" placeholder="Title" value="{{$category->title}}">
        <button class="btn btn-primary w-100">Update</button>
    </form>
@endsection
