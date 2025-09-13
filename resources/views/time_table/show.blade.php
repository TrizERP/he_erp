@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<style type="text/css">
    .form-group {
        margin-bottom: 0px !important;
    }
</style>
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Create New Timetable</h4>
            </div>
        </div>

        @php
            $grade_id = $standard_id = $division_id = $enrollment_no = $receipt_no = $from_date = $to_date = '';

            if (isset($data['grade_id'])) {
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
        @endphp
        <div class="card"> <!--  py-0 -->
            @if ($sessionData = Session::get('data'))
                @if ($sessionData['status_code'] == 1)
                    <div class="alert alert-success alert-block">
                    @else
                        <div class="alert alert-danger alert-block">
                @endif
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $sessionData['message'] }}</strong>
        </div>
        @endif
            @if(session()->has('status_code'))
                <div class="alert alert-{{ session('status_code') == 1 ? 'success' : 'danger' }}">
                    {{ session('message') }}
                </div>
            @endif
        <form action="{{ route('create-timetable.create') }}">
            @csrf
            <div class="row">

                {{ App\Helpers\SearchChain('4', 'single', 'grade,std,div', $grade_id, $standard_id, $division_id) }}

                <div class="col-md-12 form-group">
                    <center>
                        <input type="submit" name="submit" value="Search" class="btn btn-success">
                    </center>
                </div>

            </div>
        </form>
    </div>
</div>

@if (isset($data['period_data']))
    <div class="card">
        <form action="{{ route('create-timetable.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12 p-0">
                    <table id="example" class="table table-striped">
                        <thead>
                            <tr>
                                <th><span class="label label-info">Days/Lectures</span></th>
                                @foreach ($data['period_data'] as $key => $value)
                                    <th><span class="label label-info">{{ $value->title }}</span></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $j = 1;
                            @endphp
    @if (isset($data['week_data']))

        @foreach ($data['week_data'] as $fullday => $shortday)
            <tr>
                <td style='display: table-cell;'><span
                        class='label label-warning'>{{ $fullday }}</span></td>
                @foreach ($data['period_data'] as $key => $value)
                    <td class="text-center" style="font-size:10px;color: black;">
                        <input type="hidden" name="grade_id" value="{{ $grade_id }}">
                        <input type="hidden" name="standard_id" value="{{ $standard_id }}">
                        <input type="hidden" name="division_id" value="{{ $division_id }}">

                        <!-- remove or delete timetable -->
                        @php
                            $timetable_id = $value->id ?? '';
                            $j = 1;
                        @endphp
                        <div id="{{ $shortday . '-' . $timetable_id }}">

        @if (isset($data['old_timetable_data'][$shortday][$value->id]['SUBJECT_ID']))
            @foreach ($data['old_timetable_data'][$shortday][$value->id]['SUBJECT_ID'] as $index => $lect)
                <!-- assigned subject  -->
                @if (!empty($data['subject_data']))
                    <div class="form-group">
                        <select class="form-control mb-2"
                            name="subjects[{{ $value->id }}][{{ $shortday }}][{{ $index }}]">
                            <option>--Subject--</option>
                            @foreach ($data['subject_data'] as $sub => $subjects)
                                @php
                                    $selected_sub =
                                        $data['old_timetable_data'][
                                            $shortday
                                        ][$value->id]['SUBJECT_ID'][
                                            $index
                                        ] ?? '';
                                @endphp
                                <option
                                    value="{{ $subjects['subject_id'] }}"
                                    @if (isset($selected_sub) && $selected_sub == $subjects['subject_id']) selected @endif>
                                    {{ $subjects['display_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- assigned teachers  -->
                @if (!empty($data['teacher_data']))
                    <div class="form-group">
                        <select class="form-control mb-2"
                            name="teachers[{{ $value->id }}][{{ $shortday }}][{{ $index }}]">
                            <option>--Lecturer--</option>
                            @foreach ($data['teacher_data'] as $teach => $teachers)
                                @php
                                    $selected_teach =
                                        $data['old_timetable_data'][
                                            $shortday
                                        ][$value->id]['TEACHER_ID'][
                                            $index
                                        ] ?? '';
                                @endphp
                                <option value="{{ $teachers['id'] }}"
                                    @if (isset($selected_teach) && $selected_teach == $teachers['id']) selected @endif>
                                    {{ $teachers['teacher_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- assigned batches  -->

                @if (!empty($data['old_timetable_data'][$shortday][$value->id]['BATCH_ID'][$index]))
                    @php
                        $selected_batch =
                            $data['old_timetable_data'][$shortday][$value->id]['BATCH_ID'][$index] ?? '';
                        $j++;
                    @endphp
                    <div class="form-group">
                        <select class="form-control mb-2"
                            name="batches[{{ $value->id }}][{{ $shortday }}][{{ $index }}]">
                            <option>--Batch--</option>
                            @foreach ($data['batch_data'] as $batch => $batches)
                                <option value="{{ $batches['id'] }}"
                                    @if (isset($selected_batch) && $selected_batch == $batches['id']) selected @endif>
                                    {{ $batches['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- assigned rooms  -->
                @if (!empty($data['room_data']))
                    <div class="form-group">
                        <select class="form-control mb-2"
                            name="rooms[{{ $value->id }}][{{ $shortday }}][{{ $index }}]">
                            <option>--Room--</option>
                            @foreach ($data['room_data'] as $room => $rooms)
                                @php
                                    $selected_room =
                                        $data['old_timetable_data'][$shortday][$value->id]['ROOM_ID'][$index] ?? '';
                                @endphp
                                <option value="{{ $rooms['id'] }}"
                                    @if (isset($selected_room) && $selected_room == $rooms['id']) selected @endif>
                                    {{ $rooms['room_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- assigned types  -->
                @php
                    $selected_type =
                        $data['old_timetable_data'][$shortday][$value->id][
                            'TYPE'
                        ][$index] ?? '';
                @endphp
                <div class="form-group">
                    <select class="form-control mb-2"
                        name="types[{{ $value->id }}][{{ $shortday }}][{{ $index }}]">
                        <option>--Types--</option>
                        @foreach ($data['types'] as $key => $types)
                            <option value="{{ $types }}"
                                @if (isset($selected_type) && $selected_type == $types) selected @endif>
                                {{ $types }}</option>
                        @endforeach
                    </select>
                </div>
                <a class="fas fa-window-close text-danger" href="#"
                    onclick="deleteTimetable('{{ $shortday . '-' . $timetable_id . '-' . $teachers['id'] . '-' . $rooms['id'] }}');"></a>
            @endforeach
            @if (in_array($selected_type, ['Lab', 'Tutorial']))
                @php
                    $ext_lab =
                        $data['old_timetable_data'][$shortday][$value->id][
                            'LAB'
                        ][$index] ?? '';
                @endphp
                <input
                    type="checkbox"name="extend_lab[{{ $value->id }}][{{ $shortday }}][{{ $index }}]"
                    @if ($ext_lab == 'Y') checked @endif>
            @endif

            <hr>
        @else
            <!-- unassigned subject  -->
            @if (!empty($data['subject_data']))
                <div class="form-group">
                    <select class="form-control mb-2"
                        name="subjects[{{ $value->id }}][{{ $shortday }}][0]">
                        <option>--Subject--</option>
                        @foreach ($data['subject_data'] as $sub => $subjects)
                            <option value="{{ $subjects['subject_id'] }}">{{ $subjects['display_name'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- unassigned teachers  -->
            @if (!empty($data['teacher_data']))
                <div class="form-group">
                    <select class="form-control mb-2"
                        name="teachers[{{ $value->id }}][{{ $shortday }}][0]">
                        <option>--Lecturer--</option>
                        @foreach ($data['teacher_data'] as $teach => $teachers)
                            <option value="{{ $teachers['id'] }}">{{ $teachers['teacher_name'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- unassigned batches  -->
<!--            @if (!empty($data['batch_data']))
                <div class="form-group">
                    <select class="form-control mb-2"
                        name="batches[{{ $value->id }}][{{ $shortday }}][0]">
                        <option>--Batch--</option>
                        @foreach ($data['batch_data'] as $batch => $batches)
                            <option value="{{ $batches['id'] }}">{{ $batches['title'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
-->
            <!-- unassigned rooms  -->
            @if (!empty($data['room_data']))
                <div class="form-group">
                    <select class="form-control mb-2"
                        name="rooms[{{ $value->id }}][{{ $shortday }}][0]">
                        <option>--Room--</option>
                        @foreach ($data['room_data'] as $room => $rooms)
                            <option value="{{ $rooms['id'] }}">{{ $rooms['room_name'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- unassigned type -->
            <div class="form-group">
                <select class="form-control mb-2 subjectType"
                    name="types[{{ $value->id }}][{{ $shortday }}][0]">
                    <option>--Types--</option>
                    @foreach ($data['types'] as $key => $types)
                        <option value="{{ $types }}">{{ $types }}</option>
                    @endforeach
                </select>
            </div>

    </div>
    <!-- add or delete timetable  -->
    <a class="fas fa-window-close text-danger" href="#"
        onclick="deleteTimetable('{{ $shortday . '-' . $timetable_id . '-' . $teachers['id'] . '-' . $rooms['id'] }}');"
        id="delete-{{ $shortday . '-' . $timetable_id }}"></a>

    <a class='fas fa-plus-square' href='#'
        onclick="addNewRow('{{ $shortday . '-' . $timetable_id }}');"
        id="add-{{ $shortday . '-' . $timetable_id }}"></a>
    <input type="checkbox" name="extend_lab[{{ $value->id }}][{{ $shortday }}][0]" class="extend_lab">
    <hr>
    @endif
                </td>
        @endforeach

        </tr>
    @endforeach
@endif

</tbody>
</table>

<div class="col-md-12 form-group mt-4">
    <center>
        <input type="submit" name="submit" value="Create" class="btn btn-success">
    </center>
</div>


</div>
</div>
</form>
</div>
@endif
</div>


@include('includes.footerJs')

<script>
    function deleteTimetable(id) {
        var standard_id = {{ $standard_id }};
        var division_id = {{ $division_id }};
        var grade_id = {{ $grade_id }};
        var path = "{{ route('Delete_Timetable') }}";
        $.ajax({
            url: path,
            data: 'standard_id=' + standard_id + '&division_id=' + division_id + '&grade_id=' + grade_id +
                '&id=' + id,
            success: function(result) {
                // Reload the current page
                location.reload();
            }
        });
    }

    function addNewRow(id) {
        var standard_id = {{ $standard_id }};
        var division_id = {{ $division_id }};
        var path = "{{ route('add_remove_Batch_Timetable') }}";
        $.ajax({
            url: path,
            data: 'mode=batchwise&standard_id=' + standard_id + '&division_id=' + division_id + '&id=' + id,
            success: function(result) {
                $("#" + id).html(result);
                $('#add-' + id).remove();
                $('#delete-' + id).remove();
            }
        });
    }

    function removeNewRow(id, mode) {
        var standard_id = {{ $standard_id }};
        var division_id = {{ $division_id }};
        var path = "{{ route('add_remove_Batch_Timetable') }}";
        $.ajax({
            url: path,
            data: 'mode=normal&standard_id=' + standard_id + '&division_id=' + division_id + '&id=' + id,
            success: function(result) {
                $("#" + id).html(result);
            }
        });
    }
    $(document).ready(function() {
        $('.extend_lab').hide();
        $('.subjectType').on('change', function() {
            var selectedType = $(this).val();
            var $td = $(this).closest('td');
            if (selectedType === 'Tutorial' || selectedType === 'Lab') {
                $td.find('.extend_lab').show();
            } else {
                $td.find('.extend_lab').hide();
            }
        });

    })
</script>
@include('includes.footer')
