@php
    $e = $a['enquiry'];
    $f = $a['form'] ?? [];
    $r = $a['registration'] ?? [];
    $ref = 'ADM-' . str_pad((string) ($r['id'] ?? 0), 6, '0', STR_PAD_LEFT);
@endphp
<table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;color:#222">
    <tr>
        <td style="width:90px;vertical-align:top">@if ($header['logo'])<img src="{{ $header['logo'] }}" alt="" style="max-width:80px;max-height:80px">@endif</td>
        <td style="text-align:center;padding-bottom:8px">
            @forelse ($header['lines'] as $i => $line)
                <div style="{{ $i === 0 ? 'font-size:18px;font-weight:bold' : 'font-size:12px' }}">{{ $line }}</div>
            @empty
                <div style="font-size:18px;font-weight:bold">Admission Fee Receipt</div>
            @endforelse
        </td>
        <td style="width:90px"></td>
    </tr>
    <tr><td colspan="3" style="border-top:2px solid #222;border-bottom:1px solid #999;text-align:center;padding:6px;font-weight:bold;letter-spacing:1px">ADMISSION FEE RECEIPT</td></tr>
</table>

<table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;color:#222;margin-top:12px">
    <tr><td style="padding:5px 0;width:50%"><b>Receipt ref.:</b> {{ $ref }}</td>
        <td style="padding:5px 0;text-align:right"><b>Date:</b> {{ ! empty($fee['date_of_payment']) ? \Carbon\Carbon::parse($fee['date_of_payment'])->format('d M Y') : '-' }}</td></tr>
    <tr><td colspan="2" style="padding:5px 0"><b>Received from:</b> {{ $name }}@if (! empty($e['father_name'])) <span style="color:#555">(Parent/Guardian: {{ $e['father_name'] }})</span>@endif</td></tr>
    <tr><td style="padding:5px 0"><b>Programme:</b> {{ $standard ?? '-' }}</td>
        <td style="padding:5px 0;text-align:right"><b>Academic year:</b> {{ $e['syear'] ?? '-' }}</td></tr>
    <tr><td style="padding:5px 0"><b>Enquiry no.:</b> {{ $e['enquiry_no'] ?? '-' }}</td>
        <td style="padding:5px 0;text-align:right"><b>Form no.:</b> {{ $f['form_no'] ?? '-' }}@if (! empty($f['registration_no'])) &nbsp; <b>Reg. no.:</b> {{ $f['registration_no'] }}@endif</td></tr>
</table>

<table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;color:#222;margin-top:14px">
    <tr style="background:#f1f3f5"><th style="border:1px solid #999;padding:7px;text-align:left">Particulars</th><th style="border:1px solid #999;padding:7px;text-align:left">Payment mode</th><th style="border:1px solid #999;padding:7px;text-align:right">Amount</th></tr>
    <tr>
        <td style="border:1px solid #999;padding:7px">Admission fee</td>
        <td style="border:1px solid #999;padding:7px">{{ $fee['payment_mode'] ?: '-' }}@if (! empty($r['bank_name'])) - {{ $r['bank_name'] }}@endif @if (! empty($r['cheque_no'])) (Cheque {{ $r['cheque_no'] }})@endif</td>
        <td style="border:1px solid #999;padding:7px;text-align:right">{{ number_format($fee['paid'], 2) }}</td>
    </tr>
    <tr><td colspan="2" style="border:1px solid #999;padding:7px;text-align:right"><b>Total received</b></td>
        <td style="border:1px solid #999;padding:7px;text-align:right"><b>{{ number_format($fee['paid'], 2) }}</b></td></tr>
    @if ($fee['demand'] > 0)
        <tr><td colspan="2" style="border:1px solid #999;padding:7px;text-align:right">Fee due as per fee structure</td><td style="border:1px solid #999;padding:7px;text-align:right">{{ number_format($fee['demand'], 2) }}</td></tr>
        <tr><td colspan="2" style="border:1px solid #999;padding:7px;text-align:right">Balance</td><td style="border:1px solid #999;padding:7px;text-align:right">{{ number_format($fee['balance'], 2) }}</td></tr>
    @endif
</table>

<p style="font-family:Arial,sans-serif;font-size:12px;color:#555;margin-top:18px">
    This is a computer-generated copy of the payment recorded on the admission registration record. Printed on {{ now()->format('d M Y H:i') }}.
</p>
<table style="width:100%;font-family:Arial,sans-serif;font-size:13px;margin-top:34px"><tr><td>Parent's signature</td><td style="text-align:right">Authorised signatory</td></tr></table>
