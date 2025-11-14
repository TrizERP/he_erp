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
     * Display a listing of theDB:table resource.
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
   /**
 * Show the form for creating a new resource.
 *
 * @param  Request  $request
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 * @return Response
 */
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
    $term_id = session()->get('term_id');

    // 1️⃣ Decide which table to use
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
    $STATUS_STR = ['Delivered', 'Failed', 'Other'];

    // 2️⃣ Fetch all pending records
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

    // 3️⃣ Update status from API
    $SMS_URL = 'https://login.smsforyou.biz/V2/http-dlr.php';
    $client = new \GuzzleHttp\Client();

    foreach ($alldata as $row) {
        try {
            $response = $client->post($SMS_URL, [
                'form_params' => [
                    'apikey' => 'sjpPjxJFc4rm3dfL',
                    'format' => 'json',
                    'id'     => $row->message_id,
                ],
                'verify' => false,
            ]);

            $result = json_decode($response->getBody());

            if (!empty($result->data)) {
                foreach ($result->data as $id) {
                    if ($id->status != 'Submitted') {
                        DB::table(str_replace(" as s", "", $tbl))
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

    // 4️⃣ Fetch updated data including Academic Section, Standard and Division
    $query = DB::table($tbl)
        ->join($join_tbl, $join)
        ->where('s.sub_institute_id', session()->get('sub_institute_id'))
        ->when($request->filled('from_date'), function ($query) use ($request) {
            $query->whereDate('s.created_on', '>=', $request->input('from_date'));
        })
        ->when($request->filled('to_date'), function ($query) use ($request) {
            $query->whereDate('s.created_on', '<=', $request->input('to_date'));
        })
        ->select(
            DB::raw(($request->input('tbl') == 'staff') ? "'' as enrollment_no" : 'u.enrollment_no'),
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
        );

    // Handle Academic Section, Standard and Division for students
    if ($request->input('tbl') != 'staff') {
        try {
            // Join with enrollment table to get academic section, standard and division
            $query->leftJoin('tblstudent_enrollment as e', function($join) {
                $join->on('u.id', '=', 'e.student_id')
                     ->on('s.syear', '=', 'e.syear');
            });
            
            // Join with academic_section table
            $query->leftJoin('academic_section as acad', 'e.grade_id', '=', 'acad.id');
            
            // Join with standard table
            $query->join('standard as std', function($join) use ($term_id) {
			    $join->on('e.standard_id', '=', 'std.id')
			         ->where('std.marking_period_id', '=', $term_id);
			});
            
            // Join with division table
            $query->leftJoin('division as div', 'e.section_id', '=', 'div.id');
            
            // Select academic section, standard name and division name, format as "Academic Section/Standard/Division"
            $query->addSelect(DB::raw("
                CASE 
                    WHEN acad.title IS NOT NULL AND std.name IS NOT NULL AND div.name IS NOT NULL THEN CONCAT(acad.title, '/', std.name, '/', div.name)
                    WHEN acad.title IS NOT NULL AND std.name IS NOT NULL THEN CONCAT(acad.title, '/', std.name)
                    WHEN std.name IS NOT NULL AND div.name IS NOT NULL THEN CONCAT(std.name, '/', div.name)
                    WHEN std.name IS NOT NULL THEN std.name
                    WHEN div.name IS NOT NULL THEN div.name
                    WHEN acad.title IS NOT NULL THEN acad.title
                    ELSE 'N/A'
                END as sem_div
            "));
            
        } catch (\Exception $e) {
            \Log::error("Error joining academic section/standard/division tables: " . $e->getMessage());
            // Fallback to student table columns if joins fail
            $query->addSelect(DB::raw("
                CASE 
                    WHEN u.sem_div IS NOT NULL THEN u.sem_div
                    WHEN u.semester IS NOT NULL AND u.division IS NOT NULL THEN CONCAT(u.semester, '/', u.division)
                    WHEN u.semester IS NOT NULL THEN u.semester
                    WHEN u.division IS NOT NULL THEN u.division
                    WHEN u.class IS NOT NULL THEN u.class
                    WHEN u.standard IS NOT NULL THEN u.standard
                    ELSE 'N/A'
                END as sem_div
            "));
        }
    } else {
        // For staff, use a simple fallback
        $query->addSelect(DB::raw("'N/A' as sem_div"));
    }

    $reportData = $query->orderBy('s.created_on', 'desc')->get();

    // 5️⃣ Prepare array for Blade
    foreach ($reportData as $id => $arr) {
        $responce_arr[$id]['sr.no']              = $id + 1;
        $responce_arr[$id]['enrollment_no']      = $arr->enrollment_no ?? '';
        $responce_arr[$id]['name']               = trim(($arr->first_name ?? '') . ' ' . ($arr->middle_name ?? '') . ' ' . ($arr->last_name ?? ''));
        $responce_arr[$id]['sem_div']            = $arr->sem_div ?? 'N/A';
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

    // Add debug logging to check the data
    \Log::info('SMS Report Data Sample:', $responce_arr[0] ?? []);

    return is_mobile($type, "easy_comm/send_sms_report/add", $responce_arr, "view");
}

/**
 * Helper function to determine the correct column name in division table
 */
private function getDivisionColumnName()
{
    try {
        $columns = DB::getSchemaBuilder()->getColumnListing('division');
        
        // Check for common column names in order of preference
        if (in_array('division_name', $columns)) {
            return 'division_name';
        } elseif (in_array('name', $columns)) {
            return 'name';
        } elseif (in_array('division', $columns)) {
            return 'division';
        } elseif (in_array('section_name', $columns)) {
            return 'section_name';
        } elseif (in_array('class_name', $columns)) {
            return 'class_name';
        } else {
            // Return the first string column that's not id
            foreach ($columns as $column) {
                if (!in_array($column, ['id', 'created_at', 'updated_at', 'sub_institute_id'])) {
                    return $column;
                }
            }
            return 'name'; // fallback
        }
    } catch (\Exception $e) {
        \Log::error("Error getting division column name: " . $e->getMessage());
        return 'name'; // fallback
    }
}



/**
 * ✅ Helper function to detect correct Sem/Div column
 */
private function getSemDivColumn($tableName)
{
    try {
        $columns = DB::getSchemaBuilder()->getColumnListing($tableName);

        // log to confirm what columns exist (debug)
        \Log::info("Columns in $tableName:", $columns);

        if (in_array('sem_div', $columns)) {
            return "u.sem_div";
        } elseif (in_array('semester', $columns) && in_array('division', $columns)) {
            return "CONCAT(u.semester, '/', u.division)";
        } elseif (in_array('semester', $columns)) {
            return "u.semester";
        } elseif (in_array('division', $columns)) {
            return "u.division";
        } elseif (in_array('class', $columns)) {
            return "u.class";
        } elseif (in_array('standard', $columns)) {
            return "u.standard";
        } elseif (in_array('standard_name', $columns)) {
            return "u.standard_name";
        } else {
            // fallback — prevents SQL error and keeps column blank
            return "''";
        }
    } catch (\Exception $e) {
        \Log::error("Error checking columns for table {$tableName}: " . $e->getMessage());
        return "''";
    }
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
