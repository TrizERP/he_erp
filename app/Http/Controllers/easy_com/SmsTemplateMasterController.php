<?php

namespace App\Http\Controllers\easy_com;

use App\Http\Controllers\Controller;
use App\Models\easy_com\SmsTemplateMaster;
use Illuminate\Http\Request;

class SmsTemplateMasterController extends Controller
{
     public function index()
    {
        $data['data'] = SmsTemplateMaster::where('sub_institute_id', session()->get('sub_institute_id'))->orderBy('sort_order')->get();
        return view('easy_comm.show_sms_template_master', compact('data'));
    }

    public function create()
    {
        return view('easy_comm.add_sms_template_master');
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_name' => 'required',
            'template_content' => 'required',
            'sort_order' => 'required|numeric'
        ]);

        SmsTemplateMaster::create([
            'template_name' => $request->template_name,
            'template_id' => $request->template_id,
            'sender_id' => $request->sender_id,
            'template_content' => $request->template_content,
            'sort_order' => $request->sort_order,
            'status' => $request->status,
            'sub_institute_id' => session()->get('sub_institute_id')
        ]);

        return redirect()->route('sms_template_master.index')
            ->with('success', 'SMS template added successfully');
    }

    public function edit($id)
    {
        $data = SmsTemplateMaster::findOrFail($id);
        return view('easy_comm.edit_sms_template_master', compact('data'));
    }

    public function update(Request $request, $id)
    {
        SmsTemplateMaster::where('id', $id)->update([
            'template_name' => $request->template_name,
            'template_id' => $request->template_id,
            'sender_id' => $request->sender_id,
            'template_content' => $request->template_content,
            'sort_order' => $request->sort_order,
            'status' => $request->status,
            'sub_institute_id' => session()->get('sub_institute_id')
        ]);

        return redirect()->route('sms_template_master.index')
            ->with('success', 'SMS template updated successfully');
    }

    public function destroy($id)
    {
        SmsTemplateMaster::where('id', $id)->delete();

        return redirect()->route('sms_template_master.index')
            ->with('success', 'SMS template deleted successfully');
    }
}
