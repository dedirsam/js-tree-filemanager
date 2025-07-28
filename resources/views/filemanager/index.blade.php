 @extends('layouts.filemanager')
 @section('content')
     <div class="container-fluid mt-2">
         <h3>📁 File Manager Laravel + jsTree</h3>
         <div class="row">
             <div class="col">
                 <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                     ➕ Buat Folder
                 </button>
                 <button type="button" class="btn btn-sm btn-outline-success">Success</button>
                 <button type="button" class="btn btn-sm btn-outline-danger">Danger</button>
                 <button type="button" class="btn btn-sm btn-outline-info">Info</button>

             </div>
         </div>
         <div class="mb-3">
         </div>

         <div class="row">
             <div class="row gx-3">
                 <div class="col-3">
                     <div class="p-3 border bg-light">
                         <div id="folderTree"></div>
                     </div>
                 </div>
                 <div class="col-9">
                     <div class="p-3 border bg-light">
                         <div class="col-12" id="fileList">
                             <div class="text-muted">Pilih folder untuk melihat file.</div>
                         </div>
                     </div>
                 </div>
             </div>


         </div>
     </div>
 @endsection
