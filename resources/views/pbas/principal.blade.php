@extends('pbas.layout')

@section('crumb', 'View/Enter ACR Faculty Data')
@section('heading', 'VIEW DATA/ ADD ACR  :  '.($dept ? $departments[$dept] : '').' For Year :'.$year)

@section('content')
    {{-- department buttons: the ERP department master replaces the original's six fixed branches --}}
    <div class="card">
        @foreach($departments as $id => $name)
            <a href="{{ route('pbas.principal.index', ['e' => $id]) }}" class="btn {{ $dept === (int) $id ? 'btn-info' : 'btn-default' }} m-b-5">
                <i class="mdi mdi-domain"></i> {{ $name }}
            </a>
        @endforeach
    </div>

    @if($dept)
        <div class="card">
            <h5 class="m-b-0">VIEW/ADD ACR DATA FOR FACULTY</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Faculty Id</th><th>Faculty Name</th><th>Comments</th><th>View Data</th><th>ACR</th><th>ACR Status</th><th>Lock Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($faculty as $row)
                        <tr>
                            <td>{{ $row->code }}</td>
                            <td style="text-transform: uppercase">{{ $row->name }}</td>
                            <td>
                                <textarea rows="6" cols="30" class="form-control" readonly>@forelse(($comments[$row->id] ?? collect()) as $i => $c){{ "\n".($i + 1).' '.$c->comment }}@empty No comments yet @endforelse</textarea>
                            </td>
                            <td><a class="btn btn-success" target="_blank" href="{{ route('pbas.report.show', $row->id) }}">View Data</a></td>
                            <td>
                                <form action="{{ route('pbas.principal.acr') }}" method="get">
                                    <input type="hidden" name="facultyid" value="{{ $row->id }}" />
                                    <input type="hidden" name="moveback" value="{{ $dept }}" />
                                    <input class="btn btn-warning" type="submit" value="Add ACR Data" name="acr" />
                                </form>
                            </td>
                            <td>
                                @if(isset($acr[$row->id]))
                                    @php $a = $acr[$row->id]; @endphp
                                    <strong>ACR entered</strong><br>
                                    {{ $a->punctuality }} / {{ $a->innovative }} / {{ $a->dedication }} / {{ $a->inp }} / {{ $a->performance }}
                                @else
                                    <span class="text-danger">ACR not entered</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($locks[$row->id]))
                                    @if((int) $locks[$row->id] === 1)
                                        <strong>Data Already locked...</strong>
                                    @else
                                        <strong class="text-danger">data is Not Locked Yet!!!</strong>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
