  <script>
      const userBasePath = "{{ 'uploads/' . Str::slug(Auth::user()->name) . '-' . Auth::id() }}";
      const userRoot = "{{ 'uploads/' . Str::slug(Auth::user()->name) . '-' . Auth::id() }}";

      let currentFolder = null;

      $(function() {
          // Muat struktur pohon folder
          $('#folderTree').jstree({
              core: {
                  data: {
                      url: '/filemanager/tree',
                      dataType: 'json'
                  }
              },
              plugins: [
                  //   "checkbox",
                  //   "contextmenu",
                  //   "dnd",
                  //   "massload",
                  //   "search",
                  //   "sort",
                  //   "state",
                  //   "types",
                  //   "unique",
                  "wholerow",
                  //   "changed",
                  "conditionalselect"
              ]
          });


          // Saat folder diklik
          $('#folderTree').on('select_node.jstree', function(e, data) {
              const path = data.node.id;
              loadFiles(path);
          });
      });

      @if (session('success'))
          Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: '{{ session('success') }}',
              timer: 2500,
              timerProgressBar: true,
              showConfirmButton: false
          });
      @endif

      @if ($errors->any())
          Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: '{{ $errors->first() }}',
          });
      @endif

      function loadFiles(folder, showDeleteButton = true) {
          const userBasePath = "{{ 'uploads/' . Str::slug(Auth::user()->name) . '-' . Auth::id() }}";
          const relativePath = folder.replace(userBasePath + '/', '').replace(userBasePath, '');

          // ⛔ Batasi akses folder hanya ke dalam direktori user
          if (!folder.startsWith(userBasePath)) {
              console.error('⚠️ Akses folder tidak diizinkan:', folder);
              $('#fileList').html(`<div class="text-danger">🚫 Akses tidak diizinkan ke folder ini.</div>`);
              return;
          }

          $('#fileList').html('<div class="text-muted">🔄 Memuat file...</div>');

          $.get('/filemanager/browse/' + relativePath, function(files) {
              let html = '';

              if (showDeleteButton && folder !== userRoot) {
                  html += `
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        📤 Upload File
                    </button>
                    <button onclick="renameFolder()" type="button" class="btn btn-sm btn-outline-warning">
                        ✏️ Rename Folder
                    </button>
                    <button onclick="deleteFolder('${folder}')" class="btn btn-sm btn-outline-danger">
                        🗑️ Hapus Folder Ini
                    </button>
                </div>
            `;
              } else if (showDeleteButton) {
                  html += `
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        📤 Upload File
                    </button>
                    <div class="alert alert-info p-2 mt-2">
                        📌 Folder utama tidak bisa diubah atau dihapus.
                    </div>
                </div>
            `;
              }

              // Jika tidak ada file
              if (files.length === 0) {
                  html += `<div class="alert alert-warning">📁 Folder kosong.</div>`;
              } else {
                  html += `<h6>📂 File dalam folder: <code>${folder}</code></h6><div class="container-fluid">`;

                  files.forEach(f => {
                      const name = f.name;
                      const sizeKb = (f.size / 1024).toFixed(2);
                      const type = f.type.toUpperCase();

                      html += `
                    <div class="row align-items-center border-bottom py-1">
                        <div class="col-sm-6">${name}</div>
                        <div class="col-sm-3">
                            <small class="text-muted">Tipe: ${type} | Ukuran: ${sizeKb} KB</small>
                        </div>
                        <div class="col-sm-3 text-end">
                            <div class="btn-group" role="group">
                                <a href="/storage/${f.path}" target="_blank" class="btn btn-sm btn-outline-success">
                                    📥 Download
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteFile('${f.path}')">
                                    🗑️ Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                  });

                  html += `</div>`;
              }

              $('#fileList').html(html);
          }).fail(function() {
              $('#fileList').html(`<div class="text-danger">❌ Terjadi kesalahan saat memuat file.</div>`);
          });
      }

      function uploadFile() {
          const fileInput = document.getElementById('uploadInput');
          const file = fileInput.files[0];
          const selectedNode = $('#folderTree').jstree('get_selected')[0];

          if (!file || !selectedNode) {
              Swal.fire({
                  icon: 'warning',
                  title: 'Peringatan',
                  text: '📂 Pilih folder terlebih dahulu dan file harus dipilih.'
              });
              return;
          }

          const formData = new FormData();
          formData.append('file', file);
          formData.append('folder', selectedNode);

          const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          formData.append('_token', token);

          Swal.fire({
              title: 'Mengunggah...',
              html: `
            <div id="swal-progress-text">⏳ Mohon tunggu...</div>
            <div class="progress" style="margin-top:10px; height:20px;">
                <div id="swal-progress-bar" class="progress-bar bg-info" role="progressbar" style="width: 0%;">0%</div>
            </div>
        `,
              showConfirmButton: false,
              allowOutsideClick: false,
              didOpen: () => {
                  Swal.showLoading();
              }
          });

          $.ajax({
              url: '/filemanager/file/upload',
              method: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              xhr: function() {
                  const xhr = new window.XMLHttpRequest();
                  xhr.upload.addEventListener("progress", function(e) {
                      if (e.lengthComputable) {
                          const percent = Math.round((e.loaded / e.total) * 100);
                          const progressBar = document.getElementById("swal-progress-bar");
                          if (progressBar) {
                              progressBar.style.width = percent + "%";
                              progressBar.innerText = percent + "%";
                          }
                      }
                  }, false);
                  return xhr;
              },
              success: function() {
                  fileInput.value = '';
                  $('#uploadModal').modal('hide');

                  Swal.fire({
                      icon: 'success',
                      title: 'Berhasil',
                      text: '✅ File berhasil diunggah.',
                      timer: 1500,
                      timerProgressBar: true,
                      showConfirmButton: false
                  }).then(() => {
                      try {
                          const selectedNode = $('#folderTree').jstree('get_selected')[0];
                          if (selectedNode) {
                              loadFiles(selectedNode);
                          } else {
                              location.reload();
                          }
                      } catch (err) {
                          console.error('Gagal memuat ulang file:', err);
                      }
                  });
              },
              error: function() {
                  Swal.fire({
                      icon: 'error',
                      title: 'Gagal',
                      text: '❌ Gagal mengunggah file.',
                      timer: 1500,
                      timerProgressBar: true,
                  });
              }
          });
      }

      function deleteFile(filePath) {
          Swal.fire({
              title: 'Yakin hapus file?',
              text: 'File akan dihapus secara permanen.',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: '🗑️ Hapus',
              cancelButtonText: 'Batal'
          }).then(result => {
              if (result.isConfirmed) {
                  $.ajax({
                      url: '/filemanager/file/delete',
                      method: 'DELETE',
                      data: {
                          path: filePath,
                          _token: '{{ csrf_token() }}' // Penting jika pakai POST dan Laravel
                      },
                      success: function(res) {
                          Swal.fire({
                              icon: 'success',
                              title: 'Berhasil',
                              text: res.message || '✅ File berhasil dihapus.',
                              timer: 3000,
                              timerProgressBar: true,
                              showConfirmButton: false
                          }).then(() => {
                              const selectedNode = $('#folderTree').jstree('get_selected')[0];
                              if (selectedNode) {
                                  loadFiles(
                                      selectedNode); // Reload file list jika folder dipilih
                              } else {
                                  location.reload(); // Jika tidak ada folder dipilih
                              }
                          });
                      },
                      error: function(xhr) {
                          Swal.fire({
                              icon: 'error',
                              title: 'Gagal',
                              text: xhr.responseJSON?.message || '❌ Gagal menghapus file.',
                              timer: 3000,
                              timerProgressBar: true,
                              showConfirmButton: false
                          });
                      }
                  });
              }
          });
      }

      function createFolder() {
          const folderName = document.getElementById('newFolderName').value.trim();
          const selected = $('#folderTree').jstree('get_selected')[0];

          if (!folderName) {
              Swal.fire({
                  icon: 'warning',
                  title: 'Nama folder kosong',
                  text: '📁 Masukkan nama folder terlebih dahulu.'
              });
              return;
          }

          if (!selected) {
              Swal.fire({
                  icon: 'warning',
                  title: 'Folder induk belum dipilih',
                  text: '📂 Silakan pilih folder induk di panel sebelah kiri.'
              });
              return;
          }

          const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

          $.ajax({
              url: '/filemanager/create-folder',
              method: 'POST',
              data: {
                  parent: selected,
                  name: folderName,
                  _token: token
              },
              success: function() {
                  $('#createFolderModal').modal('hide'); // Tutup modal
                  document.getElementById('newFolderName').value = ''; // Reset input

                  Swal.fire({
                      icon: 'success',
                      title: 'Berhasil',
                      text: '✅ Folder berhasil dibuat.'
                  });

                  $('#folderTree').jstree(true).refresh();
              },
              error: function(xhr) {
                  let msg = '❌ Gagal membuat folder.';
                  if (xhr.responseJSON && xhr.responseJSON.message) {
                      msg += '\n' + xhr.responseJSON.message;
                  }
                  Swal.fire({
                      icon: 'error',
                      title: 'Gagal',
                      text: msg
                  });
              }
          });
      }

      function renameFolder() {
          const selected = $('#folderTree').jstree('get_selected')[0];

          if (!selected) {
              return Swal.fire({
                  icon: 'warning',
                  title: 'Gagal',
                  text: 'Pilih folder yang ingin di-rename!',
                  timer: 2000,
                  timerProgressBar: true,
                  showConfirmButton: false
              });
          }

          Swal.fire({
              title: 'Ganti Nama Folder',
              input: 'text',
              inputLabel: 'Nama baru:',
              inputValue: selected.split('/').pop(),
              showCancelButton: true,
              confirmButtonText: 'Ubah',
              preConfirm: (newName) => {
                  if (!newName) {
                      Swal.showValidationMessage('Nama tidak boleh kosong!');
                  }
                  return newName;
              }
          }).then((result) => {
              if (result.isConfirmed) {
                  $.post('/filemanager/rename-folder', {
                      _token: '{{ csrf_token() }}',
                      path: selected,
                      newName: result.value
                  }).done(response => {
                      Swal.fire({
                          icon: 'success',
                          title: 'Berhasil',
                          text: response.message,
                          timer: 2000,
                          timerProgressBar: true,
                          showConfirmButton: false
                      }).then(() => location.reload());
                  }).fail(xhr => {
                      Swal.fire({
                          icon: 'error',
                          title: 'Gagal',
                          text: xhr.responseJSON?.message || 'Gagal mengganti nama folder',
                          timer: 2000,
                          timerProgressBar: true,
                          showConfirmButton: false
                      });
                  });
              }
          });
      }

      function deleteFolder(path) {
          Swal.fire({
              title: 'Yakin hapus folder?',
              text: 'Semua isi folder juga akan dihapus!',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Hapus',
              cancelButtonText: 'Batal'
          }).then(result => {
              if (result.isConfirmed) {
                  $.ajax({
                      url: '/filemanager/folder/delete',
                      type: 'POST',
                      data: {
                          folder: path
                      },
                      headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                      },
                      success: res => {
                          Swal.fire({
                              icon: 'success',
                              title: 'Berhasil',
                              text: res.message,
                              timer: 3000,
                              timerProgressBar: true,
                              showConfirmButton: false
                          }).then(() => location.reload());
                      },
                      error: xhr => {
                          Swal.fire({
                              icon: 'error',
                              title: 'Gagal',
                              text: xhr.responseJSON?.message ||
                                  'Tidak dapat menghapus folder.',
                              timer: 3000,
                              timerProgressBar: true,
                              showConfirmButton: false
                          });
                      }
                  });
              }
          });
      }
  </script>
