@extends('layouts.app')

@section('content')
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Data Periode</h5>
            <button type="button" data-bs-toggle="modal" data-bs-target="#createPeriode" class="btn btn-outline-primary">
                <i class="bx bx-plus me-1"></i> Tambah
            </button>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-striped">
                <thead class="table-primary">
                    <tr>
                        <th style="font-weight: bold;">Nama Periode</th>
                        <th style="font-weight: bold;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($periodes as $periode)
                        <tr>
                            <td>{{ $periode->nama_periode }}</td>
                            <td>
                                <div class="demo-inline-spacing text-center">
                                    <div class="btn-group text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-primary detail-periode"
                                                data-id="{{ $periode->periode_id }}">Detail</button>
                                            <button type="button" class="btn btn-sm btn-warning edit-periode"
                                                data-id="{{ $periode->periode_id }}">Edit</button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="showDeletePeriodeModal('{{ $periode->periode_id }}', '{{ $periode->nama_periode }}')">Hapus</button>
                                        </div>
                                    </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3 me-3">
            @if ($periodes->hasPages())
                <x-pagination :paginator="$periodes" />
            @endif
        </div>

    </div>

    @include('admin.periode.show')
    @include('admin.periode.create')
    @include('admin.periode.edit')
    @include('admin.periode.delete')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    title: "Berhasil",
                    text: "{{ session('success') }}",
                    icon: "success",
                    timer: 3000
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    title: "Gagal",
                    text: "{{ session('error') }}",
                    icon: "error"
                });
            @endif

            // Handle detail button click
            $('.detail-periode').on('click', function() {
                const periodeId = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/data/periode') }}/" + periodeId + "/show",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const periode = response.periode;

                        // Populate modal fields with periode data
                        $('#detail_periode_id').text(periode.periode_id);
                        $('#detail_nama_periode').text(periode.nama_periode);

                        // Show the modal
                        $('#detailPeriode').modal('show');
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
            $('.edit-periode').on('click', function() {
                const periodeId = $(this).data('id');

                // Fetch periode data
                $.ajax({
                    url: "{{ url('admin/data/periode') }}/" + periodeId + "/edit",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const periode = response.periode;

                        // Populate form fields
                        $('#edit_nama_periode').val(periode.nama_periode);

                        // Update form action
                        $('#form-edit').attr('action', "{{ url('admin/data/periode') }}/" +
                            periodeId);

                        // Show modal
                        $('#editPeriode').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: "Error",
                            text: "Gagal mengambil data periode",
                            icon: "error"
                        });
                    }
                });
            });
        });
    </script>
@endsection
