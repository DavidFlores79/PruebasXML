<?php

namespace App\Http\Controllers;

use App\Traits\XmlTrait;
use Illuminate\Http\Request;
use File;
use ZipArchive;

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
        $xmlString = base64_decode($base64String);
        
        $dataXML = $this->readXMLData($xmlString);

        $data = [
            "code" => 200,
            "status" => "success",
            "data" => $dataXML
        ];
        return response()->json($data, $data["code"]);
    }

    public function cargaZip(Request $request)
    {

        $rules = [
            "zip" => "required",
            "nombre_zip" => "required|string",
            // "proveedor" => "required|string",
            // "sociedad" => "required|string",
        ];
        $this->validate($request, $rules);

        // Save zip File
        $fileName = $request->input('nombre_zip');
        $base64String = $request->input("zip");
        $folderPath = storage_path("app/public/zip_files/".auth()->user()->id."/");
        if (!File::exists($folderPath)) {
            \File::makeDirectory($folderPath, 0755, true);
        }
        $file = $folderPath . $fileName;
        // $zip_Array = explode(";base64,", $base64String);
        // $zip_contents = base64_decode($zip_Array[0]);
        $zip_contents = file_get_contents("compress.zlib://data://text/plain;base64," . $base64String);
        file_put_contents($file, $zip_contents); //guarda el archivo zip en storage

        $data = $this->getCsvContent($folderPath, $fileName);

        $registros = $data['csv_content'];
        
        for ($i=0; $i < count($registros) ; $i++) { 
            
            if(end($registros[$i]) != null ){
                $nuevoArray[] = end($registros[$i]);
            }
        }

        return $data = [
            "code" => 200,
            "status" => "success",
            "array" => $nuevoArray,
        ];
        
        
        //$data = $this->upzipFiles($folderPath, $fileName);

        return response()->json($data, $data["code"]);

    }
}
