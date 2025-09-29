<!-- Modal -->
<!-- <div class="modal fade" id="documentModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document" style="max-width:1000px !important">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Document</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body"> -->
        <form action="{{route('user_document', $data['id'])}}" class="card" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group">
                        <label>Document Type</label>
                    <select id="document_type_id" name="document_type_id" id="document_type_id" class="form-control">
                        <option value="">Select</option>  
                        @foreach($documentTypeLists as $key => $value)
                        <option value="{{$value->id}}">{{$value->document_type}}</option>  
                        @endforeach
                    </select>
                </div>                                            
                <div class="col-md-4 form-group">
                    <label>Document Title</label>
                    <input type="text" id="document_title" name="document_title" id="document_title" class="form-control">
                </div>                                            
                <div class="col-md-4 form-group">
                    <label>File </label>
                    <input type="file" id="document" name="document" id="document" class="form-control">
                </div>  

                <div class="col-md-12">
                    <center>
                        <input type="submit" class="btn btn-primary" value="Save" onclick="saveDocument()">
                    </center>
                </div>
            </div>
        </form>

        @if(!empty($documentLists))
            <div class="card" style="margin-top:10px !important">
                <div class="table-responsive">
                    <table id="example" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Document Type</th>
                                <th>Document Title</th>
                                <th>File</th>
                                <th class="text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentLists as $key =>$value)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{$value->document_type}}</td>
                                    <td>{{$value->document_title}}</td>
                                    <td><a target="_blank" href="{{ Storage::disk('digitalocean')->url('public/he_staff_document/'.$value->file_name)}}">{{$value->file_name ?? '-'}}</a></td> 
                                    <td>
                                        <form action="{{ route('deleteDocument.destroy', $value->id) }}" 
                                            method="POST" 
                                            onsubmit="return confirm('Are you sure you want to delete this document?');">
                                            @csrf
                                            @method('DELETE')
                                                <button type="submit" class="btn btn-danger" style="padding:4px 6px"><span class="mdi mdi-delete"></span></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

      <!-- </div>
     
    </div>
  </div> -->
<!-- </div> -->

<script>
    function saveDocument(){
        var document_type_id = $('#document_type_id').prop('required',true);
        var document_title = $('#document_title').prop('required',true);
        var file_name = $('#document').prop('required',true);
    }
</script>