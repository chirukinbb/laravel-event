@extends('adminlte::page')

@php
    $configBS4 = ['format' => 'DD/MM/YYYY HH:mm'];
    $configS2 = [
        'tags' => true,
        'tokenSeparators' => [',', ' '],
        'width' => '100%'
    ]
@endphp

@section('plugins.BsCustomFileInput', true)
@section('plugins.Select2', true)
@section('plugins.TempusDominusBs4', true)
@section('plugins.Awesomplete', true)
@section('plugins.Cropper', true)

@section('css')
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border: 1px solid #0069d9;
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
            margin-right: 5px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ffc107;
        }

        .awesomplete {
            width: 100%;
        }
    </style>
@endsection

@section('content')
    <form action="{{route('events::store')}}" method="post" class="pt-3" enctype="multipart/form-data">
        @csrf
        <div class="thumbnail"></div>
        <x-adminlte-input-file name="thumbnail" placeholder="Choose a file..." accept=".jpg, .jpeg, .png, .webp">
            <x-slot name="prependSlot">
                <div class="input-group-text bg-lightblue">
                    <i class="fas fa-upload"></i>
                </div>
            </x-slot>
        </x-adminlte-input-file>
        <x-adminlte-input name="title" placeholder="Title"/>
        <x-adminlte-textarea name="description" placeholder="Insert description..." rows="5"/>
        <div class="row mb-3">
            <div class="col-3">
                <x-adminlte-select name="user_id">
                    <option>-- Choose author --</option>
                    @foreach($users as $user)
                        <option value="{{$user->id}}">{{$user->name}}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-3">
                <x-adminlte-select name="category_id">
                    <option>-- Choose category --</option>
                    @foreach($categories as $category)
                        <option value="{{$category->id}}">{{$category->title}}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-3">
                <x-adminlte-input name="slots" placeholder="Slots" type="number"/>
            </div>
            <div class="col-3">
                <x-adminlte-input-date name="planing_time" label-class="text-danger"
                                       placeholder="DateTime" :config="$configBS4">
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-white">
                            <i class="far fa-lg fa-calendar-alt text-danger"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input-date>
            </div>
        </div>

        <x-events::address-input/>

        <select name="tags[]" id="tags" multiple="multiple" class="form-control mb-3">
            @foreach($tags as $tag)
                <option value="{{$tag->name}}">{{$tag->name}}</option>
            @endforeach
        </select>
        <div class="submit mt-3">
            <button type="submit" class="btn btn-primary w-100">Save</button>
        </div>
    </form>

    <x-adminlte-modal id="modalCustom" title="Crop image" theme="teal"
                      icon="fas fa-bell" v-centered static-backdrop scrollable>
        <div id="cropper-container" style="display: none;  width: 100%;">
            <div style=" overflow: hidden;">
                <img id="cropper-image" style="width: 100%;">
            </div>
            <x-slot name="footerSlot">
                <x-adminlte-button class="mr-auto" theme="success" label="Accept" id="crop-save-btn"/>
                <x-adminlte-button theme="danger" label="Dismiss" data-dismiss="modal"/>
            </x-slot>
        </div>
    </x-adminlte-modal>
@endsection

@section('js')
    <script>
        (function ($) {
            $('#tags').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                placeholder: "Start typing a tag...",
                allowClear: true,
                width: '100%'
            });
        })(jQuery)
    </script>
    <script>
        let pendingImageSrc = null;

        document.getElementById('thumbnail').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            const label = this.nextElementSibling;
            label.textContent = file.name;

            // 2. Читаем файл в память
            const reader = new FileReader();
            reader.onload = function (event) {
                pendingImageSrc = event.target.result;
                $('#modalCustom').modal('show');
            };
            reader.readAsDataURL(file);
        });

        $('#modalCustom').on('shown.bs.modal', function () {
            if (!pendingImageSrc) return;

            const cropperImage = document.getElementById('cropper-image');
            cropperImage.src = pendingImageSrc;

            document.getElementById('cropper-container').style.display = 'block';

            if (window.currentCropper) {
                window.currentCropper.destroy();
            }

            window.currentCropper = new Cropper(cropperImage, {
                aspectRatio: 16 / 9,
                viewMode: 1,
                autoCropArea: 0.8
            });
        });

        $('#modalCustom').on('hidden.bs.modal', function () {
            if (window.currentCropper) {
                window.currentCropper.destroy();
                window.currentCropper = null;
            }
            document.getElementById('cropper-image').src = '';
            document.getElementById('cropper-container').style.display = 'none';
            pendingImageSrc = null;
        });

        document.getElementById('crop-save-btn').addEventListener('click', function () {
            if (!window.currentCropper) return;

            const canvas = window.currentCropper.getCroppedCanvas({
                width: 800,
                height: 450,
            });

            canvas.toBlob(function (blob) {
                if (!blob) {
                    alert('Ошибка при обработке изображения');
                    return;
                }

                const fileInput = document.getElementById('thumbnail');
                const dataTransfer = new DataTransfer();
                const convertedFile = new File([blob], "thumbnail.webp", {type: "image/webp"});
                dataTransfer.items.add(convertedFile);
                fileInput.files = dataTransfer.files;

                fileInput.nextElementSibling.textContent = "thumbnail.webp (Обрезан)";

                $('#modalCustom').modal('hide');

                console.log('Файл успешно сконвертирован в WebP и подготовлен к отправке.');
            }, 'image/webp', 0.85);
        });
    </script>
@endsection
