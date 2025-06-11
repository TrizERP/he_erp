<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use function App\Helpers\is_mobile;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;


class oldDocumentTransfer extends Controller
{
    private function convertDataToUtf8($data)
    {
        if (is_array($data)) {
            // Convert each element of the array to UTF-8 encoding
            foreach ($data as $key => $value) {
                $data[$key] = $this->convertDataToUtf8($value);
            }
        } elseif (is_object($data)) {
            // Convert each property of the object to UTF-8 encoding
            foreach ($data as $key => $value) {
                $data->$key = $this->convertDataToUtf8($value);
            }
        } elseif (is_string($data)) {
            // Attempt to detect the character encoding
            $encoding = mb_detect_encoding($data, mb_detect_order(), true);
            
            // Specify the input encoding (use ISO-8859-1 as a fallback)
            $inputEncoding = $encoding ?: 'ISO-8859-1';
            
            // Convert string to UTF-8 encoding
            $data = mb_convert_encoding($data, 'UTF-8', $inputEncoding);
        }
        
        return $data;
    }
    


    public function storeImagesToDigitalOcean(Request $request)
    {
     
        // $directory = public_path('images');
        if($request->type=="storage"){
            $directory = storage_path('app/public/'.$request->directory);
        }else{
            $directory = public_path($request->directory);
        }
        // $directory = $request->directory;
        $i=0;

        if (File::exists($directory)) {
            $files = File::files($directory);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                $filePath = $file->getPathname();
                $stored = Storage::disk('digitalocean')->putFileAs('public/'.$request->digi_directory.'/', $filePath, $filename, 'public');
                if ($stored) {
                    $i++;
                }
            }
        } else {
            echo "Directory does not exist.";
        }

        $message = "Failed";
        if($i>0){
            $message="stored";
        }
        return $message;
    }

    public function ConvertBinaryData(Request $request)
    {
        // $url = $request->binaryData;  // URL to fetch the file (or binary data if locally available)
        // $contentType = $request->contentTypr;  // Content type (e.g., application/pdf)
        // $fileName = 'emp_'.$request->empId.'_'.$request->fileName;  // File name (e.g., file.pdf)

        // // Fetch the file content from the given URL
        // $fileContents = file_get_contents($url);
        $filePath = $_SERVER['DOCUMENT_ROOT'].'converted_json.json';

        // Check if the file exists
          if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Read the file content
        $jsonData = file_get_contents($filePath);

        // Decode the JSON data
        // $data = json_decode($jsonData, true);
        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'JSON decoding error: ' . json_last_error_msg()], 500);
        }

        // Return the data as a JSON response
        $dataArray = json_decode($jsonData, true);
        // echo "<Pre>";print_r($dataArray);exit;

        // foreach ($dataArray as $key => $value) {
        //     $binaryData = $value['binaryData'];  // URL to fetch the file (or binary data if locally available)
        //     $contentType = $value['contentType'];  // Content type (e.g., application/pdf)
        //     $fileName = 'emp_'.$value['empId'].'_'.$value['fileName'];  // File name (e.g., file.pdf)
        //     // echo "<pre>";print_r($binaryData);exit;
        //     if ($binaryData) {
        //         // Handle raw binary data directly
        //         $contentType = $value['contentType'] ?? 'application/octet-stream'; // Default content type if not provided
        //         $fileName = 'emp_' . $value['empId'] . '_' . $value['fileName'];  // File name (e.g., file.pdf)

        //         // Define the file path where the binary data will be processed or saved
        //         $filePath = public_path('old_to_new/doc_transfer/') . $fileName;

        //         // Save the binary data to the file (if needed) or process it directly
        //         // If you want to display the data directly, comment out the file writing part
        //         file_put_contents($filePath, $binaryData);
                

        //         // Output file path for debugging
        //         echo "<pre>"; print_r("File saved to: " . $filePath); exit;

        //         // To directly display the binary data in the browser
        //         // Uncomment the following lines if you want to directly display the binary data
        //         /*
        //         header('Content-Type: ' . $contentType);
        //         header('Content-Disposition: inline; filename="' . $fileName . '"');
        //         echo $binaryData;
        //         exit;
        //         */
        //     } else {
        //         return response()->json(['error' => 'No binary data found in JSON.'], 400);
        //     }
        //     echo "<pre>";print_r($fileName);exit;
        // }
        $storedFiles=[];
        $message='Please wait we are proccessing!';
        foreach ($dataArray as $value) {
            $fileSize=$value['size'];

            if($fileSize==0){
                $blobData = isset($value['mediumBlob']) ? $value['mediumBlob'] : null;
            }else{
                $blobData = isset($value['mediumBlob']) ? base64_decode($value['mediumBlob'],true) : null;
            }
            // echo "<pre>";print_r($blobData);exit;
            $empId=$value['empId'];
            $filename='emp_'.$empId.'_'.$value['fileName'];
            // return response($blobData, 200)
            // ->header('Content-Type', 'application/pdf')
            // ->header('Content-Disposition', 'inline; filename="document.pdf"');
            if ($blobData !== false) {
                // Generate filename and path
                $empId = $value['empId'];
                $sub_institute_id = $value['sub_institute_id'];
                $docTitle = $value['docTitle'];
                $sub_institute_id = $value['sub_institute_id'];

                $filename = $value['fileName'];

                $tempFilePath = tempnam(sys_get_temp_dir(), 'pdf');
                file_put_contents($tempFilePath, $blobData);
            
                // Step 3: Upload to DigitalOcean Spaces
                $storagePath = 'public/he_staff_document/' . $filename;
                $storedFiles[] = $storagePath;
                $disk = Storage::disk('digitalocean');
                if (Storage::disk('digitalocean')->exists($storagePath)) {
                    Storage::disk('digitalocean')->delete($storagePath);
                    // Store the file
                } 

                if($fileSize!=0){
                    $disk->put($storagePath, fopen($tempFilePath, 'r+'), 'public');
                }else{
                    // $fileContents = file_get_contents($blobData);

                    $fileContents = @file_get_contents($value['mediumBlob']); // The '@' suppresses warnings

                    if ($fileContents !== false) {
                        // Store the file in DigitalOcean Spaces
                       $disk=Storage::disk('digitalocean')->put($storagePath, $fileContents, 'public');
                    }
                }
                // Clean up temporary file
                unlink($tempFilePath);
                // store in database
                $checkData = DB::table('tbluser_staff_document_details')->where('user_id',$empId)->where('file',$filename)->where('sub_institute_id',$sub_institute_id)->get()->toArray();
                if($disk && empty($checkData)){
                    $insertData = [
                        "user_id"=>$empId,
                        // "document_type_id"=>$sub_institute_id,
                        "document_title"=>$docTitle,
                        "file"=>$filename,
                        "sub_institute_id"=>$sub_institute_id,
                        "created_at"=>now(),

                    ];
                    // echo "<pre>";print_r($insertData);exit;
                    $insert = DB::table('tbluser_staff_document_details')->insert($insertData);
                    $message='Stored Files';
                }
                $message = 'File stored successfully!';
            }else{
                $message = 'Blob Data not found!';
            }
        }
        // exit;

        return response()->json(['message' =>$message,'Total stored files'=>count($storedFiles), 'storedFiles' => $storedFiles]);
    }

}
