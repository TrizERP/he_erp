@extends('layout')
@section('container')
<style>
        input[type="radio"].Absent {
            accent-color: red; /* make absent radio red */
        }
        input[type="radio"].Present {
            accent-color: green; /* optional: present radio green */
        }
</style>
    <div id="page-wrapper">
        <div class="container-fluid">

            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title">Take Attendance</h4>
                </div>
            </div>
        </div>
        @php
            $grade_id = $standard_id = $division_id = $enrollment_no = $receipt_no = $to_date = '';
            $from_date = now();
            if (isset($data['grade_id'])) {
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            if (isset($data['from_date'])) {
                $from_date = $data['from_date'];
            }
            if (isset($data['lecture_no'])) {
                $lecture_no = $data['lecture_no'];
            }

        @endphp

        @if (session()->get('user_profile_name') == 'Lecturer' || session()->get('user_profile_name') == 'LMS Teacher')
            <script>
                $(document).ready(function() {
                    $('#from_date').datepicker({
                        autoclose: true,
                        todayHighlight: true,
                        minDate: new Date() // Set the minimum date to today
                    });
                });
            </script>
        @endif

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

        <form action="{{ route('students_attendance.create') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group d-flex text-center">
                        <div class="form-check">
                            @php
                                $checked = 'checked';
                                $proxy = $extra = '';
                                $type = isset($data['exampleRadios']) ? $data['exampleRadios'] : '';

                                if (isset($data['exampleRadios'])) {
                                    if ($data['exampleRadios'] == 'Proxy') {
                                        $proxy = 'checked';
                                        $checked = '';
                                    } elseif ($data['exampleRadios'] == 'Extra') {
                                        $extra = 'checked';
                                        $checked = '';
                                    }
                                }
                            @endphp

                            <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1"
                                value="Regular" {{ $checked }}>
                            <label class="form-check-label" for="exampleRadios1">Regular</label>
                        </div>
                        @if (isset($data['show_proxy']) && $data['show_proxy'] == 1)
                            <div class="form-check ml-2">
                                <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2"
                                    value="Proxy" {{ $proxy }}>
                                <label class="form-check-label" for="exampleRadios2">Proxy</label>
                            </div>
                        @endif
                        @if (isset($data['show_extra']) && $data['show_extra'] == 1)
                            <div class="form-check ml-2">
                                <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios3"
                                    value="Extra" {{ $extra }}>
                                <label class="form-check-label" for="exampleRadios3">Extra</label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-2" id="attendanceTypeSelect">
                    <label for="attendance_type">Select Type</label>
                    <select name="attendance_type" id ="attendance_type_select" class="form-control">
                        @php
                            $att_type = ['Lecture', 'Lab', 'Tutorial'];
                        @endphp

                        @foreach ($att_type as $key => $value)
                            <option value="{{ $value }}" @if (isset($data['attendance_type']) && $data['attendance_type'] == $value) selected @endif>
                                {{ $value }}</option>
                        @endforeach
                    </select>
                </div>

            </div>


            <div class="row">
                <div class="col-md-2 form-group">
                    <label>Date</label>
                    <input type="text" id="from_date" name="from_date" value="{{ $from_date }}"
                        class="form-control mydatepicker" autocomplete="off">
                </div>

                {{ App\Helpers\SearchChain('2', 'single', 'grade,std,div', $grade_id, $standard_id, $division_id) }}
                <div class="col-md-2">
                    <input type="hidden" id="subject_name" name="subject_name"
                        @if (isset($data['subject_name'])) value="{{ $data['subject_name'] }}" @endif>
                    <label for="subject">Subject :</label>
                    <select class="form-control" id="subject" name="subject" required>
                        @if (!empty($data['all_subject']))
                            @foreach ($data['all_subject'] as $index => $val)
                                @if (!empty($val))
                                    @foreach ($val as $key => $value)
                                        <option value="{{ $value['subject_id'] . '|||' . $value['period_id'] }}" 
                                                data-type="{{ $value['type'] }}"
                                                data-periodid="{{ $value['period_id'] }}"   
                                                data-timetableid="{{ $value['timetable'] }}"
                                                @if(isset($data['subject']) && $data['subject'] == $value['subject_id'] . '|||' . $value['period_id']) selected @endif>
                                            {{ $value['subject'] }}
                                        </option>
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    </select>

                </div>

                <div class="col-md-2" id="batch_div">
                    <input type="hidden" id="batch_name" name="batch_name"
                        @if (isset($data['batch_name'])) value="{{ $data['batch_name'] }}" @endif>
              
                    <label for="batch">Batch :</label>
                    <select class="form-control" id="batch" name="batch">
                        @if (!empty($data['batchs']['original']))
                            @foreach ($data['batchs']['original'] as $value)
                                <option value="{{ $value['id'] }}" 
                                    data-id="{{ $value['timetable_id'] }}"
                                    data-batchid="{{ $value['period_id'] }}"
                                    @if (isset($data['batch_id']) && $data['batch_id'] == $value['id']) selected @endif>
                                    {{ $value['batch'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

            </div>
             <input type="hidden" id="lecture_name" name="lecture_name"
                        @if (isset($data['lecture_name'])) value="{{ $data['lecture_name'] }}" @endif>
            <div class="row">
                <input type="hidden" name="timetable_id" id="timetable_id">
                <input type="hidden" name="period_id" id="period_id">

                <div class="col-md-12 form-group">
                    <center>
                        <input type="submit" name="submit" value="Search" class="btn btn-success">
                    </center>
                </div>
            </div>

            <div>
            </div>
        </form>
    </div>

    @if (isset($data['student_data']))
        @php
            $j = 1;
            if (isset($data['student_data'])) {
                $student_data = $data['student_data'];
            }
        @endphp
        <div class="card">
            <form method="POST" action="{{ route('students_attendance.store') }}">
                @csrf
                @php 
                    $subject_id=explode('|||',$data['subject'] ?? '');
                @endphp
                <input type="hidden" name="subjects_id" value="{{ $subject_id[0] ?? 0 }}">
                <input type="hidden" name="periods_id" value="{{ $subject_id[1] ?? 0 }}">
                <input type="hidden" name="timetables_id" value="{{ $data['timetable_id'] }}">
                <input type="hidden" name="batchs_id" value="{{ $data['batch_id'] }}">
                <input type="hidden" name="att_type" value="{{ $data['exampleRadios'] }}">
                <input type="hidden" name="att_for" value="{{ $data['attendance_type'] }}">

                <div class="table-responsive">
                    <table id="example" class="table table-striped">
                        <thead>
                            <tr>
                                <!--<th>Sr No</th>-->
                                <th>Subject</th>
                                <th>Lecture</th>
                                <th>Roll No</th>
                                <th>{{ App\Helpers\get_string('studentname', 'request') }}</th>
                                <th>Middle Name</th>
                                <th>Last Name</th>
                                @if (isset($data['batch_id']) && !empty($data['batchs']))
                                    <th>Batch</th>
                                @endif
                                <th>{{ App\Helpers\get_string('grno', 'request') }}</th>
                                <th>Present 
                                <input id="checkall" name="attendance" onchange="checkAll(this,'Present');" type="radio"></th>
                                <th>Absent
                                <input id="checkall" name="attendance" onchange="checkAll(this,'Absent');" type="radio">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($student_data as $key => $value)
                                <tr>
                                    <!--<td> {{ $j++ }} </td>-->
                                    <td> {{ $data['subject_name'] }} </td>
                                    <td> {{ $data['lecture_name'] }} </td>
                                    <td> {{ $value['roll_no'] }} </td>
                                    <td> {{ $value['first_name'] }} </td>
                                    <td> {{ $value['middle_name'] }} </td>
                                    <td> {{ $value['last_name'] }} </td>
                                    @if (isset($data['batch_id']) && !empty($data['batchs']))
                                        <td>{{ $value['batch_title'] }}</td>
                                    @endif
                                    <td> {{ $value['enrollment_no'] }} </td>
                                    <td> <input type="radio" value="P"
                                            @if (!isset($data['attendance_data'][$value['id']]) || $data['attendance_data'][$value['id']] == 'P') checked @endif class="Present"
                                            name="student[{{ $value['id'] }}]"> </td>
                                    <td> <input type="radio" value="A"
                                            @if (isset($data['attendance_data'][$value['id']]) && $data['attendance_data'][$value['id']] == 'A') checked @endif class="Absent"
                                            name="student[{{ $value['id'] }}]"> </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <center>
                                <input type="hidden" name="date"
                                    @if (isset($from_date)) value="{{ $from_date }}" @endif">
                                <input type="hidden" name="standard_division"
                                    @if (isset($standard_id) && isset($division_id)) value="{{ $standard_id }}||{{ $division_id }}" @endif">
                                <input type="hidden" name="lecture_no" value="{{ $data['lecture_no'] ?? null }}">
                                <input type="submit" name="submit" value="Submit" class="btn btn-success">
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @endif

    </div>

    </div>
    </div>

    @include('includes.footerJs')
<script>
$(document).ready(function () {
    $('#example').DataTable({
        // Basic
        paging: false,          // Enable pagination
        pageLength: 500,        // Rows per page
        //lengthMenu: [5, 10, 25, 50, 100], // Page size options
        ordering: true,        // Enable sorting
        searching: true,       // Enable search box
        info: true,            // Show "Showing 1 to n of n entries"
        autoWidth: false,      // Disable auto column width

        // Language / Labels
        /*language: {
            search: "Filter records:",
            lengthMenu: "Display _MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },*/

        // Column specific settings
        columnDefs: [
            { targets: 6, orderable: true },  // Enable sorting on 1st column
        //    { targets: [1, 2], searchable: true }, // Search enabled for columns
        //    { targets: -1, orderable: false } // Last column no sorting
        ],

        // Default sorting
        order: [[6, "asc"]], // Sort by 1st column ascending

        // State saving (keeps paging, sorting, etc. on reload)
        stateSave: true,

        // AJAX source (if needed)
        // ajax: "data.json",

        // Buttons (if you include DataTables Buttons extension)
        // dom: 'Bfrtip',
        // buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });
});
</script>


    <script>
        // ========================================
        // Attendance Management - Improved JavaScript
        // ========================================
        
        (function($) {
            'use strict';

            // Configuration object to avoid hard-coded values
            const CONFIG = {
                selectors: {
                    exampleRadios: 'input[name="exampleRadios"]',
                    attendanceTypeSelect: '#attendanceTypeSelect',
                    batchDiv: '#batch_div',
                    subjectSelect: '#subject',
                    divisionSelect: '#division',
                    standardSelect: '#standard',
                    fromDate: '#from_date',
                    batchSelect: '#batch',
                    lectureSelect: '#lecture',
                    subjectName: '#subject_name',
                    lectureName: '#lecture_name',
                    batchName: '#batch_name',
                    batchId: '#batch_id',
                    timetableId: '#timetable_id',
                    periodId: '#period_id',
                    attendanceTypeSelectEl: '#attendance_type_select',
                },
                api: {
                    subjectList: '/api/get-subject-list-timetable',
                    batchList: '/api/get-batch-list-timetable',
                },
                messages: {
                    selectOption: 'Select',
                    noSubjects: 'No subjects available',
                    errorLoading: 'Error loading data',
                }
            };

            // ========================================
            // Utility Functions
            // ========================================
            
            /**
             * Debounce function for performance optimization
             * @param {Function} func - Function to debounce
             * @param {number} wait - Delay in milliseconds
             * @returns {Function} - Debounced function
             */
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func.apply(this, args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            /**
             * Safe AJAX call with error handling
             * @param {Object} options - AJAX configuration
             * @returns {Promise} - AJAX promise
             */
            function safeAjax(options) {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        ...options,
                        success: (response) => {
                            console.log('AJAX Success:', options.url, response);
                            resolve(response);
                        },
                        error: (xhr, status, error) => {
                            console.error('AJAX Error:', options.url, error);
                            reject({ xhr, status, error });
                        }
                    });
                });
            }

            // ========================================
            // DOM Caching Helper
            // ========================================
            
            /**
             * Cache DOM elements for better performance
             * @returns {Object} - Cached DOM elements
             */
            function cacheElements() {
                return {
                    $exampleRadios: $(CONFIG.selectors.exampleRadios),
                    $attendanceTypeSelect: $(CONFIG.selectors.attendanceTypeSelect),
                    $batchDiv: $(CONFIG.selectors.batchDiv),
                    $subjectSelect: $(CONFIG.selectors.subjectSelect),
                    $divisionSelect: $(CONFIG.selectors.divisionSelect),
                    $standardSelect: $(CONFIG.selectors.standardSelect),
                    $fromDate: $(CONFIG.selectors.fromDate),
                    $batchSelect: $(CONFIG.selectors.batchSelect),
                    $lectureSelect: $(CONFIG.selectors.lectureSelect),
                    $subjectName: $(CONFIG.selectors.subjectName),
                    $lectureName: $(CONFIG.selectors.lectureName),
                    $batchName: $(CONFIG.selectors.batchName),
                    $batchId: $(CONFIG.selectors.batchId),
                    $timetableId: $(CONFIG.selectors.timetableId),
                    $periodId: $(CONFIG.selectors.periodId),
                    $attendanceTypeSelectEl: $(CONFIG.selectors.attendanceTypeSelectEl),
                };
            }

            // ========================================
            // Attendance Type Handlers
            // ========================================
            
            /**
             * Handle lecture type radio button change
             * @param {jQuery} $elements - Cached DOM elements
             */
            function handleLectureTypeChange($elements) {
                const selectedType = $(this).val();
                const isNonRegular = selectedType !== 'Regular';
                
                $elements.$attendanceTypeSelect.toggle(isNonRegular);
                updateBatchVisibility($elements);
            }

            /**
             * Handle attendance type select change
             * @param {jQuery} $elements - Cached DOM elements
             */
            function handleAttendanceTypeChange($elements) {
                updateBatchVisibility($elements);
            }

            /**
             * Update batch div visibility based on conditions
             * @param {jQuery} $elements - Cached DOM elements
             */
            function updateBatchVisibility($elements) {
                const selectedAttendanceType = $elements.$attendanceTypeSelectEl.val();
                const selectedLectureType = $elements.$exampleRadios.filter(':checked').val();
                
                // Show batch div when: non-Lecture type with Proxy lecture type
                const shouldShowBatch = 
                    selectedAttendanceType !== 'Lecture' && 
                    selectedLectureType === 'Proxy';
                
                $elements.$batchDiv.toggle(shouldShowBatch);
            }

            // ========================================
            // Subject and Division Handlers
            // ========================================
            
            /**
             * Load subjects based on selected division
             * @param {jQuery} $elements - Cached DOM elements
             */
            async function loadSubjects($elements) {
                const standardId = $elements.$standardSelect.val();
                const divisionId = $elements.$divisionSelect.val();
                const fromDate = $elements.$fromDate.val();
                const lectureType = $elements.$exampleRadios.filter(':checked').val();
                const attendanceType = $elements.$attendanceTypeSelectEl.val();

                // Validate required fields
                if (!standardId || !divisionId) {
                    $elements.$subjectSelect.empty();
                    return;
                }

                try {
                    const subjects = await safeAjax({
                        url: `${CONFIG.api.subjectList}`,
                        method: 'GET',
                        data: {
                            attendance_type: lectureType,
                            attendance_for: attendanceType,
                            standard_id: standardId,
                            division_id: divisionId,
                            date: fromDate,
                        }
                    });

                    populateSubjectDropdown($elements, subjects);
                } catch (error) {
                    console.error('Failed to load subjects:', error);
                    showErrorMessage($elements.$subjectSelect, CONFIG.messages.errorLoading);
                }
            }

            /**
             * Populate subject dropdown with data
             * @param {jQuery} $elements - Cached DOM elements
             * @param {Array} subjects - Array of subject objects
             */
            function populateSubjectDropdown($elements, subjects) {
                $elements.$subjectSelect.empty();
                
                if (!subjects || subjects.length === 0) {
                    showErrorMessage($elements.$subjectSelect, CONFIG.messages.noSubjects);
                    return;
                }

                const options = [{ value: '', text: CONFIG.messages.selectOption }];
                
                subjects.forEach(subject => {
                    options.push({
                        value: `${subject.subject_id}|||${subject.period_id}`,
                        text: subject.subject,
                        dataAttributes: {
                            type: subject.type,
                            periodid: subject.period_id,
                            timetableid: subject.timetable,
                        }
                    });
                });

                options.forEach(opt => {
                    const $option = $('<option>', {
                        value: opt.value,
                        text: opt.text,
                    });
                    
                    if (opt.dataAttributes) {
                        Object.entries(opt.dataAttributes).forEach(([key, val]) => {
                            $option.data(key.replace(/([A-Z])/g, '-$1').toLowerCase(), val);
                        });
                    }
                    
                    $elements.$subjectSelect.append($option);
                });
            }

            /**
             * Show error message in dropdown
             * @param {jQuery} $select - Select element
             * @param {string} message - Error message
             */
            function showErrorMessage($select, message) {
                $select.empty().append(
                    $('<option>', { value: '', text: message })
                );
            }

            // ========================================
            // Subject Change Handler
            // ========================================
            
            /**
             * Handle subject selection change
             * @param {jQuery} $elements - Cached DOM elements
             */
            async function handleSubjectChange($elements) {
                const selectedValue = $elements.$subjectSelect.val();
                const selectedOption = $elements.$subjectSelect.find('option:selected');
                
                if (!selectedValue) {
                    resetBatchFields($elements);
                    return;
                }

                const subjectType = selectedOption.data('type');
                const periodId = selectedOption.data('periodid');
                const timetableId = selectedOption.data('timetableid');
                const subjectName = selectedOption.text();

                // Update hidden fields
                $elements.$subjectName.val(subjectName);
                $elements.$lectureName.val(subjectType);
                $elements.$timetableId.val(timetableId);
                $elements.$periodId.val(periodId);

                // Show/hide batch div based on subject type
                const shouldShowBatch = subjectType !== 'Lecture';
                $elements.$batchDiv.toggle(shouldShowBatch);

                // Load batches if applicable
                if (shouldShowBatch) {
                    await loadBatches($elements);
                } else {
                    $elements.$batchSelect.empty();
                }
            }

            /**
             * Reset batch-related fields
             * @param {jQuery} $elements - Cached DOM elements
             */
            function resetBatchFields($elements) {
                $elements.$batchSelect.empty();
                $elements.$batchName.val('');
                $elements.$batchId.val('');
            }

            // ========================================
            // Batch Loading
            // ========================================
            
            /**
             * Load batches from API
             * @param {jQuery} $elements - Cached DOM elements
             */
            async function loadBatches($elements) {
                const subjectId = $elements.$subjectSelect.val();
                const standardId = $elements.$standardSelect.val();
                const divisionId = $elements.$divisionSelect.val();
                const fromDate = $elements.$fromDate.val();
                const subjectType = $elements.$lectureName.val();
                const periodId = $elements.$periodId.val();

                if (!subjectId || !standardId || !divisionId) {
                    return;
                }

                try {
                    const batches = await safeAjax({
                        url: CONFIG.api.batchList,
                        method: 'GET',
                        data: {
                            subject_id: subjectId,
                            standard_id: standardId,
                            division_id: divisionId,
                            date: fromDate,
                            type: subjectType,
                            period_id: periodId,
                        }
                    });

                    populateBatchDropdown($elements, batches);
                } catch (error) {
                    console.error('Failed to load batches:', error);
                    showErrorMessage($elements.$batchSelect, CONFIG.messages.errorLoading);
                }
            }

            /**
             * Populate batch dropdown with data
             * @param {jQuery} $elements - Cached DOM elements
             * @param {Array} batches - Array of batch objects
             */
            function populateBatchDropdown($elements, batches) {
                $elements.$batchSelect.empty();
                
                if (!batches || batches.length === 0) {
                    $elements.$batchSelect.append(
                        $('<option>', { value: '', text: CONFIG.messages.selectOption })
                    );
                    return;
                }

                $elements.$batchSelect.append(
                    $('<option>', { value: '', text: CONFIG.messages.selectOption })
                );

                batches.forEach(batch => {
                    if (batch.batch != null) {
                        $elements.$batchSelect.append(
                            $('<option>', {
                                value: batch.id,
                                text: batch.batch,
                                data: { id: batch.timetable_id }
                            })
                        );
                    }
                });
            }

            // ========================================
            // Batch Selection Handler
            // ========================================
            
            /**
             * Handle batch selection change
             * @param {jQuery} $elements - Cached DOM elements
             */
            function handleBatchChange($elements) {
                const selectedOption = $elements.$batchSelect.find('option:selected');
                $elements.$batchName.val(selectedOption.text());
                $elements.$timetableId.val(selectedOption.data('id'));
            }

            // ========================================
            // Check All Function
            // ========================================
            
            /**
             * Check/uncheck all radio buttons of a specific class
             * @param {HTMLInputElement} checkbox - Master checkbox
             * @param {string} className - Class name to filter
             */
            window.checkAll = function(checkbox, className) {
                const isChecked = checkbox.checked;
                const checkboxes = document.getElementsByClassName(className);
                
                Array.from(checkboxes).forEach(el => {
                    if (el.type === 'radio') {
                        el.checked = isChecked;
                    }
                });
            };

            // ========================================
            // Initialization
            // ========================================
            
            /**
             * Initialize all event handlers
             * @param {jQuery} $elements - Cached DOM elements
             */
            function initializeEventHandlers($elements) {
                // Lecture type radio buttons
                $elements.$exampleRadios.on('change', function() {
                    handleLectureTypeChange.call(this, $elements);
                });

                // Attendance type select
                $elements.$attendanceTypeSelectEl.on('change', function() {
                    handleAttendanceTypeChange.call(this, $elements);
                });

                // Division change with debounce
                $elements.$divisionSelect.on('change', debounce(() => {
                    loadSubjects($elements);
                }, 300));

                // Subject change
                $elements.$subjectSelect.on('change', function() {
                    handleSubjectChange.call(this, $elements);
                });

                // Batch change
                $elements.$batchSelect.on('change', function() {
                    handleBatchChange.call(this, $elements);
                });
            }

            /**
             * Initialize UI state on page load
             * @param {jQuery} $elements - Cached DOM elements
             */
            function initializeUIState($elements) {
                // Check if any non-Regular radio is already selected on page load
                const checkedLectureType = $elements.$exampleRadios.filter(':checked').val();
                const isNonRegular = checkedLectureType && checkedLectureType !== 'Regular';
                
                // Show attendanceTypeSelect if non-Regular type is selected, otherwise hide it
                $elements.$attendanceTypeSelect.toggle(isNonRegular);
                
                // Check if batch div should be shown based on lecture_name hidden field
                const lectureName = $elements.$lectureName.val();
                const hasSubject = $elements.$subjectSelect.val();
                
                // Show batch div if lecture_name is not 'Lecture' and subject is selected
                const shouldShowBatch = hasSubject && lectureName && lectureName !== 'Lecture';
                $elements.$batchDiv.toggle(shouldShowBatch);
            }

            // ========================================
            // Document Ready Handler
            // ========================================
            
            $(document).ready(function() {
                // Cache DOM elements for performance
                const $elements = cacheElements();
                
                // Initialize UI state
                initializeUIState($elements);
                
                // Set up event handlers
                initializeEventHandlers($elements);
            });

        })(jQuery);
    </script>   
    @include('includes.footer')
@endsection
