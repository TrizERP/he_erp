@include('includes.headcss')

<style>
   .left-container {
   background: #ffffff; 
   background: -webkit-linear-gradient(to right, #434343, #000000);
   background: #ffffff; 
   flex: 1;
   max-width: 20%;
   display: flex;
   flex-direction: column;
   align-items: center;
   height:400px;
   /* padding: 10px; */
   margin: 30px;
   border-radius: 20px;
   box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
   }
   .right-container {
   background: #ffffff; 
   background: -webkit-linear-gradient(to left, #434343, #000000);
   background: #ffffff; 
   flex: 1;
   max-width:80%;
   padding: 20px;
   margin: 20px;
   border-radius:30px;
   box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
   }
   @media only screen and (max-width: 860px) {
   .card
   {
   flex-direction: column;
   margin: 10px;
   height: auto;
   width: 90%;
   }
   .left-container{
   flex: 1;
   max-width:100%; 
   }
   }
   @media only screen and (max-width: 600px) {
   .card
   {
   flex-direction: column;
   margin: 10px;
   }
   .left-container{
   flex: 1;
   max-width:100%; 
   }
   p{
   font-size:16px
   }
   }
   img {
   width: 150px;
   height: 150px;
   max-width: 200px;
   border-radius: 50%;
   margin: 10px;
   box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
   background:#fff;
   }
   h2 {
   font-size: 24px;
   margin-bottom: 5px;
   }
   h3 {
   text-align: center;
   font-size: 24px;
   margin-bottom: 5px;
   }
   .gradienttext{
   background-image: linear-gradient(to right, #ee00ff 0%, #aeb11e 100%);
   color: transparent;
   -webkit-background-clip: text;
   }
   p {
   font-size: 16px;
   margin-bottom: 20px;
   color:#537b9f
   }
   .credit a {
   text-decoration: none;
   color: #fff;
   font-weight: 800;
   }
   .credit {
   color: #fff;
   text-align: center;
   margin-top: 10px;
   font-family: Verdana,Geneva,Tahoma,sans-serif;
   }
   aside{
   display:none !important;
   }
   .circledata {
   width: 100%;
   /* height: 60%; */
   background: #25bdea;
   clip-path: circle(150px at center 0);
   text-align: center;
   }
   .circledata h2 {
   color: #fff;
   padding : 45px;
   }
   .tab-title {
   background:#25bdea !important;
   }
   .section-linemove-1{
   text-align:center;
   }
   .enquiryTableDiv{
   display:flex;
   justify-content:center;
   }
   .enquiryTable{
   width:50%;
   }
</style>
<div class="d-flex">
   <!-- strat  -->
   <div class="left-container">
      <div class="circledata">
         <h2>Welocome !<br>{{ucfirst($data['details']->first_name).' '.ucfirst($data['details']->middle_name).' '.ucfirst($data['details']->last_name)}}</h2>
      </div>
      <div class="content" style="padding:35px 0px">
         <p><b>Enquiry No: </b>{{$data['details']->enquiry_no}}</p>
         <p><b>Email: </b>{{$data['details']->email}}</p>
         <p><b>Mobile: </b>{{$data['details']->mobile}}</p>
         <p><a class="btn btn-primary" href="{{route('admission_status.create')}}">Log Out</a></p>
      </div>
   </div>
   <div class="right-container">
     <!-- tabs start  -->
        <div class="sttabs tabs-style-linemove triz-verTab bg-white style2" style="text-align:center">
        <ul class="nav nav-tabs tab-title mb-4">
            <li class="nav-item">
                <a href="#section-linemove-1" class="nav-link active" aria-selected="true" data-toggle="tab"><span>Enquiry</span></a>
            </li>
            <li class="nav-item">
                <a href="#section-linemove-2" class="nav-link" aria-selected="false" data-toggle="tab"><span>Registration</span></a>
            </li>
            <li class="nav-item">
                <a href="#section-linemove-3" class="nav-link" aria-selected="false" data-toggle="tab"><span>Confirmation</span></a>
            </li>
        </ul>
        <!-- tabs end  -->

            <!-- Tab Content Wrapper -->
            <div class="tab-content">
                <!-- tab 1 -->
                <div class="tab-pane fade show active" id="section-linemove-1" role="tabpanel">        
                    <h4><b>Status :</b> <span style="color:green">Completed</span></h4>
                    <br>
                    <div class="enquiryTableDiv">
                        <table class="table enquiryTable">
                        <tr>
                            <th>DOB</th>
                            <td>{{$data['details']->date_of_birth}}</td>
                        </tr>
                        <tr>
                            <th>Age</th>
                            <td>{{$data['details']->age}}</td>
                        </tr>
                        <tr>
                            <th>Gender</th>
                            <td>{{$data['details']->gender}}</td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>{{$data['details']->address}}</td>
                        </tr>
                        <tr>
                            <th>Previous School</th>
                            <td>{{$data['details']->previous_school_name}}</td>
                        </tr>
                        <tr>
                            <th>Previous Standard</th>
                            <td>{{$data['details']->previous_standard_name}}</td>
                        </tr>
                        <tr>
                            <th>Admission Standard</th>
                            <td>{{$data['details']->admission_standard_name}}</td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td>{{$data['details']->category_name}}</td>
                        </tr>
                        </table>
                    </div>
                </div>
                
                <!-- tab 2 -->
                <div class="tab-pane fade" id="section-linemove-2" role="tabpanel"> 
                    <!-- Content for Registration tab -->
                    @php 
                        $registrationStatus = 'In-Progress';
                        $registrationColor = 'red';
                        if(!empty($data['registration'])){
                        $registrationStatus = $data['registration']->status;
                        $registrationColor = 'green';
                        }
                        @endphp
                        <h4><b>Status :</b> <span style="color:{{$registrationColor}}">{{$registrationStatus}}</span></h4>
                        <br>
                        @if(!empty($data['registration']))
                        <div class="enquiryTableDiv">
                        <table class="table enquiryTable">
                            <tr>
                                <th>Remarks</th>
                                <td>{{$data['registration']->remarks}}</td>
                            </tr>
                            <tr>
                                <th>Follow Up Date</th>
                                <td>{{date('d-m-Y',strtotime($data['registration']->followup_date))}}</td>
                            </tr>
                            <tr>
                                <th>Father Education Qualification</th>
                                <td>{{$data['registration']->father_education_qualification}}</td>
                            </tr>
                            <tr>
                                <th>Father Occupation</th>
                                <td>{{$data['registration']->father_occupation}}</td>
                            </tr>
                            <tr>
                                <th>Mother Education Qualification</th>
                                <td>{{$data['registration']->mother_education_qualification}}</td>
                            </tr>
                            <tr>
                                <th>Mother Occupation</th>
                                <td>{{$data['registration']->mother_occupation}}</td>
                            </tr>
                            <tr>
                                <th>Annual Income</th>
                                <td>{{$data['registration']->annual_income}}</td>
                            </tr>
                            <tr>
                                <th>Stop For Transport</th>
                                <td>{{$data['registration']->stop_for_transport}}</td>
                            </tr>
                            <tr>
                                <th>Counciler Name</th>
                                <td>{{$data['registration']->counciler_name}}</td>
                            </tr>
                            <tr>
                                <th>Last Exam Name</th>
                                <td>{{$data['registration']->last_exam_name}}</td>
                            </tr>
                            <tr>
                                <th>Last Exam Percentage</th>
                                <td>{{$data['registration']->last_exam_percentage}}</td>
                            </tr>
                            
                        </table>
                    </div>
                    @endif
                </div>
                
                <!-- tab 3 -->
                <div class="tab-pane fade" id="section-linemove-3" role="tabpanel"> 
                    <!-- Content for Confirmation tab -->
                    @php 
                        $registrationStatus = 'In-Progress';
                        $registrationColor = 'red';
                        if(!empty($data['confirmation'])){
                            if($data['confirmation']->admission_status=='YES'){
                                $registrationColor = 'green';
                            }
                            $registrationStatus = $data['confirmation']->admission_status;
                        }
                        @endphp
                        <h4><b>Admission Status :</b> <span style="color:{{$registrationColor}}">{{$registrationStatus}}</span></h4>
                        <br>
                        @if(!empty($data['confirmation']))
                        <div class="enquiryTableDiv">
                        <table class="table enquiryTable">
                            <tr>
                                <th>Enrollment No</th>
                                <td>{{$data['confirmation']->enrollment_no}}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td>{{date('d-m-Y',strtotime($data['confirmation']->amount))}}</td>
                            </tr>
                            <tr>
                                <th>Payment Mode</th>
                                <td>{{$data['confirmation']->payment_mode}}</td>
                            </tr>
                            <tr>
                                <th>Date of Payment</th>
                                <td>{{date('d-m-Y',strtotime($data['confirmation']->date_of_payment))}}</td>
                            </tr>
                            @if($data['confirmation']->bank_name)
                            <tr>
                                <th>Bank Name</th>
                                <td>{{$data['confirmation']->bank_name}}</td>
                            </tr>
                            @endif
                            @if($data['confirmation']->bank_branch)
                            <tr>
                                <th>Bank Branch</th>
                                <td>{{$data['confirmation']->bank_branch}}</td>
                            </tr>
                            @endif
                            @if($data['confirmation']->cheque_no)
                            <tr>
                                <th>Cheque No</th>
                                <td>{{$data['confirmation']->cheque_no}}</td>
                            </tr>
                            @endif
                            @if($data['confirmation']->cheque_date)
                            <tr>
                                <th>Cheque Date</th>
                                <td>{{date('d-m-Y',strtotime($data['confirmation']->cheque_date))}}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Register No.</th>
                                <td>{{$data['confirmation']->register_number}}</td>
                            </tr>
                            <tr>
                                <th>Mother Name</th>
                                <td>{{$data['confirmation']->mother_name}}</td>
                            </tr>
                            <tr>
                                <th>Mother Mobile No.</th>
                                <td>{{$data['confirmation']->mother_mobile_number}}</td>
                            </tr>
                            <tr>
                                <th>Admission Date</th>
                                <td>{{$data['confirmation']->admission_date}}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{$data['confirmation']->status}}</td>
                            </tr>
                           
                        </table>
                    </div>
                    @endif
                </div>
            </div>
            </div>
      
   </div>
   <!-- end  -->
</div>
@include('includes.footerJs')
