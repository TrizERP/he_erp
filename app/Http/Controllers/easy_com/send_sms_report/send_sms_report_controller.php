<?php

namespace App\Http\Controllers\easy_com\send_sms_report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function App\Helpers\is_mobile;
use GuzzleHttp\Client;
use App\Models\easy_com\manage_sms_api\manage_sms_api;

class send_sms_report_controller extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (session()->has('data')) {
            // check if it exists
            $data_arr = session('data'); // to retrieve value
            if (isset($data_arr['message'])) {
                $data['message'] = $data_arr['message'];
            }
        }

        $data['data'] = [];
        $type = $request->input('type');

        return is_mobile($type, "easy_comm/send_sms_report/show", $data, "view");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  Request  $request
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return Response
     */
    
    public function create(Request $request)
    {
        $join_tbl = "";
        $join = [];
        $responce_arr = [];
    
        // Table selection based on staff or parent
        if ($request->input('tbl') == 'staff') {
            $tbl = "sms_sent_staff as s";
            $join_tbl = "tbluser as u";
            $join = [
                's.staff_id'         => 'u.id',
                's.sub_institute_id' => 'u.sub_institute_id',
            ];
        } else {
            $tbl = "sms_sent_parents as s";
            $join_tbl = "tblstudent as u";
            $join = [
                's.student_id'       => 'u.id',
                's.sub_institute_id' => 'u.sub_institute_id',
            ];
        }
    
        $type = $request->input('type');
        $STATUS_STR = ['Delivered', 'Failed', 'Other']; // ,'INVALID','REJECTD','UNDELIV'
    
        // Step 1: Fetch records with blank/pending MESSAGE_RESPONSE
        $alldata = DB::table($tbl)
            ->join($join_tbl, $join)
            ->where('s.sub_institute_id', session()->get('sub_institute_id'))
            ->whereNotNull('s.message_id')
            ->where(function ($q) use ($STATUS_STR) {
                $q->whereNotIn('s.message_response', $STATUS_STR)
                  ->orWhereNull('s.message_response');
            });
    
        if ($request->filled('from_date')) {
            $alldata->whereDate('s.created_on', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $alldata->whereDate('s.created_on', '<=', $request->input('to_date'));
        }
    
        $alldata = $alldata->groupBy('s.message_id')->get();
    
        // Step 2: Call API and update records
        $SMS_URL = 'https://login.smsforyou.biz/V2/http-dlr.php';
        $client = new Client();
    
        foreach ($alldata as $row) {
            try {
                $response = $client->post($SMS_URL, [
                    'form_params' => [
                        'apikey' => 'sjpPjxJFc4rm3dfL',
                        'format' => 'json',
                        'id'     => $row->message_id,
                    ],
                    'verify' => false, // ignore SSL
                ]);
    
                $result = json_decode($response->getBody());
    
                if (!empty($result->data)) {
                    foreach ($result->data as $id) {
                        if ($id->status != 'Submitted') {
                            DB::table(str_replace(" as s", "", $tbl)) // remove alias for update
                                ->where('message_id', $id->id)
                                ->whereNull('message_response')
                                ->update([
                                    'message_response'    => $id->status,
                                    'delivered_datetime'  => $id->delivered_date,
                                    'message_bill_credit' => $id->status_desc,
                                ]);
                        }
                    }
                }
    
            } catch (\Exception $e) {
                \Log::error("SMS Status Update Error: " . $e->getMessage());
            }
        }
    
        // Step 3: Fetch updated dataset
        $reportData = DB::table($tbl)
        ->join($join_tbl, $join)
        ->where('s.sub_institute_id', session()->get('sub_institute_id'))
        ->when($request->filled('from_date'), function ($query) use ($request) {
            $query->whereDate('s.created_on', '>=', $request->input('from_date'));
        })
        ->when($request->filled('to_date'), function ($query) use ($request) {
            $query->whereDate('s.created_on', '<=', $request->input('to_date'));
        })
        ->select(
            'u.enrollment_no',
            'u.first_name',
            'u.middle_name',
            'u.last_name',
            's.syear',
            's.sms_no',
            's.sms_text',
            's.module_name',
            's.message_id',
            's.message_response',
            's.message_bill_credit',
            's.delivered_datetime',
            DB::raw("DATE_FORMAT(s.created_on, '%d-%m-%Y') as sent_date")
        )
        ->orderBy('s.created_on','desc')
        ->get();

        // Step 4: Prepare response array
        foreach ($reportData as $id => $arr) {
            $responce_arr[$id]['sr.no']              = $id + 1;
            $responce_arr[$id]['enrollment_no']      = $arr->enrollment_no ?? '';
            $responce_arr[$id]['name']               = ($arr->first_name ?? '') . ' ' . ($arr->middle_name ?? '') . ' ' . ($arr->last_name ?? '');
            $responce_arr[$id]['syear']              = $arr->syear ?? '';
            $responce_arr[$id]['sms_no']             = $arr->sms_no ?? '';
            $responce_arr[$id]['sms_text']           = $arr->sms_text ?? '';
            $responce_arr[$id]['module_name']        = $arr->module_name ?? '';
            $responce_arr[$id]['message_id']         = $arr->message_id ?? '';
            $responce_arr[$id]['message_response']   = $arr->message_response ?? '';
            $responce_arr[$id]['message_bill_credit']= $arr->message_bill_credit ?? '';
            $responce_arr[$id]['delivered_datetime'] = $arr->delivered_datetime ?? '';
            $responce_arr[$id]['sent_date']          = $arr->sent_date ?? '';
        }
    
        // Step 5: Return to view/mobile
        return is_mobile($type, "easy_comm/send_sms_report/add", $responce_arr, "view");
    }    

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return void
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return void
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return void
     */
    public function destroy($id)
    {
        //
    }

}
