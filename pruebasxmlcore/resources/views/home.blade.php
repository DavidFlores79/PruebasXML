@extends('layouts.main')

@section('page-title', 'Carga XML')
@section('ngApp', 'home')
@section('ngController', 'home')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-8">
            <div class="card">
                <div class="card-header bg-default d-flex flex-column flex-sm-row justify-content-between ">
                    <div class=" centers-title my-2 my-sm-0">
                        <h5 class="font-weight-bold">@yield('page-title')</h5>
                    </div>
                </div>
                <div class="card-body">
                    <form class="was-validated" ng-submit="cargarXML( $event )" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="anio_fiscal">Año Fiscal</label>
                            <input class="form-control" type="text" name="anio_fiscal" id="anio_fiscal" readonly pattern="[0-9]+" maxlength="4" required autofocus value="2022">
                        </div>
                        <div class="input-group is-invalid">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" accept=".xml" name="xml" id="xml" value="Navegar" required autofocus>
                                <label class="custom-file-label" for="xml">Escoger Archivo Xml...</label>
                            </div>
                        </div>
                        <div class="form-group pt-3">
                            <button type="submmit" class="btn btn-sm btn-primary" data-toggle="tooltip" data-placement="top" title="Cargar el archivo XML" onmouseenter="$(this).tooltip('show')" onmouseleave="$(this).tooltip('hide')" onclick="$(this).tooltip('hide')"><i class="fas fa-upload"></i> <span class="d-none d-md-inline-block">Cargar XML</span></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="my-4"></div>
            <div class="card">
                <div class="card-header bg-default d-flex flex-column flex-sm-row justify-content-between ">
                    <div class=" centers-title my-2 my-sm-0">
                        <h5 class="font-weight-bold">Carga Masiva ZIP</h5>
                    </div>
                </div>
                <div class="card-body">
                    <form class="was-validated" ng-submit="cargarZIP( $event )" enctype="multipart/form-data">
                        <div class="input-group is-invalid">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" accept=".zip" name="zip" id="zip" value="Navegar" required autofocus>
                                <label class="custom-file-label" for="zip">Escoger Archivo Zip...</label>
                            </div>
                        </div>
                        <div class="form-group pt-3">
                            <button type="submmit" class="btn btn-sm btn-primary" data-toggle="tooltip" data-placement="top" title="Cargar el archivo ZIP" onmouseenter="$(this).tooltip('show')" onmouseleave="$(this).tooltip('hide')" onclick="$(this).tooltip('hide')"><i class="fas fa-upload"></i> <span class="d-none d-md-inline-block">Cargar ZIP</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('ngFile')
<script src="{{ asset('js/home.js') }}"></script>
@endsection

@section('scripts')
<script>
    $('#xml').on('change',function(){
        //get the file name
        var fileName = $(this).val().replace('C:\\fakepath\\', " ");
        //replace the "Choose a file" label
        $(this).next('.custom-file-label').html(fileName);
    })
    $('#zip').on('change',function(){
        //get the file name
        var fileName = $(this).val().replace('C:\\fakepath\\', " ");
        //replace the "Choose a file" label
        $(this).next('.custom-file-label').html(fileName);
    })
</script>
@endsection

@section('styles')
<style>
    .custom-file-input~.custom-file-label::after {
        content: "Elegir";
    }
</style>
@endsection