<?php

namespace App\Http\Controllers;

use App\Models\XmlCarga;
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
            "proveedor" => "string|required",
            "sociedad" => "string|required",
            "documento" => "string|required",
            "importe_documento" => "numeric|required|min:0",
            "referencia" => "string|required",
            "rfc" => "string|required",
            "tipo_xml" => "string|required",
            "ejercicio" => "numeric|required|min:2000",

            "xml" => "required",
            "nombre_xml" => "string|required",
        ];
        $this->validate($request, $rules);

        $proveedor = $request->input('proveedor');
        $sociedad = $request->input('sociedad');
        $documento = $request->input('documento');
        $referencia = $request->input('referencia');
        $tipo_comprobante = $request->input('tipo_xml');
        $ejercicio = $request->input('ejercicio');

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

        if (!is_array($xmlData = $this->readXMLData($xmlString))) {

            return [
                "code" => 400,
                "status" => "error",
                "message" => $xmlData
            ];
        }

        if ($xmlData['tipo_comprobante'] != $tipo_comprobante) {

            return [
                "code" => 400,
                "status" => "error",
                "message" => "El tipo de comprobante no coincide: " . $tipo_comprobante
            ];
        }

        $cargaXml = [
            $documento,
            $referencia,
            $tipo_comprobante,
            $ejercicio,
            "",
            $xmlString,
        ];

        $resultado = $this->guardarRegistro($cargaXml, $proveedor, $sociedad, $xmlData['forma_pago']);
        $data = [
            "code" => 200,
            "status" => "success",
            "message" => "Archivo cargado correctamente.",
            "data" => $xmlData
        ];
        return response()->json($data, $data["code"]);
    }

    public function cargaZip(Request $request)
    {

        $rules = [
            "zip" => "required",
            "nombre_zip" => "required|string",
            "proveedor" => "required|string",
            "sociedad" => "required|string",
        ];
        $this->validate($request, $rules);

        $proveedor = $request->input('proveedor');
        $sociedad = $request->input('sociedad');

        // Save zip File
        $fileName = $request->input('nombre_zip');
        $base64String = $request->input("zip");
        $folderPath = storage_path("app/public/zip_files/" . auth()->user()->id . "/");
        if (!File::exists($folderPath)) {
            \File::makeDirectory($folderPath, 0755, true);
        }
        $file = $folderPath . $fileName;
        // $zip_Array = explode(";base64,", $base64String);
        // $zip_contents = base64_decode($zip_Array[0]);
        $zip_contents = file_get_contents("compress.zlib://data://text/plain;base64," . $base64String);
        file_put_contents($file, $zip_contents); //guarda el archivo zip en storage

        $data = $this->getCsvContent($folderPath, $fileName);

        if ($data["code"] == 200) {
            $registros = $data['csv_content'];

            for ($i = 0; $i < count($registros); $i++) {

                //ignora los XMl que no tuvieron contenido
                if (end($registros[$i]) == null) {
                    $registros[$i][] = 'No se encontró el XML en el archivo.';
                    $xmlValidos[] = $registros[$i];
                    continue;
                }
                if ((count($registros[$i]) != 6)) {
                    $registros[$i][] = 'La fila en archivo index no tiene un formato válido.';
                    $xmlValidos[] = $registros[$i];
                    continue;
                }
                if ($registros[$i][2] != 'I') {
                    $registros[$i][] = 'No es un tipo XML de Ingreso';
                    $xmlValidos[] = $registros[$i];
                    continue;
                }

                $resultado = $this->guardarRegistro($registros[$i], $proveedor, $sociedad);
                $xmlValidos[] = $resultado;
            }

            return [
                "code" => 200,
                "status" => "success",
                "array" => $xmlValidos,
            ];
        }

        //$data = $this->upzipFiles($folderPath, $fileName);

        return response()->json($data, $data["code"]);
    }
}
