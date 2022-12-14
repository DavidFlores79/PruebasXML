<?php

namespace App\Traits;

use App\Models\XmlCarga;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use File;

trait XmlTrait
{
    public function readXMLData($xmlString)
    {
        try {
            $xml = simplexml_load_string($xmlString);
            $ns = $xml->getNamespaces(true);
            $xml->registerXPathNamespace("c", $ns["cfdi"]);
            $xml->registerXPathNamespace("t", $ns["tfd"]);
        } catch (\Throwable $th) {
            return $this->returnError("Error asignar namespaces", $th);
        }

        $datos = array();
        $comprobante = array();
        $emisor = array();
        $receptor = array();
        $fecha_timbrado = array();
        $impuestos = array();
        $pago10Pago = array();
        $docto_relacionado = array();

        try {
            //EMPIEZO A LEER LA INFORMACION DEL CFDI E IMPRIMIRLA 
            foreach ($xml->xpath("//cfdi:Comprobante") as $cfdiComprobante) {
                $json = json_encode($cfdiComprobante);
                $xmlArray = json_decode($json, true);
                $comprobante = $xmlArray["@attributes"];
            }

            foreach ($xml->xpath("//cfdi:Comprobante//cfdi:Emisor") as $cfdiEmisor) {
                $json = json_encode($cfdiEmisor);
                $xmlArray = json_decode($json, true);
                $emisor = $xmlArray["@attributes"];
            }

            foreach ($xml->xpath("//cfdi:Comprobante//cfdi:Receptor") as $cfdiReceptor) {
                $json = json_encode($cfdiReceptor);
                $xmlArray = json_decode($json, true);
                $receptor = $xmlArray["@attributes"];
            }

            foreach ($xml->xpath('//t:TimbreFiscalDigital') as $tfdTimbreFiscal) {
                $json = json_encode($tfdTimbreFiscal);
                $xmlArray = json_decode($json, true);
                $tfd = $xmlArray["@attributes"];
                $fecha_timbrado = new DateTime($tfd['FechaTimbrado']);
            }
            
            $serie = (isset($comprobante["Serie"])) ? $comprobante["Serie"]: ''; //algunos XML no traen serie

            $datos = [
                "serie" => $serie,
                "folio" => $comprobante["Folio"],
                "fecha_timbrado" => $fecha_timbrado->format('Y-m-d'),
                "hora_timbrado" => $fecha_timbrado->format('H:i:s'),
                "folio_fiscal" => $tfd["UUID"],
                "tipo_comprobante" => $comprobante["TipoDeComprobante"],
                "emisor" => $emisor["Rfc"],
                "receptor" => $receptor["Rfc"],
            ];

            if (($comprobante["TipoDeComprobante"] == "I")) {

                foreach ($xml->xpath("//cfdi:Impuestos") as $cfdiImpuestos) {
                    $json = json_encode($cfdiImpuestos);
                    $xmlArrayImpuestos = json_decode($json, true);
                }
                $impuestos = $xmlArrayImpuestos["@attributes"];    
                
                $datos['importe_iva'] = (isset($impuestos["TotalImpuestosTrasladados"])) ? $impuestos["TotalImpuestosTrasladados"]: '';
                $datos['importe'] = $comprobante["Total"];
                $datos['forma_pago'] = $comprobante["FormaPago"];
                $datos['metodo_pago'] = $comprobante["MetodoPago"];
            } else if (($comprobante["TipoDeComprobante"] == "P")) {

                $xml->registerXPathNamespace("p", $ns["pago10"]);

                foreach ($xml->xpath('//p:Pago') as $pago10Pago) {
                    $json = json_encode($pago10Pago);
                    $xmlArray = json_decode($json, true);
                    $pago10Pago = $xmlArray["@attributes"];
                    $datos['importe'] = $pago10Pago["Monto"];
                    $datos['forma_pago'] = $pago10Pago["FormaDePagoP"];
                }
                foreach ($xml->xpath('//p:Pago//p:DoctoRelacionado') as $docto_relacionado) {
                    $json = json_encode($docto_relacionado);
                    $xmlArray = json_decode($json, true);
                    $docto_relacionado = $xmlArray["@attributes"];
                    $datos['metodo_pago'] = $docto_relacionado["MetodoDePagoDR"];
                }
            } else {
                throw new Exception('Tipo de Comprobante XML no v??lido.');
            }

            return $datos;

        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function upzipFiles($folderPath, $fileName)
    {
        //Unzip files
        $zip = new ZipArchive();

        try {
            //code...
            $zip->open($folderPath . $fileName);
            $upzipDestinationPath = storage_path("app/public/unzip_files/" . $fileName);

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
            return $this->returnError("Error al descomprimir.", $th);
        }
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

    public function getCsvContent(string $folderPath, string $fileName)
    {
        $file = $folderPath . $fileName;
        try {
            $zip = new ZipArchive;
            if(!$zip->open($file)) throw new Exception('No se encontr?? el archivo.');

            //Contenido del archivo index.csv
            if($zip->getFromName('index.csv')) {
                $lines = explode(PHP_EOL, $zip->getFromName('index.csv'));
            } else if ($zip->getFromName('index.txt')) {
                $lines = explode(PHP_EOL, $zip->getFromName('index.txt'));
            } else {
                throw new Exception('S??lo se permiten archivos "index.csv" o "index.txt".');
            }
            $csvContent = array();

            foreach ($lines as $key => $line) {
                if ($line != null) {
                    $csvContent[] = str_getcsv($line);
                    $fileName = end($csvContent[$key]);
                    $csvContent[$key][] = $this->getXmlContentFromName($fileName, $zip);
                }
            }

            return $data = [
                "code" => 200,
                "status" => "success",
                "csv_content" => $csvContent,
            ];
        } catch (\Throwable $th) {
            return $this->returnError("Error en archivo index. \n", $th);
        }
    }

    public function getXmlContentFromName(string $fileName, ZipArchive $zip)
    {
        $name = substr($fileName, 0, strrpos($fileName, '.'));

        for ($id = 0; $id < $zip->numFiles; $id++) {
            if (str_contains($zip->getNameIndex($id), $name)) {
                return $zip->getFromIndex($id);
            }
        }

        return null;
    }

    public function guardarRegistro( $registro, $proveedor, $sociedad, $forma_pago = null )
    {
        $cargaXml = [
            'documento' => $registro[0],
            'referencia' => $registro[1],
            'tipo_xml' => $registro[2],
            'ejercicio' => $registro[3],
            'archivo' => $registro[4],
            'xml' => $registro[5],
            'proveedor' => $proveedor,
            'sociedad' => $sociedad,
            'forma_pago' => $forma_pago,
        ];

        $result = XmlCarga::where('documento', $cargaXml['documento'])->first();

        if(empty($result)) {
            if(is_object(XmlCarga::create($cargaXml))) {
                $registro[] = 'Ok';
                return $registro;
            }
        } else {
            $registro[] = 'El documento '.$cargaXml['documento'].' ya existe en BD.';
            return $registro;
        }
        

        
        return XmlCarga::create($cargaXml);
    }

    public function getFilesInZip($zip)
    {
        $filesInZip = [];
        for ($id = 0; $id < $zip->numFiles; $id++) {

            $file = [
                "nombre" => $zip->getNameIndex($id),
                "contenido" => $zip->getFromIndex($id)
            ];
            array_push($filesInZip, $file);
        }
        return $filesInZip;
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

        foreach ($xml->xpath("//cfdi:Comprobante//cfdi:Emisor") as $cfdiEmisor) {
            $json = json_encode($cfdiEmisor);
            $xmlArray = json_decode($json, true);
            $emisor = $xmlArray["@attributes"];
        }

        foreach ($xml->xpath("//cfdi:Comprobante//cfdi:Receptor") as $cfdiReceptor) {
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


    function XMLNode($XMLNode, $ns)
    {
        //
        $nodes = array();
        $response = array();
        $attributes = array();

        // first item ?
        $_isfirst = true;

        // each namespace
        //  - xmlns:cfdi="http://www.sat.gob.mx/cfd/3"
        //  - xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital"
        foreach ($ns as $eachSpace) {
            //
            // each node
            foreach ($XMLNode->children($eachSpace) as $_tag => $_node) {
                //
                $_value = $this->XMLNode($_node, $ns);

                // exists $tag in $children?
                if (key_exists($_tag, $nodes)) {
                    if ($_isfirst) {
                        $tmp = $nodes[$_tag];
                        unset($nodes[$_tag]);
                        $nodes[] = $tmp;
                        $is_first = false;
                    }
                    $nodes[] = $_value;
                } else {
                    $nodes[$_tag] = $_value;
                }
            }
        }

        //
        $attributes = array_merge(
            $attributes,
            (array)current($XMLNode->attributes())
        );

        // nodes ?
        if (count($nodes)) {
            $response = array_merge(
                $response,
                $nodes
            );
        }

        // attributes ?
        if (count($attributes)) {
            $response = array_merge(
                $response,
                $attributes
            );
        }
    }

    public function returnError($message, $th)
    {
        return $data = [
            "code" => 400,
            "status" => "error",
            "message" => $message . " " . $th->getMessage(),
        ];
    }
}
