@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $d = $data;
    $prefix = $d['prefix'];
    $extra = $d['query'] ?? [];
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">{{ $d['title'] }}</h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                @if($d['canWrite'])
                    <button type="button" class="btn btn-success" id="pl-add"><i class="fa fa-plus"></i> Add</button>
                @endif
            </div>
        </div>

        @if(!empty($d['tabs']))
            <ul class="nav nav-tabs" style="margin-bottom:15px; flex-wrap:wrap">
                @foreach($d['tabs'] as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link {{ ($extra['type'] ?? '') === $key ? 'active' : '' }}" href="{{ route($prefix.'.index', ['type' => $key]) }}">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table id="pl-table" class="table table-striped" style="width:100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        @foreach($d['columns'] as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Add / edit modal, generated from the controller's field definitions --}}
    <div class="modal fade" id="pl-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form id="pl-form" class="modal-content" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="pl-method" value="POST">
                @foreach($extra as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="pl-modal-title">{{ $d['title'] }}</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" id="pl-errors" style="display:none"></div>
                    <div class="row">
                        @foreach($d['fields'] as $f)
                            @php($type = $f['type'])
                            <div class="{{ $type === 'textarea' ? 'col-md-12' : 'col-md-6' }} form-group">
                                <label>{{ $f['label'] }} @if(!empty($f['required']))<span class="text-danger">*</span>@endif</label>
                                @if($type === 'textarea')
                                    <textarea name="{{ $f['name'] }}" class="form-control" rows="3"></textarea>
                                @elseif($type === 'select')
                                    <select name="{{ $f['name'] }}" class="form-control">
                                        <option value="">-- Select --</option>
                                        @foreach($f['options'] as $val => $text)
                                            <option value="{{ $val }}">{{ $text }}</option>
                                        @endforeach
                                    </select>
                                @elseif($type === 'multiselect')
                                    <select name="{{ $f['name'] }}[]" class="form-control" multiple size="5">
                                        @foreach($f['options'] as $val => $text)
                                            <option value="{{ $val }}">{{ $text }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $type }}" name="{{ $f['name'] }}" class="form-control"
                                           @if($type === 'number') step="any" @endif>
                                @endif
                                @if(!empty($f['help']))<small class="text-muted">{{ $f['help'] }}</small>@endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            var routes = {
                data: "{{ route($prefix.'.data', $extra) }}",
                store: "{{ route($prefix.'.store') }}",
                edit: "{{ url(route($prefix.'.index', [], false)) }}",
            };
            var extra = @json($extra);
            var qs = $.isEmptyObject(extra) ? '' : '?' + $.param(extra);
            var csrf = $('meta[name="csrf-token"]').attr('content') || $('#pl-form input[name=_token]').val();

            var cols = [{data: 'DT_RowIndex', orderable: false, searchable: false}];
            @foreach(array_keys($d['columns']) as $key)
            cols.push({data: '{{ $key }}', defaultContent: ''});
            @endforeach
            cols.push({data: 'action', orderable: false, searchable: false});

            var table = $('#pl-table').DataTable({processing: true, serverSide: true, ajax: routes.data, columns: cols});

            function showErrors(xhr) {
                var msgs = [];
                if (xhr.status === 422 && xhr.responseJSON) {
                    $.each(xhr.responseJSON.errors, function (k, v) { msgs.push(v.join(' ')); });
                } else {
                    msgs.push((xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.');
                }
                $('#pl-errors').html(msgs.join('<br>')).show();
            }

            function openForm(id) {
                $('#pl-errors').hide();
                var form = $('#pl-form')[0];
                form.reset();
                $('#pl-form').data('id', id || '');
                if (!id) {
                    $('#pl-modal').modal('show');
                    return;
                }
                $.getJSON(routes.edit + '/' + id + '/edit' + qs, function (row) {
                    $.each(row, function (k, v) {
                        var el = $('#pl-form [name="' + k + '"], #pl-form [name="' + k + '[]"]');
                        if (!el.length) { return; }
                        if (el.is('select[multiple]')) { el.val(v || []); }
                        else { el.val(v === null ? '' : (el.attr('type') === 'date' && typeof v === 'string' ? v.substr(0, 10) : v)); }
                    });
                    $('#pl-modal').modal('show');
                });
            }

            $('#pl-add').on('click', function () { openForm(null); });
            $('#pl-table').on('click', '.pl-edit', function () { openForm($(this).data('id')); });

            $('#pl-table').on('click', '.pl-delete', function () {
                if (!confirm('Delete this record?')) { return; }
                $.ajax({url: routes.edit + '/' + $(this).data('id'), type: 'POST', data: $.extend({_method: 'DELETE', _token: csrf}, extra)})
                    .done(function () { table.ajax.reload(null, false); })
                    .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.'); });
            });

            $('#pl-table').on('click', '.pl-act', function () {
                var b = $(this);
                if (b.data('confirm') && !confirm(b.data('confirm'))) { return; }
                $.post(b.data('url'), {_token: csrf})
                    .done(function () { table.ajax.reload(null, false); })
                    .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Action failed.'); });
            });

            $('#pl-form').on('submit', function (e) {
                e.preventDefault();
                var id = $(this).data('id');
                var url = id ? routes.edit + '/' + id : routes.store;
                $('#pl-method').val(id ? 'PUT' : 'POST');
                $.ajax({url: url, type: 'POST', data: $(this).serialize()})
                    .done(function () { $('#pl-modal').modal('hide'); table.ajax.reload(null, false); })
                    .fail(showErrors);
            });
        });
    </script>
    @include('includes.footer')
</div>
