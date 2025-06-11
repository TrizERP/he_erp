@include('includes.headcss')
<style>
    .left-container {
    background: #ffffff; 
    background: -webkit-linear-gradient(to right, #434343, #000000);
    background: #ffffff; 
    flex: 1;
    max-width: 30%;
    display: flex;
    flex-direction: column;
    align-items: center;
    height:430px;
    padding: 10px;
    margin: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
  }
  
  .right-container {
    background: #ffffff; 
    background: -webkit-linear-gradient(to left, #434343, #000000);
    background: #ffffff; 
    flex: 1;
    max-width:70%;
    height:430px;
    padding: 10px;
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
    font-size: 20px;
    margin-bottom: 20px;
    color:#537b9f
  }
  
  table {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
    border-collapse: collapse;
    margin-top:32px;
  }
  
  td {
    padding: 10px;
    border: none;
    border-radius: 20px;
    color: white;
  }
  
  td:first-child {
    font-weight: bold;
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
</style>
        <div class="card d-flex">
            <!-- strat  -->
            @php 
            $data2 = $data['data'];
            @endphp
            <div class="left-container">
                <img src="https://he.triz.co.in/admin_dep/images/clg_student.webp" alt="Profile Image">
                <h2 class="gradienttext" style="margin-bottom:16px">Login Details</h2>
                <p><b>Enquiry No :</b> {{$data2['enquiry_no']}}</p>
                <p><b>Email :</b> {{$data2['email']}}</p>
                <p><b>Password :</b> {{$data2['admission_password']}}</p>
                <a href="{{route('admission_status.index')}}?sub_institute_id={{$data['sub_institute_id']}}" class="btn btn-outline-info" target="_blank">Login</a>
            </div>
            <div class="right-container">
                <h3 class="gradienttext">Confirm Your Details</h3>
                <table>
                    <tr>
                        <td>Name :</td>
                        <td>{{$data2['first_name'].' '.$data2['middle_name'].' '.$data2['last_name']}}</td>
                    </tr>
                    <tr>
                        <td>Admission Standard :</td>
                        <td>{{$data['standard_name']}}</td>
                    </tr>
                    <tr>
                        <td>DOB :</td>
                        <td>{{ date('d-m-Y',strtotime($data2['date_of_birth']))}}</td>
                    </tr>
                    <tr>
                        <td>Age :</td>
                        <td>{{$data2['age']}}</td>
                    </tr>
                    <tr>
                        <td>Gender :</td>
                        <td>{{$data2['gender']}}</td>
                    </tr>
                    <tr>
                        <td>Mobile :</td>
                        <td>{{$data2['mobile']}}</td>
                    </tr>
                    <tr>
                        <td>Email :</td>
                        <td>{{$data2['email']}}</td>
                    </tr>
                    <tr>
                        <td>Address :</td>
                        <td>{{$data2['address']}}</td>
                    </tr>
                </table>
               
            </div>
            <!-- end  -->
        </div>

@include('includes.footerJs')
<script>
   $(document).on('keydown', function(event) {
    if ((event.which === 116) || (event.ctrlKey && event.which === 82)) {
        event.preventDefault(); // Prevent the refresh
    }
});
window.addEventListener('beforeunload', function (event) {
    event.preventDefault();
});

</script>