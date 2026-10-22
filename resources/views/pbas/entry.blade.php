@extends('pbas.layout')

@section('crumb', $def['crumb'])
@section('heading', $def['h1'])

@section('content')
    <fieldset @if($locked) disabled="disabled" @endif>
        <div class="card">
            <h5 class="m-b-0">{{ trim($def['h5']) }}</h5>
            @if($def['note'])<p class="text-muted">{{ $def['note'] }}</p>@endif
            <form action="{{ route("pbas.{$slug}.store") }}" method="post" autocomplete="off">
                @csrf
                <div class="row">
                    @foreach($def['fields'] as $field)
                        @php $name = $field['name']; @endphp
                        <div class="col-md-6 form-group" style="margin-left:0;margin-right:0">
                            <label>{{ $field['label'] }}</label>
                            @if($field['type'] === 'year')
                                <select name="{{ $name }}" class="form-control">
                                    @foreach($years as [$value, $label])
                                        <option value="{{ $value }}" @selected(old($name, $currentYear) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @elseif($field['type'] === 'select')
                                <select name="{{ $name }}" class="form-control">
                                    @foreach($field['options'] as [$value, $label])
                                        <option value="{{ $value }}" @selected(old($name) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @elseif($field['type'] === 'radio')
                                <div>
                                    @foreach($field['options'] as [$value, $label])
                                        <label class="radio-inline" style="margin-right:15px">
                                            <input value="{{ $value }}" type="radio" name="{{ $name }}" @checked(old($name) === $value) /> {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <input type="{{ $field['type'] }}" name="{{ $name }}" @if(isset($field['id'])) id="{{ $field['id'] }}" @endif
                                       class="form-control" placeholder="{{ $field['placeholder'] ?? '' }}" value="{{ old($name) }}" />
                            @endif
                        </div>
                    @endforeach
                </div>
                <button type="submit" name="{{ $def['submit'][0] }}" value="{{ $def['submit'][1] }}" class="btn btn-success">Add Record</button>
            </form>
        </div>

        <div class="card">
            <form name="frmEntryView" method="post" action="{{ route("pbas.{$slug}.destroy") }}">
                @csrf
                @method('DELETE')
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th style="width:30px"></th>
                            @foreach($def['columns'] as $header => $column)
                                <th class="text-left">{{ trim($header) }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td><input type="checkbox" name="{{ $def['checkbox'] }}[]" value="{{ $row->id }}"></td>
                                @foreach($def['columns'] as $column)
                                    <td>{{ $row->{$column} }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <input type="submit" name="{{ $def['delete'] }}" value="Delete" class="btn btn-danger m-t-10" onclick="return confirm('Delete the selected records?');" />
            </form>
        </div>
    </fieldset>
@endsection
