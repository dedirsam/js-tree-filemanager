<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="uploadForm" class="modal-content" onsubmit="event.preventDefault(); uploadFile();">
            @csrf
            <input type="hidden" name="folder" id="uploadFolderPath">
            <div class="modal-header">
                <h5 class="modal-title">Upload File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="file" name="file" id="uploadInput" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>
