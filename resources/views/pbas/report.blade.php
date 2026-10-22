


<html>
<body align="center">
<p style="text-align: center;"><strong><span style="color: #000000; font-size: 16pt; font-family: 'Arial','sans-serif';">Annexure-B</span></strong></p>
<p style="text-align: center;"><strong><span style="color: #000000; font-size: 16pt; font-family: 'Arial','sans-serif';">Annual Self-Assessment form &nbsp;</span></strong></p>
<p style="text-align: center;"><strong><span style="color: #000000; font-size: 16pt; font-family: 'Arial','sans-serif';">Performance Based Appraisal System (PBAS)</span></strong></p>
<table style=" border-collapse: collapse; border-color=#FFFFFF" width="100%">
<tbody>

<tr style="height: 17.5pt;">
<td style=" padding: 0in 5.4pt 0in 5.4pt; height: 17.5pt;" width="660">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 14.0pt; font-family: 'Arial','sans-serif';">Shree Swami Atmanand Saraswati Institute of Technology</span></strong></p>
</td>
</tr>



<tr style="height: 22.0pt;">
<td style=" text-align: center; padding: 0in 5.4pt 0in 5.4pt; height: 22.0pt; font-size: 14pt; " width="100%">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Session/Year &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :From June {{ $syear }} TO May {{ $syear + 1 }} </span></strong></p>
</td>
</tr>

<tr style="height: 19.75pt;">
<td style="width: 495.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 19.75pt;" width="100%">
<table style=" border-collapse: collapse; border: none; margin-left: 6.75pt; margin-right: 6.75pt;" width="100%">
<tbody>
<tr style="height: .4in;">
<td style="width: 495.0pt; border: none; border-bottom: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" colspan="5" width="660">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">PART A</span></strong></p>
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">( GENERAL INFORMATION )</span></strong></p>
</td>
</tr>
<tr style="height: .4in;">
<td style="width: 26.4pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="35">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">1</span></p>
</td>

@foreach($report['profile'] ? [$report['profile']] : [] as $row)

<td style="width: 131.1pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="175">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Name of Faculty (in Block Letter)</span></p>
</td>
<td style="width: 337.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" colspan="3" width="450">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;{{ $row['facultyname'] ?? '' }}</span></p>
</td>
</tr>

<tr style="height: .4in;">
<td style="width: 26.4pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="35">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">2</span></p>
</td>
<td style="width: 255.1pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="175">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Father's Name / Mother's Name / Husband's Name</span></p>
</td>
<td style="width: 337.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" colspan="3" width="450">
			<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;{{ $row['fathername'] ?? '' }}</span></p>
</td>
</tr>

<tr style="height: .4in;">
<td style="width: 26.4pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="35">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">3</span></p>
</td>
<td style="width: 131.1pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="175">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Department</span></p>
</td>
<td style="width: 337.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" colspan="3" width="450">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;{{ $row['department'] ?? '' }}</span></p>
</td>
</tr>

<tr style="height: .4in;">
<td style="width: 26.4pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="35">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">4</span></p>
</td>
<td style="width: 131.1pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="175">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Date of Joining </span></p>
</td>
<td style="width: 337.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" colspan="3" width="450">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;{{ $row['dateofjoining'] ?? '' }}</span></p>
</td>
</tr>

<tr style="height: .4in;">
<td style="width: 26.4pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="35">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">5</span></p>
</td>
<td style="width: 131.1pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="175">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Present Designation, Pay Band &amp; AGP</span></p>
</td>
<td style="width: 337.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" colspan="3" width="450">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;{{ $row['designation'] ?? '' }}</span></p>
</td>
</tr>

<tr style="height: 44.95pt;">
<td style="width: 26.4pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 44.95pt;" width="35">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">6</span></p>
</td>
<td style="width: 131.1pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 34.95pt;" width="175">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Date of last Promotion/Increment</span></p>
</td>
<td style="width: 337.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 44.95pt;" colspan="3" width="450">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;{{ $row['promotiondate'] ?? '' }}</span></p>
</td>
</tr>

<tr>
<td style="width: 26.4pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 71.95pt;" width="35">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">7</span></p>
</td>
<td style="width: 131.1pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 71.95pt;" width="175">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Permanent Address </span></p>
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">(with Pincode, Telephone No. &amp; Email)</span></p>
</td>
<td style="width: 337.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 71.95pt;" colspan="3" width="450">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;{{ $row['address'] }}<br />
{{ $row['email'] }}<br />
{{ $row['mobile'] }}</span></p>
</td>
</tr>

<tr>
<td style="width: 26.4pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 44.05pt;" width="35">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">8</span></p>
</td>
<td style="width: 131.1pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 44.05pt;" width="175">
<p style="text-indent: -13.5pt; line-height: normal; text-autospace: none; margin: 0in 0in .0001pt 13.2pt;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">(a) </span><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Whether acquired any degree or fresh academic qualifications during the year </span></p>
</td>
<td style="width: 337.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 44.05pt;" colspan="3" width="450">
<p style="margin-bottom: .0001pt;  line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;{{ $row['extraqualification'] ?? '' }}</span></p>
</td>
</tr>

<tr>
<td style="width: 26.4pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 44.05pt;" width="35">
<p style="margin-bottom: .0001pt;  line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;</span></p>
</td>
<td style="width: 131.1pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 44.05pt;" width="175">
<p style="text-indent: -13.5pt; line-height: normal; text-autospace: none; margin: 0in 0in .0001pt 13.2pt;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">(b) </span><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">LWP during the year</span></p>
</td>
<td style="width: 337.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 44.05pt;" colspan="3" width="450">
			<p style=" tab-stops: 59.25pt;"><span style="font-size: 10.0pt; line-height: 115%; font-family: 'Arial','sans-serif';">&nbsp; {{ $row['lwp'] ?? '' }}</span></p>
</td>
</tr>


</tbody>
</table>
</td>
</tr>

</tbody>
</table>

<p style="margin-bottom: .0001pt;">&nbsp;</p>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="">
<td style="width: 495.0pt; border: none; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" colspan="18" width="660">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;">&nbsp;</p>
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-family: 'Arial','sans-serif';">PART B</span></strong></p>


</td>
</tr>

<tr style="">
<td style="width: 495.0pt; border: none; padding: 0in 5.4pt 0in 5.4pt; height: 12.1pt;" colspan="18" width="660">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 8.0pt; font-family: 'Arial','sans-serif';">&nbsp;</span></p>
</td>
</tr>

<tr style="">
<td style="width: 495.0pt; border: none; border-bottom: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" colspan="18" width="660">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-family: 'Arial','sans-serif';">CATEGORY-I TEACHING PROCESS</span></strong></p>
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-family: 'Arial','sans-serif';"></span></p>
</td>
</tr>

<tr style="">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 12.0pt; font-family: 'Arial','sans-serif';">1</span></strong></p>
</td>
<td style="width: 6.5in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" colspan="18" width="624">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Lectures, Seminars, Tutorials, Practical, Contact Hours (give semester-wise details, where necessary)</span></strong></p>
</td>
</tr>

<tr>
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Year</span></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sem.</span></p>
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">(odd/ even)</span></p>
</td>

<td style="width: 109.05pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="145">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Course/Subject</span></p>
</td>

<td style="width: 61.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="83">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Mode of Teaching*</span></p>
</td>

<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">No of classes per Week/ Total allotted</span></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">No. of Classes conducted</span></p>
</td>

<td style="width: 81.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="108">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">% of classes / practical taken as per documented record</span></p>
</td>


<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">GTU Result % </span></p>
</td>
<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">SSASIT Result % </span></p>
</td>
<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">GTU Rank </span></p>
</td>
<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">SSASIT Rank </span></p>
</td>


</tr>

@foreach($report['sections']['lectures']['rows'] as $row)


<tr>
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>


<td style="width: 59.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['year'] ?? '' }}</span></p>
</td>

<td style="width: 35.05pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="145">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"></span>{{ $row['semester'] ?? '' }}</p>
</td>

<td style="width: 125.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="83">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['subject'] ?? '' }}</span></p>
</td>

<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['mode_teaching'] ?? '' }}</span></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['class_alloted_weekly'] ?? '' }}</span></p>
</td>

<td style="width: 81.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="108">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['class_conducted_weekly'] ?? '' }}</span></p>
</td>


<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;"  width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['percent_class_parc'] ?? '' }}</span></p>
</td>

<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['gtu_percentage'] ?? '' }}</span></p>
</td>

<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['ssasit_percentage'] ?? '' }}</span></p>
</td>

<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="78">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['gtu_rank'] ?? '' }}</span></p>
</td>

<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="2" width="78">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['ssasit_rank'] ?? '' }}@endforeach</span></p>
</td>

</tr>



<tr style="height: .3in;">
<td style="width: 495.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="18" width="660">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">* Lecture (L), Seminar (S), Tutorial(T), Practical (P), <em></em>Contact Hours (C)</span></p>
</td>
</tr>

<tr style="height: 18.4pt;">
<td style="width: 11.55pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 18.4pt;" colspan="4" width="55">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;</span></p>
</td>
<td style="width: 388.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 18.4pt;" colspan="10" width="479">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;</span></p>
</td>
<td style="width: 94.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 18.4pt;" colspan="4" width="126">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>

<tr style="height: 26.5pt;">
<td style="width: 11.55pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 26.5pt;" colspan="4" width="55">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">(a)</span></strong></p>
</td>
<td style="width: 388.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 20.5pt;" colspan="10" width="479">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Result <!--(<strong>Max. 10 for 100% performance &amp; Proportionate score up to 80% performance, below which no score may be given)--></strong></span></p>
</td>
<td style="width: 94.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 20.5pt;" colspan="4" width="126">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $report['sections']['lectures']['total'] }}</center></span></p>
</td>
</tr>

<tr style="height: .3in;">
<td style="width: 11.55pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="4" width="55">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">(b)</span></strong></p>
</td>
<td style="width: 388.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 20.5pt;" colspan="10" width="479">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';padding: 2px;">Syllabus<!--Teaching Load in excess of AICTE norms (Max. score: 10)--></span></p>
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;</span></strong></p>

</td>
<td style="width: 94.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 20.5pt;" colspan="4" width="126">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $report['sections']['lectures']['total_b'] }}</center></span></p>
</td>
</tr>

</tbody>
</table>
<br/>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: 18.85pt;">
<td style="width: 41.55pt; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: 18.85pt;" width="55">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 12.0pt; font-family: 'Arial','sans-serif';">2</span></strong></p>
</td>
<td style="width: 453.45pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: 18.85pt;" colspan="5" width="605">
<p style="margin-bottom: .0001pt; text-align: justify; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Reading/Instructional material Prepared and additional knowledge resources provided.</span></strong></p>
</td>
</tr>
<tr>
<td style="width: 41.55pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt;" width="55">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr.</span></p>
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">No.</span></p>
</td>
<td style="width: 265.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="127">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Course/Subject</span></p>
</td>
<td style="width: 89.85pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Material Prepared</span></p>
</td>
<td style="width: 89.85pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Type of Material</span></p>
</td>
<td style="width: 80.25pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Prescribed/ Quantity</span></p>
</td>
<td style="width: 18.35pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;"  width="238">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Additional Resource Provided</span></p>
</td>
</tr>

@foreach($report['sections']['materials']['rows'] as $row)



<tr style="height: 16.6pt;">
<td style="width: 41.55pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 16.6pt;" width="55">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>

<td style="width: 95.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 16.6pt;" width="127">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-family: 'Arial','sans-serif';">{{ $row['subject'] ?? '' }}</span></p>
</td>

<td style="width: 89.85pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 16.6pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-family: 'Arial','sans-serif';">{{ $row['material_prepared'] ?? '' }}</span></p>
</td>

<td style="width: 90.25pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 16.6pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-family: 'Arial','sans-serif';">{{ $row['material_type'] ?? '' }}</span></p>
</td>

<td style="width: 90.25pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 16.6pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-family: 'Arial','sans-serif';">{{ $row['quantity'] ?? '' }}</span></p>
</td>

<td style="width: 178.35pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 16.6pt;" colspan="2" width="238">
<p style="text-align: center;"><span style="font-family: 'Arial','sans-serif';">{{ $row['resources'] ?? '' }}@endforeach</span></p>
</td>

</tr>



<tr style="height: 21.1pt;">
<td style="width: 399.95pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 21.1pt;" colspan="5" rowspan="2" width="533">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;</span></p>
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API score based on Preparation and imparting of knowledge /instruction as per curriculum &amp; syllabus enrichment by providing additional resources to students</span></p>
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <!--<strong>(Max. Score : 05)</strong>--></span></p>
</td>
<td style="width: 95.05pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 21.1pt;" width="127">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></p>
</td>
</tr>
<tr style="height: 31.0pt;">
<td style="width: 95.05pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 31.0pt;" width="127">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $report['sections']['materials']['total'] }}</span></p>
</td>
</tr>
<tr>
<td style="border: none;" width="55">&nbsp;</td>
<td style="border: none;" width="127">&nbsp;</td>
<td style="border: none;" width="120">&nbsp;</td>
<td style="border: none;" width="120">&nbsp;</td>
<td style="border: none;" width="111">&nbsp;</td>
<td style="border: none;" width="127">&nbsp;</td>
</tr>
</tbody>
</table>




  <table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>




<tr style="">
<td style="width: 495.0pt; border: none; border-bottom: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" colspan="18" width="660">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong></strong></p>
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-family: 'Arial','sans-serif';"><strong>CATEGORY II: DEPARTMENTAL ACTIVITIES/PERSONAL DEVELOPMENT ACTIVITIES</strong></span></p>
</td>
</tr>

<tr style="">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 12.0pt; font-family: 'Arial','sans-serif';">2.1.</span></strong></p>
</td>
<td style="width: 6.5in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .4in;" colspan="18" width="624">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Published Papers in Journals/Presented Paper in Conference</span></strong></p>

<!--<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Refereed Journal 5 Marks/Publication(Scopus Index), UGC Approved Journal 5 Marks/Publication , other Journals 2Marks/Publication and Paper Presentation in conference/Seminars - 2 Marks/Paper presentation</span></strong></p>-->
</td>
</tr>

<tr>
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></p>
</td>

<td style="width: 800.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="0" width="116">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Title with PageNos.</span></p>
</td>

<td style="width: 109.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="0" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">JournalType</span></p>

</td>

<td style="width: 109.05pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="145">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Journal</span></p>
</td>

<td style="width: 61.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="83">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">ISSN/ISBN No.</span></p>
</td>

<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Whether peer reviewed,impact factor,if any</span></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">No. of co-authors</span></p>
</td>

<td style="width: 29.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="158">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Whether you are the mainauthor</span></p>
</td>




<td style="width: 29.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;"  width="28">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score </span></p>
</td>


</tr>

@foreach($report['sections']['papers']['rows'] as $row)


<tr>
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>


<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;"  width="116">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['title'] ?? '' }}</span></p>
</td>

<td style="width: 109.05pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="145">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"></span>{{ $row['published_in'] ?? '' }}</p>
</td>

<td style="width: 61.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="83">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['journal_detail'] ?? '' }}</span></p>
</td>

<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['isbn_no'] ?? '' }}</span></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['reviewed'] ?? '' }}</span></p>
</td>

<td style="width: 81.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="158">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['co_author'] ?? '' }}</span></p>
</td>


<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="78">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['main_author'] ?? '' }}</span></p>
</td>



<td style="width: 58.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="28">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['mark'] ?? '' }}@endforeach</span></p>
</td>

</tr>





<!--<tr style="height: 18.4pt;">
<td style="width: 41.55pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 18.4pt;" colspan="8" width="55">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><strong>Total</strong></span></p>
</td>
<td style="width: 358.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 18.4pt;" colspan="10" width="479">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $report['sections']['papers']['total'] }}</center></span></p>
</td>

</tr>-->

</tbody>
</table>
</br>
<!--
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: 18.85pt;">
<td style="width: .5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: 18.85pt;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 12.0pt; font-family: 'Arial','sans-serif';">4</span></strong></p>
</td>
<td style="width: 459.0pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: 18.85pt;" colspan="4" width="612">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Examination Duties Assigned and Performed</span></strong></p>
</td>
</tr>
<tr>
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr No.</span></strong></p>
</td>
<td style="width: 155.9pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="208">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Type of Examination Duties</span></strong></p>
</td>
<td style="width: 132.1pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="176">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Duties Assigned</span></strong></p>
</td>
<td style="width: 103.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="138">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Extent to which carried out (%)</span></strong></p>
</td>
<td style="width: 67.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="90">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>

@foreach($report['sections']['exam_duties']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>
<td style="width: 155.9pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="208">
<p style="margin-right: 28.55pt; text-align: justify; line-height: 13.9pt; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Cambria','serif';">{{ $row['type'] ?? '' }}</span></p>
</td>
<td style="width: 132.1pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="176">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 9.5pt; font-family: 'Arial','sans-serif';">{{ $row['assined_duty'] ?? '' }}</span></p>
</td>
<td style="width: 103.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="138">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 9.5pt; font-family: 'Arial','sans-serif';">{{ $row['percent_carried'] ?? '' }}</span></p>
</td>
<td style="width: 67.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="90">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 9.5pt; font-family: 'Arial','sans-serif';">@endforeach</span></p>
</td>
</tr>

<tr style="height: .3in;">
<td style="width: 427.5pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="4" width="570">
<p style="margin-bottom: .0001pt; text-align: right; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total Score (Max.: 25)</span></strong></p>
</td>
<td style="width: 67.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="90">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;</span></strong></p>
</td>
</tr>
<tr style="height: .3in;">
<td style="width: 427.5pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="4" width="570">
<p style="margin-bottom: .0001pt; text-align: right; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total Score Category-II (1 + 2 + 3 + 4) (Min.: 100) out of &nbsp;&nbsp;(Max.: 125)</span></strong></p>
</td>
<td style="width: 67.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="90">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;</span></strong></p>
</td>
</tr>
</tbody>
</table>-->


<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: 20.65pt;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: 20.65pt;" width="36">
<p style="text-align: center; line-height: normal; text-autospace: none; margin: 0in -5.4pt .0001pt -5.4pt;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">2.2</span></strong></p>
</td>
<td style="width: 472.5pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: 20.65pt;" colspan="7" width="630">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Expert Lecture/Seminar/Sessions Delivered<br/>
<!--State/National Level - 5 Marks/Lecture, SSASIT Level - 2 Marks/Lecture--></span></strong></p>
</td>
</tr>
<tr>
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt;height: 23.35pt;"" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 111.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="149">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Topic Of Session.</span></strong></p>
</td>
<td style="width: 77.05pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="103">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">SSASIT/State/National/InternationalLevel</span></strong></p>
</td>
<td style="width: 85.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="114">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Date</span></strong></p>
</td>



<td style="width: 40.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="54">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>


@foreach($report['sections']['expert_lectures']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $loop->iteration }}</center></span></p>
</td>
<td style="width: 111.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="149">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['topic'] ?? '' }}</center></span></p>
</td>
<td style="width: 77.05pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="103">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['level'] ?? '' }}</center></span></p>
</td>
<td style="width: 85.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="114">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['date'] ?? '' }}</span></center></p>
</td>



<td style="width: 40.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="54">
	<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['mark'] ?? '' }}@endforeach</center></span></p>
</td>
</tr>

</tbody>
</table>
</br>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: 20.65pt;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: 20.65pt;" width="36">
<p style="text-align: center; line-height: normal; text-autospace: none; margin: 0in -5.4pt .0001pt -5.4pt;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">2.3</span></strong></p>
</td>
<td style="width: 472.5pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: 20.65pt;" colspan="7" width="630">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Articles / Chapters published in Books<br/>
<!--International/National/Local Publication Sole Author - 5 Marks
<br/>International/National/Local Publication Co-Author - 2 Marks--></span></strong></p>
</td>
</tr>
<tr>
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt;height: 23.35pt;"" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 111.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="149">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Publisher Name.</span></strong></p>
</td>
<td style="width: 77.05pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="103">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Title of Book</span></strong></p>
</td>
<td style="width: 85.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="114">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">ISSN/ ISBN No.</span></strong></p>
</td>
<td style="width: 85.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="114">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Are You Sole Author/Co-Author?.</span></strong></p>
</td>


<td style="width: 40.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" width="54">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>


@foreach($report['sections']['books']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $loop->iteration }}</center></span></p>
</td>
<td style="width: 111.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="149">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['publication_type'] ?? '' }}</center></span></p>
</td>
<td style="width: 77.05pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="103">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['book_title'] ?? '' }}</center></span></p>
</td>
<td style="width: 85.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="114">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['isbn_no'] ?? '' }}</span></center></p>
</td>
<td style="width: 85.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="114">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['author_type'] ?? '' }}</center></span></p>
</td>


<td style="width: 40.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="54">
	<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['mark'] ?? '' }}@endforeach</center></span></p>
</td>
</tr>

</tbody>
</table>
</br>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">2.4</span></strong></p>
</td>
<td style="width: 459.0pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="9" width="612">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Grant Received for Research Projects and Consultancies<!--<br/>Up to Rs. 1 Lac Budget - 5 Marks and for more than Rs. 1 Lac - 10 Marks--></span></strong></p>
</td>
</tr>

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr No</span></strong></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="2" width="204">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Title</span></strong></p>
</td>
<td style="width: 67.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="2" width="90">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Agency</span></strong></p>
</td>
<td style="width: 103.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="2" width="138">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Period</span></strong></p>
</td>
<td style="width: 85.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="2" width="114">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">sponsored Amount inLacs</span></strong></p>
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">(Rs. In Lakhs)</span></strong></p>
</td>
<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API score</span></strong></p>
</td>
</tr>


@foreach($report['sections']['research_projects']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $loop->iteration }}</center></span></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="2" width="204">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['title'] ?? '' }}</center></span></p>
</td>
<td style="width: 67.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="2" width="90">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['agency'] ?? '' }}</center></span></p>
</td>
<td style="width: 103.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="2" width="138">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['period'] ?? '' }}</center></span></p>
</td>
<td style="width: 85.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="2" width="114">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['amount'] ?? '' }}</center></span></p>
</td>
<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $row['mark'] ?? '' }}@endforeach</center></span></p>
</td>
</tr>

<!--<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="text-align: center; line-height: normal; text-autospace: none; margin: 0in -5.4pt .0001pt -5.4pt;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">(iii&amp; iv)</span></strong></p>
</td>
<td style="width: 459.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="9" width="612">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total Marks</span></strong></p>
</td>
</tr>-->


</tbody>
</table>
</br>

<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">2.5.</span></strong></p>
</td>
<td style="width: 459.0pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="5" width="612">
<p style="margin-bottom: .0001pt; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Research Guidance</span></strong></p>
</td>
</tr>
<tr style="height: 23.35pt;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="204">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Year Of Enrollment of Course
</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Number Enrolled</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Thesis Submitted</span></strong></p>
</td>
<td style="width: 76.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="102">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Degree awarded</span></strong></p>
</td>
<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>

@foreach($report['sections']['research_individuals']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="204">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['enrollment_year'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['enrollment_number'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['thesis_submitted'] ?? '' }}</span></p>
</td>
<td style="width: 76.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="102">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['degree_awarded'] ?? '' }}</span></p>
</td>
<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['mark'] ?? '' }}@endforeach</span></p>
</td>
</tr>
</tbody>
</table>
</br>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">2.6</span></strong></p>
</td>
<td style="width: 459.0pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="5" width="612">
<p style="margin-bottom: .0001pt; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Award/Recognition<!--<br/>Any Outstanding Achievement or Performance Achieved : 5 Marks/Achievement--></span></strong></p>
</td>
</tr>
<tr style="height: 23.35pt;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="204">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Title of Award</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Award Given By</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Date of Achievement</span></strong></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>

@foreach($report['sections']['awards']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="204">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['name'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['givenby'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['date'] ?? '' }}</span></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['mark'] ?? '' }}@endforeach</span></p>
</td>
</tr>
</tbody>
</table>
</br>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">2.7</span></strong></p>
</td>
<td style="width: 459.0pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="5" width="612">
<p style="margin-bottom: .0001pt; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Projects Guided<!--<br/>Ph.D project Guide - 5 Marks, UG/PG Projects Core Guide - 3 Marks, UG/PG Project Co-Guide - 2 Marks and DE ProjectGuide - 1 Mark--></span></strong></p>
</td>
</tr>
<tr style="height: 23.35pt;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="204">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Type Of Project</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Guide Type</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Achievement/Award/Rewards</span></strong></p>
</td>


<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>

@foreach($report['sections']['project_guides']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="204">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['project_type'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['guide_type'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['number'] ?? '' }}</span></p>
</td>


<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['mark'] ?? '' }}@endforeach</span></p>
</td>
</tr>
</tbody>
</table>
</br>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">2.8</span></strong></p>
</td>
<td style="width: 459.0pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="6" width="612">
<p style="margin-bottom: .0001pt; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Short Term Training Program/Faculty Development Program Attended<!--<br/>1 Mark Per Day--></span></strong></p>
</td>
</tr>
<tr style="height: 23.35pt;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="204">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Training Program Type</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Title of FDP/STTP</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Name of Organizer</span></strong></p>
</td>
<td style="width: 76.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="102">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Date</span></strong></p>
</td>
<td style="width: 76.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="102">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">No. of Days</span></strong></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>

@foreach($report['sections']['sttps']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="204">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['program'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['title'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['organizer'] ?? '' }}</span></p>
</td>
<td style="width: 76.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="102">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['date'] ?? '' }}</span></p>
</td>
<td style="width: 76.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="102">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['day_number'] ?? '' }}</span></p>
</td>
<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['mark'] ?? '' }}@endforeach</span></p>
</td>
</tr>
<!--<tr><td colspan="6" style="width: 49.5pt;  border-left: solid windowtext 1.0pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total</span></strong></p>
</td>
<td  style="width: 49.5pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"></span></strong></p>
</td></tr>-->
</tbody>
</table>
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-family: 'Arial','sans-serif';">CATEGORY III: INSTITUTIONAL ACTIVITIES </span></strong></p>


</br>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">3.1</span></strong></p>
</td>
<td style="width: 459.0pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="5" width="612">
<p style="margin-bottom: .0001pt; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Administration Work/Contribution to Corporate Life and Management of the Institution<!--<br/>Dean/HOD - 5 Marks, Co-Dean - 3 Marks, Class Coordinator - 3 Marks, Committee Members - 2 Marks, Lab Incharge - 2Marks--></span></strong></p>
</td>
</tr>
<tr style="height: 23.35pt;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="204">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Worked As</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Committee Name/Department Name</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Date</span></strong></p>
</td>


<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>

@foreach($report['sections']['admin_duties']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="204">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['post'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['com_name'] ?? '' }}</span></p>
</td>

<td style="width: 76.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="102">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['year'] ?? '' }}</span></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['mark'] ?? '' }}@endforeach</span></p>
</td>
</tr>
<!--<tr><td colspan="4" style="width: 49.5pt;  border-left: solid windowtext 1.0pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total</span></strong></p>
</td>
<td  style="width: 49.5pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"></span></strong></p>

</td></tr>-->
</tbody>
</table>
</br>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">3.2</span></strong></p>
</td>
<td style="width: 459.0pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="5" width="612">
<p style="margin-bottom: .0001pt; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Extension Co-curricular & field based activities: Event Coordination at GTU/State/SSASIT<!--<br/>GTU/State Level Coordinator - 5 Marks,GTU/State Level Committee Member - 2 Marks<br/>SSASIT/Department Level Event Administration - 2 Marks and SSASIT/Department Level Event committee Member -1 Mark--></span></strong></p>
</td>
</tr>
<tr style="height: 23.35pt;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="204">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Event Name</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Event Type</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Post/Designationin Event</span></strong></p>
</td>


<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>

@foreach($report['sections']['events']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="204">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['event_name'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['level'] ?? '' }}</span></p>
</td>

<td style="width: 76.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="102">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['post'] ?? '' }}</span></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['mark'] ?? '' }}@endforeach</span></p>
</td>
</tr>
<!--<tr><td colspan="4" style="width: 49.5pt;  border-left: solid windowtext 1.0pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total</span></strong></p>
</td>
<td  style="width: 49.5pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"></span></strong></p>
	
</td></tr>-->
</tbody>
</table>
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-family: 'Arial','sans-serif';">CATEGORY IV: CONTRIBUTION TO SOCIETY</span></strong></p>
<br/>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">4.1(a)</span></strong></p>
</td>
<td style="width: 459.0pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="5" width="612">
<p style="margin-bottom: .0001pt; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Professional Development : Industrial Visit/Training, Industrial Visit with Students <!--- 3 Marks, Local Visit - 2 Marks and Industrial Training 1 Mark/Day--></span></strong></p>
</td>
</tr>
<tr style="height: 23.35pt;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="204">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Industrial Visit/Training</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Name of Industy</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">No. of Days</span></strong></p>
</td>



<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>

</tr>

@foreach($report['sections']['industrial_visits']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="204">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['activity'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['name'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['responsibilities'] ?? '' }}</span></p>
</td>


<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['mark'] ?? '' }}@endforeach</span></p>
</td>
<!--<tr><td colspan="4" style="width: 49.5pt;  border-left: solid windowtext 1.0pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total</span></strong></p>
</td>
<td  style="width: 49.5pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"></span></strong></p>

</td>
</tr>-->
</tbody>
</table>
<br>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">4.1(b)</span></strong></p>
</td>
<td style="width: 459.0pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="5" width="612">
<p style="margin-bottom: .0001pt; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Professional Development : Professional Membership<!--<br/>3 Marks/Membership--></span></strong></p>
</td>
</tr>
<tr style="height: 23.35pt;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="204">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Professional Body Name</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Membership Type</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Membership Id</span></strong></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>

@foreach($report['sections']['memberships']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="204">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['professionalbody_name'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['membership_duration'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['membership_id'] ?? '' }}</span></p>
</td>


<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['mark'] ?? '' }}@endforeach</span></p>
</td>
</tr>
<!--<tr><td colspan="4" style="width: 49.5pt;  border-left: solid windowtext 1.0pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total</span></strong></p>
</td>
<td  style="width: 49.5pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $report['sections']['memberships']['total'] }}</span></strong></p>

</td>
</tr>-->
</tbody>
</table>

<br/>
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">4.2</span></strong></p>
</td>
<td style="width: 459.0pt; border: solid windowtext 1.0pt; border-left: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="5" width="612">
<p style="margin-bottom: .0001pt; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Other Activities</span></strong></p>
</td>
</tr>
<tr style="height: 23.35pt;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="204">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Worked As</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Committee Name/Department Name</span></strong></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Date</span></strong></p>
</td>


<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: 23.35pt;" width="66">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">API Score</span></strong></p>
</td>
</tr>

@foreach($report['sections']['community_activities']['rows'] as $row)

<tr style="height: .3in;">
<td style="width: .5in; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="48">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $loop->iteration }}</span></p>
</td>
<td style="width: 153.0pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="204">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['name'] ?? '' }}</span></p>
</td>
<td style="width: 1.25in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="120">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['worked_as'] ?? '' }}</span></p>
</td>

<td style="width: 76.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="102">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['date'] ?? '' }}</span></p>
</td>

<td style="width: 49.5pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">{{ $row['mark'] ?? '' }}@endforeach</span></p>
</td>
</tr>
<!--<tr><td colspan="4" style="width: 49.5pt;  border-left: solid windowtext 1.0pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total</span></strong></p>
</td>
<td  style="width: 49.5pt; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="66">
	<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"></span></strong></p>

</td></tr>-->
</tbody>
</table>
<br/>

<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr style="height: .3in;">
<!--<td style="width: 27.0pt; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"></span></strong></p>
</td>-->
<td style="width: 6.5in; border: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="5" width="624">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><center><span style="font-size: 15.0pt; font-family: 'Arial','sans-serif';">SUMMARY OF API SCORES</span></center></strong></p>
</td>
</tr>
<tr style="height: .3in;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr. No.</span></strong></p>
</td>
<td style="width: 2.5in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="240">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Criteria</span></strong></p>
</td>

<td style="width: 92.55pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="123">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total API Score For Assessment Period</span></strong></p>
</td>

</tr>
<tr style="height: .3in;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">1</span></strong></p>
</td>
<td style="width: 2.5in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="240">
<p style="margin-bottom: .0001pt; text-autospace: none;"><span style="font-size: 10.0pt; line-height: 115%; font-family: 'Arial','sans-serif';">TEACHING PROCESS (Category -1)</span></p>
</td>

<td style="width: 92.55pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="123">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $report['c1'] }}</center></span></p>
</td>

</tr>
<tr style="height: .3in;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">2</span></strong></p>
</td>
<td style="width: 2.5in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="240">
<p style="margin-bottom: .0001pt;"><span style="font-size: 10.0pt; line-height: 115%; font-family: 'Arial','sans-serif';">DEPARTMENTAL ACTIVITIES/PERSONAL DEVELOPMENT ACTIVITIES (Category -2)</span></p>
</td>

<td style="width: 92.55pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="123">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $report['c2'] }}</center></span></p>
</td>

</tr>
<tr style="height: .3in;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">3</span></strong></p>
</td>
<td style="width: 2.5in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="240">
<p style="margin-bottom: .0001pt; text-align: left;"><span style="font-size: 10.0pt; line-height: 115%; font-family: 'Arial','sans-serif';"> INSTITUTIONAL ACTIVITIES  (Category -3)</span></p>
</td>

<td style="width: 92.55pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="123">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $report['c3'] }}</center></span></p>
</td>

</tr>
<tr style="height: .3in;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">4</span></strong></p>
</td>
<td style="width: 2.5in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="240">
<p style="margin-bottom: .0001pt;"><span style="font-size: 10.0pt; line-height: 115%; font-family: 'Arial','sans-serif';">CONTRIBUTION TO SOCIETY (Category -4)</span></p>

</td>

<td style="width: 92.55pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="123">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $report['c4'] }}</center></span></p>
</td>

</tr>
<tr style="height: .3in;">

<td colspan="2" style="width: 2.5in;  border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="240">
<p style="margin-bottom: .0001pt; text-align: right; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total ( 1 + 2 + 3 + 4) (Out of 65)</span></strong></p>

</td>

<td style="width: 92.55pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="123">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"><center>{{ $report['total'] }}</center></span></p>
</td>

</tr>
<tr style="height: .3in;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">5</span></strong></p>
</td>
<td style="width: 2.5in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="240">
<p style="margin-bottom: .0001pt;"><span style="font-size: 10.0pt; line-height: 115%; font-family: 'Arial','sans-serif';">STUDENT FEEDBACK (Category -5) <strong>(Out of 25)</strong></span></p>

</td>

<td style="width: 92.55pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="123">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp</span></p>
</td>

</tr>
</tr>
<tr style="height: .3in;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">6</span></strong></p>
</td>
<td style="width: 2.5in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="240">
<p style="margin-bottom: .0001pt;"><span style="font-size: 10.0pt; line-height: 115%; font-family: 'Arial','sans-serif';">HOD (Category -6)<strong>(Out of 5)</strong></span></p>

</td>

<td style="width: 92.55pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="123">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp</span></p>
</td>

</tr>
<tr style="height: .3in;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">7</span></strong></p>
</td>
<td style="width: 2.5in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="240">
<p style="margin-bottom: .0001pt;"><span style="font-size: 10.0pt; line-height: 115%; font-family: 'Arial','sans-serif';">Principal (Category -7) <strong>(Out of 5)</strong></span></p>

</td>

<td style="width: 92.55pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="123">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp</span></p>
</td>

</tr>
<tr style="height: .3in;">
<td style="width: 27.0pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="36">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;</span></p>
</td>
<td style="width: 2.5in; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="240">
<p style="margin-bottom: .0001pt; text-align: right; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Total ( 1 + 2 + 3 + 4 + 5 + 6 + 7)</span></strong></p>
</td>

<td style="width: 92.55pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="123">
<p style="margin-bottom: .0001pt; line-height: normal;"><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">&nbsp;</span></p>
</td>

</tr>
</tbody>
</table>

<!--
<table style="border-color=#FFFFFF; border-collapse: collapse; border: none;" width="100%">
<tbody>
<tr>
<td style="width: 495.0pt; border: none; padding: 0in 5.4pt 0in 5.4pt;" colspan="5" width="660">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-family: 'Arial','sans-serif';">PART C : OTHER RELEVANT INFORMATION</span></strong></p>
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-family: 'Arial','sans-serif';">&nbsp;</span></strong></p>
</td>
</tr>
<tr>
<td style="width: 495.0pt; border: none; border-bottom: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt;" colspan="5" width="660">
<p style="margin-bottom: .0001pt; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Please give details of any other credential significant contributions. Awards received, etc. not mention earlier.</span></strong></p>
</td>
</tr>
<tr style="height: 26.95pt;">
<td style="width: 58.05pt; border: solid windowtext 1.0pt; border-top: none; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: 26.95pt;" width="77">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Sr No.</span></strong></p>
</td>
<td style="width: 436.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; background: #D9D9D9; padding: 0in 5.4pt 0in 5.4pt; height: 26.95pt;" colspan="4" width="583">
<p style="margin-bottom: .0001pt; text-align: center; line-height: normal; text-autospace: none;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">Details (Mention Year value etc where relevant)</span></strong></p>
</td>
</tr>



<tr style="height: .3in;">
<td style="width: 58.05pt; border: solid windowtext 1.0pt; border-top: none; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" width="77">
<p style="margin-bottom: .0001pt; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';"></span></strong></p>
</td>
<td style="width: 436.95pt; border-top: none; border-left: none; border-bottom: solid windowtext 1.0pt; border-right: solid windowtext 1.0pt; padding: 0in 5.4pt 0in 5.4pt; height: .3in;" colspan="4" width="583">
	<p style="margin-bottom: .0001pt; line-height: normal;"><strong><span style="font-size: 10.0pt; font-family: 'Arial','sans-serif';">@endforeach</span></strong></p>
</td>
</tr>


</tbody>
</table>-->


</body>
</html>