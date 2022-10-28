<?php

namespace App\Traits;

use Illuminate\Http\Request;
//use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use File;

trait XmlTrait
{
    public function readXMLData($base64String) {

        $xmlString = base64_decode($base64String);
        $xml = simplexml_load_string($xmlString);
        $ns = $xml->getNamespaces(true);
        $xml->registerXPathNamespace("c", $xml["cfdi"]);
        $xml->registerXPathNamespace("t", $xml["tfd"]);

        $comprobante = array();
        $emisor = array();
        $receptor = array();

        //EMPIEZO A LEER LA INFORMACION DEL CFDI E IMPRIMIRLA 
        foreach ($xml->xpath("//cfdi:Comprobante") as $cfdiComprobante) {
            $json = json_encode($cfdiComprobante);
            $xmlArray = json_decode($json, true);
            $comprobante = $xmlArray["@attributes"];
        }

        foreach ($xml->xpath("//cfdi:Comprobante//cfdi:Emisor") as $cfdiEmisor){ 
            $json = json_encode($cfdiEmisor);
            $xmlArray = json_decode($json, true);
            $emisor = $xmlArray["@attributes"];
         } 

         foreach ($xml->xpath("//cfdi:Comprobante//cfdi:Receptor") as $cfdiReceptor){ 
            $json = json_encode($cfdiReceptor);
            $xmlArray = json_decode($json, true);
            $receptor = $xmlArray["@attributes"];
         } 

        if (is_array($comprobante)) {
            $data = [
                "code" => 200,
                "status" => "success",
                "data" => [
                    "tipo_comprobante" => $comprobante["TipoDeComprobante"],
                    "emisor" => $emisor["Rfc"],
                    "receptor" => $receptor["Rfc"],
                    "total" => $comprobante["Total"],
                ]
            ];
        } else {
            $data = [
                "code" => 400,
                "status" => "error",
                "message" => "Se ha producido un error al guardar.",
            ];
        }
        return $data;
    }

    public function upzipFiles($file)
    {
        //Unzip files
        $zip = new ZipArchive();

        try {
            //code...
            $zip->open($file);
            $upzipDestinationPath = storage_path("app/public/unzip_files/");

            if (!File::exists($upzipDestinationPath)) {
                \File::makeDirectory($upzipDestinationPath, 0755, true);
            }
            $zip->extractTo($upzipDestinationPath);
            $zip->close();

            return $data = [
                "code" => 200,
                "status" => "success",
                "message" => "Archivo descromprimido correctamente.",
            ];
        } catch (\Throwable $th) {
            return $data = [
                "code" => 400,
                "status" => "error",
                "message" => "Se ha producido un error al guardar. " . $th->getMessage(),
            ];
        }
    }

    public function saveNReadXML(Request $request)
    {
        $xmlString = base64_decode($request->input("xml"));
        Storage::disk("local")->put($request->input("nombre_xml"), $xmlString);
        $path = storage_path("app/" . $request->input("nombre_xml"));

        $xml = simplexml_load_file($path);
        $ns = $xml->getNamespaces(true);
        $xml->registerXPathNamespace("c", $ns["cfdi"]);
        $xml->registerXPathNamespace("t", $ns["tfd"]);

        $comprobante = array();
        $emisor = array();
        $receptor = array();

        //EMPIEZO A LEER LA INFORMACION DEL CFDI E IMPRIMIRLA 
        foreach ($xml->xpath("//cfdi:Comprobante") as $cfdiComprobante) {
            $json = json_encode($cfdiComprobante);
            $xmlArray = json_decode($json, true);
            $comprobante = $xmlArray["@attributes"];
        }

        foreach ($xml->xpath("//cfdi:Comprobante//cfdi:Emisor") as $cfdiEmisor){ 
            $json = json_encode($cfdiEmisor);
            $xmlArray = json_decode($json, true);
            $emisor = $xmlArray["@attributes"];
         } 

         foreach ($xml->xpath("//cfdi:Comprobante//cfdi:Receptor") as $cfdiReceptor){ 
            $json = json_encode($cfdiReceptor);
            $xmlArray = json_decode($json, true);
            $receptor = $xmlArray["@attributes"];
         } 

        if (is_array($comprobante)) {
            $data = [
                "code" => 200,
                "status" => "success",
                "message" => "Creado satisfactoriamente",
                "comprobante" => $comprobante,
                "emisor" => $emisor,
                "receptor" => $receptor,
            ];
        } else {
            $data = [
                "code" => 400,
                "status" => "error",
                "message" => "Se ha producido un error al guardar.",
            ];
        }
        return response()->json($data, $data["code"]);
    }

    public static function fromBase64(string $base64File): UploadedFile
    {
        // Get file data base64 string
        $fileData = base64_decode(Arr::last(explode(",", $base64File)));

        // Create temp file and get its absolute path
        $tempFile = tmpfile();
        $tempFilePath = stream_get_meta_data($tempFile)["uri"];

        // Save file data in file
        file_put_contents($tempFilePath, $fileData);

        $tempFileObject = new File($tempFilePath);
        $file = new UploadedFile(
            $tempFileObject->getPathname(),
            $tempFileObject->getFilename(),
            $tempFileObject->getMimeType(),
            0,
            true // Mark it as test, since the file isn"t from real HTTP POST.
        );

        // Close this file after response is sent.
        // Closing the file will cause to remove it from temp director!
        app()->terminating(function () use ($tempFile) {
            fclose($tempFile);
        });

        // return UploadedFile object
        return $file;
    }

}