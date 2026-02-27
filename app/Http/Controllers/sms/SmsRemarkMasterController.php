<?php

namespace App\Http\Controllers\sms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SmsRemarkMasterController extends Controller
{
     public function index()
    {
        $data['data'] = \App\Models\sms\SmsRemarkMaster::orderBy('sort_order')->get();
        return view('sms.show_sms_remark_master', compact('data'));
    }

    public function create()
    {
        return view('sms.add_sms_remark_master');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'sort_order' => 'required|numeric'
        ]);

        \App\Models\sms\SmsRemarkMaster::create([
            'title' => $request->title,
            'sort_order' => $request->sort_order,
            'remark_status' => $request->result_status,
            'sub_institute_id' => session()->get('sub_institute_id')
        ]);

        return redirect()->route('sms_remark_master.index')
            ->with('success', 'SMS Remark added successfully');
    }

    public function edit($id)
    {
        $data = \App\Models\sms\SmsRemarkMaster::findOrFail($id);
        return view('sms.edit_sms_remark_master', compact('data'));
    }

    public function update(Request $request, $id)
    {
        \App\Models\sms\SmsRemarkMaster::where('id', $id)->update([
            'title' => $request->title,
            'sort_order' => $request->sort_order,
            'remark_status' => $request->result_status,
            'sub_institute_id' => session()->get('sub_institute_id')
        ]);

        return redirect()->route('sms_remark_master.index')
            ->with('success', 'SMS Remark updated successfully');
    }

    public function destroy($id)
    {
        \App\Models\sms\SmsRemarkMaster::where('id', $id)->delete();

        return redirect()->route('sms_remark_master.index')
            ->with('success', 'SMS Remark deleted successfully');
    }
}
