{{-- @include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation') --}}
<link href="{{ asset("/plugins/bower_components/report/css/styleReport.css") }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link href="{{ asset("/plugins/bower_components/report/css/aos.css") }}" rel="stylesheet" type="text/css" />
<style>
    .highcharts-figure, .highcharts-data-table table {
    min-width: 360px; 
    max-width: 800px;
    margin: 1em auto;
}

.highcharts-data-table table {
	font-family: Verdana, sans-serif;
	border-collapse: collapse;
	border: 1px solid #EBEBEB;
	margin: 10px auto;
	text-align: center;
	width: 100%;
	max-width: 500px;
}
.highcharts-data-table caption {
    padding: 1em 0;
    font-size: 1.2em;
    color: #555;
}
.highcharts-data-table th {
	font-weight: 600;
    padding: 0.5em;
}
.highcharts-data-table td, .highcharts-data-table th, .highcharts-data-table caption {
    padding: 0.5em;
}
.highcharts-data-table thead tr, .highcharts-data-table tr:nth-child(even) {
    background: #f8f8f8;
}
.highcharts-data-table tr:hover {
    background: #f1f7ff;
}
</style>
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="w-100">
                <div class="w-100">
                    <header class="header">
                        <div class="container">
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="select-box">
                                        <h5>Select Exam</h5>
                                        <select name="exam_type" onchange="redirectthis();">
                                            <option value="" >--Select--</option>
                                            @foreach ($data['data']['all_dd']['exam_type_dd'] as $item=>$val)
                                            @php
                                            if ($data['data']['selected_exam_type'] == $item){
                                            $selected = "selected=selected";
                                            }else{
                                            $selected = "";
                                            }
                                            @endphp

                                            <option {{ $selected }} value="{{ $item }}">{{ $val }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="subject-list">
                                        <h5>Subjects</h5>
                                        <ul>
                                            <?php
                                            $color_arr = array(
                                                "red-btn","navy-btn","yellow-btn","pink-btn","blue-btn",
                                                "red-btn","navy-btn","yellow-btn","pink-btn","blue-btn",
                                                "red-btn","navy-btn","yellow-btn","pink-btn","blue-btn"
                                            );
                                            $i = 0;
                                             ?>
                                             @foreach ($data['data']['subject_dd'] as $item=>$val)
                                             <li><a href="#" class={{$color_arr[$i]}}>{{$val}}</a></li>     
                                             <?php $i++; ?>
                                             @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </header>

                    <div class="container-fluid" data-aos="fade-up" data-aos-duration="2000">
                        <div class="row justify-content-center">
                            <div class="col-md-10 text-center">
                                <h2 class="std-details">
                                    <strong>Institute</strong> has scored 
                                    {{$data['data']['total_subject_marks']}}
                                    in Learning Outcome
                                </h2>
                            </div>
                        </div>
                    </div>

                    <!-- Subject Report Start -->
                    <div class="subject-report py-4" data-aos="fade-up" data-aos-duration="2000">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-md-12">
                                    <div class="school-heading">
                                        <h2>Course Report</h2>
                                    </div>
                                    <div class="row">
                                        @foreach ($data['data']['triangle'] as $subject=>$data1)
                                        <div class="col">
                                            <div class="subject-area">
                                                <div class="low" style="height: {{ $data1['RED']['per'] }}%;"
                                                    tabindex="0" data-toggle="tooltip" data-placement="top" title="{{ $data1['RED']['per'] }}%">
                                                    <span>low</span></div>
                                                <div class="medium" style="height: {{ $data1['YELLOW']['per'] }}%;"
                                                    tabindex="0" data-toggle="tooltip" data-placement="top" title="{{ $data1['YELLOW']['per'] }}%">
                                                    <span>Medium</span></div>
                                                <div class="high" style="height: {{ $data1['GREEN']['per'] }}%;"
                                                    tabindex="0" data-toggle="tooltip" data-placement="top" title="{{ $data1['GREEN']['per'] }}%"><span>High</span>
                                                </div>
                                            </div>
                                            <div class="total-mark">
                                                <h5>{{ $subject }}</h5>
                                                {{-- <h5>Gujarati</h5> --}}
                                                <div class="sub-mark"> {{ $data1['mar'] }}/ {{ $data1['tot'] }}</div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Subject Report End -->

                    <!-- Subject Graph Start -->
                    <div class="sub-graph mb-5" data-aos="fade-up" data-aos-duration="2000">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="school-heading">
                                        <h2>Course Report</h2>
                                        <p>Upon completion of the course, students will develop foundational technical skills, enhance problem-solving abilities, and gain practical exposure necessary for advanced engineering studies and professional success.</p>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div id="chart-container">View</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Subject Graph Start -->

                    <!-- Learning Outcome Start -->
                    <div class="learning-outcome py-5" data-aos="fade-up" data-aos-duration="2000">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="school-heading">
                                        <h2>Learning Outcome</h2>
                                    </div>
                                </div>
                                <div class="col-md-8 text-center">
                                    <div class="subject-list">
                                        <ul>
                                            <?php $i = 0; ?>
                                            @foreach ($data['data']['subject_dd'] as $item=>$val)
                                            <li><a href="#" class="{{$color_arr[$i]}}">{{$val}}</a></li>
                                            <?php $i++; ?>
                                            @endforeach

                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless student-table">
                                    <?php $i = 0; ?>
                                    @foreach ($data['data']['all_subject_lo'] as $subject=>$lo_arr)
                                    <tr>
                                        <th width="200">
                                            <div class="chap-btn {{$color_arr[$i]}}">{{$subject}}</div>
                                        </th>
                                        <td>
                                            <ul class="student-total-per">
                                                @foreach ($lo_arr as $lo=>$mar_ar)
                                                <li>
                                                    <div class="total-per-box">
                                                    <h5>{{$lo}}</h5>
                                                    <div class="total-per">{{ $mar_ar['PER'] }}%</div>
                                                    </div>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Learning Outcome End -->
                </div>
                <div class="remedation-section container-fluid">
                    <div class="row">
                        <div class="col-md-12 col-lg-6 remedation" data-aos="fade-right" data-aos-duration="2000">
                            <div class="py-5 px-5">
                                <div class="school-heading">
                                    <h2>Remediation</h2>
                                </div>
                                <ul class="performance-receiving">
                                    <li>
                                        <h5>Practice Required</h5>
                                        <div class="stars text-right">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                        </div>
                                    </li>
                                    <li>
                                        <h5>Intervention Required</h5>
                                        <div class="stars text-right">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                        </div>
                                    </li>
                                    <li>
                                        <h5>Conceptual Clerity Required</h5>
                                        <div class="stars text-right">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                        </div>
                                    </li>
                                </ul>
                
                                <ul class="performance-star text-center">
                                    <li>
                                        <h5>Best Performance</h5>
                                        <div class="stars text-center">
                                                <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                                <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                                <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                                <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            </div>
                                    </li>
                                    <li>
                                        <h5>Best Performance</h5>
                                        <div class="stars text-center">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                        </div>
                                    </li>
                                    <li>
                                        <h5>Best Performance</h5>
                                        <div class="stars text-center">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                        </div>
                                    </li>
                                    <li>
                                        <h5>Best Performance</h5>
                                        <div class="stars text-center">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                            <img src="{{ asset("/plugins/bower_components/report/images/star.svg") }}" height="20" alt="">
                                        </div>
                                    </li>
                                </ul>
                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@include('includes.footerJs')
<script src="{{ asset("/plugins/bower_components/report/js/popper.min.js") }}" type="text/javascript"></script>
<script src="{{ asset("/plugins/bower_components/report/js/bootstrap.min.js") }}" type="text/javascript"></script>
<script src="{{ asset("/plugins/bower_components/report/js/school.js") }}" type="text/javascript"></script>
<script src="{{ asset("/plugins/bower_components/report/js/fusioncharts.js") }}" type="text/javascript"></script>
<script src="{{ asset("/plugins/bower_components/report/js/fusioncharts.theme.fusion.js") }}" type="text/javascript">
</script>
<script src="{{ asset("/plugins/bower_components/report/js/fusioncharts.widgets.js") }}" type="text/javascript"></script>
<script src="{{ asset("/plugins/bower_components/report/js/aos.js") }}"></script>

<script src="//code.highcharts.com/highcharts.js"></script>
<script src="//code.highcharts.com/modules/series-label.js"></script>
<script src="//code.highcharts.com/modules/exporting.js"></script>
<script src="//code.highcharts.com/modules/export-data.js"></script>
<script src="//code.highcharts.com/modules/accessibility.js"></script>


<script>
    var x, i, j, selElmnt, a, b, c;
/*look for any elements with the class "select-box":*/
x = document.getElementsByClassName("select-box");
for (i = 0; i < x.length; i++) {
    selElmnt = x[i].getElementsByTagName("select")[0];
    /*for each element, create a new DIV that will act as the selected item:*/
    a = document.createElement("DIV");
    a.setAttribute("class", "select-selected");
    a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
    x[i].appendChild(a);
    /*for each element, create a new DIV that will contain the option list:*/
    b = document.createElement("DIV");
    b.setAttribute("class", "select-items select-hide");
    for (j = 1; j < selElmnt.length; j++) {
        /*for each option in the original select element,
        create a new DIV that will act as an option item:*/
        c = document.createElement("DIV");
        c.innerHTML = selElmnt.options[j].innerHTML;
        c.addEventListener("click", function (e) {
            /*when an item is clicked, update the original select box,
            and the selected item:*/
            var y, i, k, s, h;
            s = this.parentNode.parentNode.getElementsByTagName("select")[0];
            h = this.parentNode.previousSibling;
            for (i = 0; i < s.length; i++) {
                if (s.options[i].innerHTML == this.innerHTML) {
                    s.selectedIndex = i;
                    h.innerHTML = this.innerHTML;
                    y = this.parentNode.getElementsByClassName("same-as-selected");
                    for (k = 0; k < y.length; k++) {
                        y[k].removeAttribute("class");
                    }
                    this.setAttribute("class", "same-as-selected");
                    break;
                }
            }
            h.click();
        });
        b.appendChild(c);
    }
    x[i].appendChild(b);
    a.addEventListener("click", function (e) {
        /*when the select box is clicked, close any other select boxes,
        and open/close the current select box:*/
        e.stopPropagation();
        closeAllSelect(this);
        this.nextSibling.classList.toggle("select-hide");
        this.classList.toggle("select-arrow-active");
    });
}
function closeAllSelect(elmnt) {
    /*a function that will close all select boxes in the document,
    except the current select box:*/
    var x, y, i, arrNo = [];
    x = document.getElementsByClassName("select-items");
    y = document.getElementsByClassName("select-selected");
    for (i = 0; i < y.length; i++) {
        if (elmnt == y[i]) {
            arrNo.push(i)
        } else {
            y[i].classList.remove("select-arrow-active");
        }
    }
    for (i = 0; i < x.length; i++) {
        if (arrNo.indexOf(i)) {
            x[i].classList.add("select-hide");
        }
    }
}
/*if the user clicks anywhere outside the select box,
then close all select boxes:*/
document.addEventListener("click", closeAllSelect);
</script>

<script>
    $(function () {
    $('[data-toggle="tooltip"]').tooltip()
})
    AOS.init();
</script>

<script type="text/javascript">
$(document).ready(function() {
Highcharts.chart('chart-container', {

title: {
    text: 'Exam Report'
},

subtitle: {
    text: 'Subject Wise Exam Report'
},

yAxis: {
    title: {
        text: 'Percentage'
    }
},


xAxis: {
    pointStart:1,
        categories: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15]
    },

legend: {
    layout: 'vertical',
    align: 'right',
    verticalAlign: 'middle'
},


series: [
    <?php echo $data['data']['line_chart_data']; ?>
],


responsive: {
    rules: [{
        condition: {
            maxWidth: 500
        },
        chartOptions: {
            legend: {
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'bottom'
            }
        }
    }]
}

});
});
    function redirectthis(){
        // alert("asdasd");
        document.location.href="{!!route('lo_marks_greport.index')!!}";
    }
</script>

@include('includes.footer')