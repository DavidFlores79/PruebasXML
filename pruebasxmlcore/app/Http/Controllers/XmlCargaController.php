<?php

namespace App\Http\Controllers;

use App\Traits\XmlTrait;
use Illuminate\Http\Request;
use File;

class XmlCargaController extends Controller
{
    use XmlTrait;

    public function cargaXml(Request $request)
    {
        $rules = [
            "xml" => "required",
            "nombre_xml" => "string|required",
        ];
        $this->validate($request, $rules);

        $base64String = $request->input("xml");

        if (!base64_decode($base64String))
            $data = [
                "code" => 422,
                "status" => "error",
                "errors" => [
                    "No es un formato válido."
                ],
            ];
        $data = $this->readXMLData($base64String);

        return response()->json($data, $data["code"]);
    }

    public function cargaZip(Request $request)
    {

        $rules = [
            "zip" => "required",
            "nombre_zip" => "required|string",
        ];
        $this->validate($request, $rules);

        // Save zip File
        $base64String = $request->input("zip");
        $folderPath = storage_path("app/public/zip_files/");
        if (!File::exists($folderPath)) {
            \File::makeDirectory($folderPath, 0755, true);
        }
        $file = $folderPath . $request->input('nombre_zip');
        // $zip_Array = explode(";base64,", $base64String);
        // $zip_contents = base64_decode($zip_Array[0]);
        $zip_contents = file_get_contents("compress.zlib://data://text/plain;base64," . $base64String);
        file_put_contents($file, $zip_contents);


        //return file_get_contents("zip://" . $file . "#index.csv");

        $data = $this->upzipFiles($file);
        return response()->json($data, $data["code"]);
    }
}
