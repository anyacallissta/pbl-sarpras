@extends('layouts.app')

@section('content')
<!-- Flash Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Ruang</h5>

        <div class="d-flex align-items-center gap-2" style="max-width: 100%;">
            <div class="position-relative" style="max-width: 300px; width: 100%;">
                <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: #6c757d;"></i>
                <input 
                    type="text" 
                    id="searchInput" 
                    class="form-control form-control-sm" 
                    placeholder="Cari..." 
                    style="background-color: #f8f9fa; border: 1px solid #ced4da; color: #495057; font-weight: 400; font-size: 1rem; height: 42px; padding-left: 2.5rem;" />
            </div>
            <button type="button" data-bs-toggle="modal" data-bs-target="#createRuang" class="btn btn-outline-primary btn-sm"  style="height: 42px; align-items: center;">
                <i class="bx bx-plus me-1"></i> Tambah
            </button>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-striped">
            <thead class="table-primary">
                <tr>
                    <th style="font-weight: bold;">No</th>
                    <th style="font-weight: bold;">Kode Ruang</th>
                    <th style="font-weight: bold;">Nama Ruang</th>
                    <th style="font-weight: bold;">Kode Gedung</th>
                    <th style="font-weight: bold;">Nama Gedung</th>
                    <th style="font-weight: bold;">Lantai</th>
                    <th style="font-weight: bold;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($ruangs as $ruang)
                    <tr>
                        <td>{{ $loop->iteration + ($ruangs->firstItem() - 1) }}</td>
                        <td>{{ $ruang->kode }}</td>
                        <td>{{ $ruang->nama }}</td>
                        <td>{{ $ruang->gedung->kode }}</td>
                        <td>{{ $ruang->gedung->nama }}</td>
                        <td>{{ $ruang->lantai }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <!-- <button type="button" class="btn btn-sm btn-primary detail-ruang" data-id="{{ $ruang->ruang_id }}">Detail</button> -->
                                <button type="button" class="btn btn-sm btn-warning edit-ruang" data-id="{{ $ruang->ruang_id }}">Edit</button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="showDeleteModal('{{ $ruang->ruang_id }}', '{{ $ruang->nama }}')">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Tidak ada data ruang</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end mt-3 me-3">
        @if ($ruangs->hasPages())
            <x-pagination :paginator="$ruangs" />
        @endif
    </div>
    
</div>

@include('admin.ruang.create')
@include('admin.ruang.edit')
@include('admin.ruang.delete')
@include('admin.ruang.show')

<!-- SweetAlert Script -->
<script>
    // Auto-trigger SweetAlert for success or error messages
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                title: "Berhasil",
                text: "{{ session('success') }}",
                icon: "success",
                timer: 3000
            });
        @endif

        @if(session('error'))
            Swal.fire({
                title: "Gagal",
                text: "{{ session('error') }}",
                icon: "error"
            });
        @endif

        @if(session('adding'))
            // Buka modal tambah user otomatis setelah validasi gagal
            var createRuangModal = new bootstrap.Modal(document.getElementById('createRuang'));
            createRuangModal.show();
        @endif

        
        // Handle detail button click
        $('.detail-ruang').on('click', function () {
            const ruangId = $(this).data('id');
            $.ajax({
                url: "{{ url('admin/data/ruang') }}/" + ruangId + "/show",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const ruang = response.ruang;
                    const gedung = response.gedung;
                    
                    // Populate modal fields with ruang data
                    $('#detail_ruang_id').text(ruang.ruang_id);
                    $('#detail_kode').text(ruang.kode);
                    $('#detail_nama').text(ruang.nama);
                    $('#detail_gedungKode').text(gedung.kode);
                    $('#detail_gedungNama').text(gedung.nama);
                    $('#detail_lantai').text(ruang.lantai);

                    // Show the modal
                    $('#detailRuang').modal('show');
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Gagal mengambil detail user',
                        icon: 'error'
                    });
                }
            });
        });

        // Handle edit button click
        $('.edit-ruang').on('click', function() {
            const ruangId = $(this).data('id');
            
            // Fetch ruang data
            $.ajax({
                url: "{{ url('admin/data/ruang') }}/" + ruangId + "/edit",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const ruang = response.ruang;
                    const gedung = response.gedung;
                    
                    // Populate form fields
                    $('#edit_kode').val(ruang.kode);
                    $('#edit_nama').val(ruang.nama);
                    $('#edit_lantai').val(ruang.lantai);
                    $('#edit_gedung').val(ruang.gedung_id);

                    // Update form action
                    $('#form-edit').attr('action', "{{ url('admin/data/ruang') }}/" + ruangId);
                    
                    // Show modal
                    $('#editRuang').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        title: "Error",
                        text: "Gagal mengambil data ruang",
                        icon: "error"
                    });
                }
            });
        });

        // Search input filtering
        $('#searchInput').on('keyup', function () {
            const keyword = $(this).val().toLowerCase().trim();
            
            $('table tbody tr').each(function () {
                // Cek semua kolom di baris ini
                const rowText = $(this).text().toLowerCase();
                
                if (rowText.indexOf(keyword) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
</script>

@endsection
